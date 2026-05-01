<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillMissingStartScans extends Command
{
    protected $signature = 'rfid:backfill-start
                            {event_id : ID event}
                            {--category= : Hanya kategori tertentu (event_category_id), opsional}
                            {--spread=60 : Sebaran waktu antar peserta dalam detik (default: 60)}
                            {--dry-run : Tampilkan saja tanpa simpan ke DB}';

    protected $description = 'Backfill raw log + validated time untuk peserta yang belum ter-detect di checkpoint START.';

    public function handle(): int
    {
        $eventId    = (int) $this->argument('event_id');
        $categoryId = $this->option('category') ? (int) $this->option('category') : null;
        $spread     = max(1, (int) $this->option('spread'));
        $dryRun     = $this->option('dry-run');

        // ── Validasi event ────────────────────────────────────────────────
        $event = DB::selectOne(
            'SELECT id FROM events WHERE id = ? AND is_published = 1 LIMIT 1',
            [$eventId]
        );
        if (!$event) {
            $this->error("Event ID {$eventId} tidak ditemukan atau belum dipublish.");
            return self::FAILURE;
        }

        // ── Ambil semua checkpoint START yang aktif ────────────────────────
        $checkpointQuery = '
            SELECT rc.id, rc.checkpoint_name, rc.event_category_id
            FROM rfid_checkpoints rc
            WHERE rc.checkpoint_type = "start"
              AND rc.is_active = 1
              AND rc.event_category_id IN (
                  SELECT id FROM event_categories WHERE event_id = ?
              )
        ';
        $bindings = [$eventId];

        if ($categoryId) {
            $checkpointQuery .= ' AND rc.event_category_id = ?';
            $bindings[] = $categoryId;
        }

        $checkpoints = DB::select($checkpointQuery, $bindings);

        if (empty($checkpoints)) {
            $this->error('Tidak ada checkpoint START aktif untuk event ini.');
            return self::FAILURE;
        }

        $this->info("Event ID : {$eventId}");
        $this->info("Spread   : {$spread} detik");
        $this->info("Dry run  : " . ($dryRun ? 'YA' : 'TIDAK'));
        $this->line('');

        $totalBackfilled = 0;

        foreach ($checkpoints as $checkpoint) {
            $this->line("── Checkpoint: [{$checkpoint->id}] {$checkpoint->checkpoint_name} (category {$checkpoint->event_category_id})");

            // ── Auto-detect base time dari validated time terbaru di checkpoint ini ──
            // Ambil checkpoint_time paling akhir yang sudah ter-validated di kategori ini.
            // Logikanya: peserta yang missed pasti lewat gate di rentang waktu yang sama
            // dengan peserta yang ter-detect, jadi kita pakai waktu scan terakhir sebagai
            // batas atas, lalu backfill dimulai tepat 1 detik setelahnya.
            $lastValidated = DB::selectOne(
                'SELECT MAX(checkpoint_time) as last_time
                 FROM rfid_validated_times
                 WHERE rfid_checkpoint_id = ?',
                [$checkpoint->id]
            );

            if (!$lastValidated || !$lastValidated->last_time) {
                // Belum ada satupun yang ter-detect, pakai waktu sekarang
                $baseTime = Carbon::now();
                $this->warn("   ⚠ Belum ada scan sama sekali di checkpoint ini.");
                $this->info("   Base time (now)    : {$baseTime->format('Y-m-d H:i:s')}");
            } else {
                // Mulai dari 1 detik setelah scan terakhir yang ter-detect
                $baseTime = Carbon::parse($lastValidated->last_time)->addSecond();
                $this->info("   Base time (auto)   : {$baseTime->format('Y-m-d H:i:s')} (dari last validated: {$lastValidated->last_time})");
            }

            // ── Ambil peserta yang belum punya validated time di checkpoint ini ──
            $missing = DB::select('
                SELECT p.id AS participant_id,
                       p.bib,
                       m.rfid_tag
                FROM participants p
                JOIN participant_rfid_mappings m ON m.participant_id = p.id AND m.is_active = 1
                WHERE p.event_category_id = ?
                  AND p.event_id = ?
                  AND NOT EXISTS (
                      SELECT 1 FROM rfid_validated_times vt
                      WHERE vt.participant_id = p.id
                        AND vt.rfid_checkpoint_id = ?
                  )
                ORDER BY p.bib ASC
            ', [$checkpoint->event_category_id, $eventId, $checkpoint->id]);

            if (empty($missing)) {
                $this->line("   ✓ Semua peserta sudah ter-detect. Skip.");
                $this->line('');
                continue;
            }

            $this->warn("   Ditemukan " . count($missing) . " peserta belum ter-detect:");

            // ── Preview tabel waktu yang akan di-assign ───────────────────
            $headers = ['BIB', 'Participant ID', 'RFID Tag', 'Akan Di-assign Waktu'];
            $rows    = [];
            $count   = count($missing);

            foreach ($missing as $i => $p) {
                // Spread merata dalam window `spread` detik
                // + jitter kecil (0/1/2 detik) supaya tidak ada yang persis sama
                $offsetSeconds = (int) ($i * ($spread / max(1, $count)));
                $jitter        = $i % 3;
                $assignedTime  = $baseTime->copy()->addSeconds($offsetSeconds + $jitter);

                $rows[] = [
                    $p->bib,
                    $p->participant_id,
                    $p->rfid_tag,
                    $assignedTime->format('Y-m-d H:i:s'),
                ];
            }

            $this->table($headers, $rows);

            if ($dryRun) {
                $this->line("   [DRY RUN] Tidak ada perubahan ke DB.");
                $this->line('');
                continue;
            }

            if (!$this->confirm("   Proses backfill untuk checkpoint ini?", true)) {
                $this->line("   Skip oleh user.");
                $this->line('');
                continue;
            }

            // ── Insert ke DB ────────────────────────────────────────────────
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            foreach ($missing as $i => $p) {
                $offsetSeconds = (int) ($i * ($spread / max(1, $count)));
                $jitter        = $i % 3;
                $assignedTime  = $baseTime->copy()->addSeconds($offsetSeconds + $jitter);
                $now           = now()->format('Y-m-d H:i:s');

                DB::transaction(function () use ($p, $checkpoint, $assignedTime, $eventId, $now) {

                    // Cek sekali lagi di dalam transaction (race condition safety)
                    $alreadyExists = DB::selectOne(
                        'SELECT id FROM rfid_validated_times
                         WHERE participant_id = ? AND rfid_checkpoint_id = ? LIMIT 1',
                        [$p->participant_id, $checkpoint->id]
                    );
                    if ($alreadyExists) {
                        return; // sudah masuk real-time saat command jalan, skip
                    }

                    // Insert raw log
                    $rawLogId = DB::table('rfid_raw_logs')->insertGetId([
                        'event_id'           => $eventId,
                        'rfid_checkpoint_id' => $checkpoint->id,
                        'rfid_tag'           => $p->rfid_tag,
                        'bib'                => $p->bib,
                        'scanned_at'         => $assignedTime->format('Y-m-d H:i:s'),
                        'reader_id'          => 'BACKFILL',
                        'signal_strength'    => null,
                        'is_valid'           => true,
                        'notes'              => 'Backfill by rfid:backfill-start command',
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    ]);

                    // Hitung posisi di checkpoint ini
                    $posRow   = DB::selectOne(
                        'SELECT COUNT(*) as cnt FROM rfid_validated_times WHERE rfid_checkpoint_id = ?',
                        [$checkpoint->id]
                    );
                    $position = ($posRow->cnt ?? 0) + 1;

                    // Insert validated time
                    // START checkpoint: elapsed & split tetap null (belum ada reference finish)
                    DB::table('rfid_validated_times')->insert([
                        'participant_id'         => $p->participant_id,
                        'rfid_checkpoint_id'     => $checkpoint->id,
                        'rfid_raw_log_id'        => $rawLogId,
                        'checkpoint_time'        => $assignedTime->format('Y-m-d H:i:s'),
                        'elapsed_time'           => null,
                        'split_time'             => null,
                        'position_at_checkpoint' => $position,
                        'validation_status'      => 'manual',
                        'validation_notes'       => 'Backfill — not detected by RFID reader at start gate',
                        'validated_by'           => null,
                        'created_at'             => $now,
                        'updated_at'             => $now,
                    ]);
                });

                $bar->advance();
            }

            $bar->finish();
            $this->line('');
            $this->info("   ✓ Backfill selesai: {$count} peserta.");
            $this->line('');

            $totalBackfilled += $count;
        }

        if (!$dryRun && $totalBackfilled > 0) {
            $this->info("══════════════════════════════════════════");
            $this->info("Total peserta di-backfill: {$totalBackfilled}");
            $this->info("══════════════════════════════════════════");
        }

        return self::SUCCESS;
    }
}
