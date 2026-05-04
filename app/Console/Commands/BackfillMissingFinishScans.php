<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillMissingFinishScans extends Command
{
    protected $signature = 'rfid:backfill-finish
                            {event_id : ID event}
                            {--category= : Hanya kategori tertentu (event_category_id), opsional}
                            {--dry-run : Tampilkan saja tanpa simpan ke DB}';

    protected $description = 'Backfill raw log + validated time untuk peserta yang belum ter-detect di checkpoint FINISH. Waktu finish diambil dari rata-rata peserta terlambat di kategori yang sama.';

    public function handle(): int
    {
        $eventId    = (int) $this->argument('event_id');
        $categoryId = $this->option('category') ? (int) $this->option('category') : null;
        $dryRun     = $this->option('dry-run');

        // ── Validasi event ─────────────────────────────────────────────────
        $event = DB::selectOne(
            'SELECT id FROM events WHERE id = ? AND is_published = 1 LIMIT 1',
            [$eventId]
        );
        if (!$event) {
            $this->error("Event ID {$eventId} tidak ditemukan atau belum dipublish.");
            return self::FAILURE;
        }

        // ── Ambil semua checkpoint FINISH yang aktif ───────────────────────
        $checkpointQuery = '
            SELECT rc.id, rc.checkpoint_name, rc.checkpoint_order, rc.event_category_id
            FROM rfid_checkpoints rc
            WHERE rc.checkpoint_type = "finish"
              AND rc.is_active = 1
              AND rc.event_category_id IN (
                  SELECT id FROM event_categories WHERE event_id = ?
              )
        ';
        $bindings = [$eventId];

        if ($categoryId) {
            $checkpointQuery .= ' AND rc.event_category_id = ?';
            $bindings[]       = $categoryId;
        }

        $checkpoints = DB::select($checkpointQuery, $bindings);

        if (empty($checkpoints)) {
            $this->error('Tidak ada checkpoint FINISH aktif untuk event ini.');
            return self::FAILURE;
        }

        $this->info("Event ID : {$eventId}");
        $this->info("Dry run  : " . ($dryRun ? 'YA' : 'TIDAK'));
        $this->line('');

        $totalBackfilled = 0;

        foreach ($checkpoints as $checkpoint) {
            $this->line("── Checkpoint: [{$checkpoint->id}] {$checkpoint->checkpoint_name} (category {$checkpoint->event_category_id})");

            // ── Hitung waktu rata-rata finish peserta terlambat ───────────────
            //
            // Strategi: ambil rata-rata dari 25% peserta paling lambat finish
            // di kategori ini. Tujuannya agar synthetic finish time masuk di
            // belakang pack, tidak merebut posisi peserta yang benar-benar finish.
            //
            // Kalau peserta yang sudah finish < 4 orang, pakai semua saja.
            $totalFinished = DB::selectOne(
                'SELECT COUNT(*) as cnt FROM rfid_validated_times WHERE rfid_checkpoint_id = ?',
                [$checkpoint->id]
            );
            $finishedCount = (int) ($totalFinished->cnt ?? 0);

            if ($finishedCount === 0) {
                // Tidak ada satupun yang finish — fallback ke gun_time + batas wajar
                $category = DB::selectOne(
                    'SELECT gun_time FROM event_categories WHERE id = ? LIMIT 1',
                    [$checkpoint->event_category_id]
                );

                if ($category && $category->gun_time) {
                    // Asumsikan DNF finish di gun_time + 2 jam sebagai placeholder
                    $syntheticFinish = Carbon::parse($category->gun_time)->addHours(2);
                    $this->warn("   ⚠ Belum ada peserta finish sama sekali.");
                    $this->info("   Synthetic finish (gun+2h) : {$syntheticFinish->format('Y-m-d H:i:s')}");
                } else {
                    $this->error("   ✗ Tidak ada data finish maupun gun_time. Skip checkpoint ini.");
                    $this->line('');
                    continue;
                }
            } else {
                // Ambil 25% paling lambat, minimal 1 baris
                $slowTail = max(1, (int) ceil($finishedCount * 0.25));

                $avgRow = DB::selectOne("
                    SELECT AVG(UNIX_TIMESTAMP(checkpoint_time)) AS avg_ts
                    FROM (
                        SELECT checkpoint_time
                        FROM rfid_validated_times
                        WHERE rfid_checkpoint_id = ?
                        ORDER BY checkpoint_time DESC
                        LIMIT ?
                    ) AS slow_tail
                ", [$checkpoint->id, $slowTail]);

                if (!$avgRow || !$avgRow->avg_ts) {
                    $this->error("   ✗ Gagal menghitung rata-rata finish. Skip.");
                    $this->line('');
                    continue;
                }

                $syntheticFinish = Carbon::createFromTimestamp((int) round($avgRow->avg_ts));

                $this->info("   Peserta sudah finish      : {$finishedCount}");
                $this->info("   Sampel rata-rata (25% akhir): {$slowTail} peserta");
                $this->info("   Synthetic finish time      : {$syntheticFinish->format('Y-m-d H:i:s')}");
            }

            // ── Ambil peserta yang BELUM punya validated time finish ──────────
            // PENTING: hanya ambil yang BELUM ada — tidak menyentuh yang sudah ada
            $missing = DB::select('
                SELECT
                    p.id        AS participant_id,
                    p.bib,
                    m.rfid_tag,
                    vt_start.checkpoint_time AS start_time,
                    ec.gun_time
                FROM participants p
                JOIN participant_rfid_mappings m
                    ON m.participant_id = p.id AND m.is_active = 1
                JOIN event_categories ec
                    ON ec.id = p.event_category_id
                LEFT JOIN rfid_checkpoints cp_start
                    ON cp_start.event_category_id = p.event_category_id
                    AND cp_start.checkpoint_type = "start"
                    AND cp_start.is_active = 1
                LEFT JOIN rfid_validated_times vt_start
                    ON vt_start.participant_id = p.id
                    AND vt_start.rfid_checkpoint_id = cp_start.id
                WHERE p.event_category_id = ?
                  AND p.event_id = ?
                  AND NOT EXISTS (
                      SELECT 1 FROM rfid_validated_times vt
                      WHERE vt.participant_id    = p.id
                        AND vt.rfid_checkpoint_id = ?
                  )
                ORDER BY p.bib ASC
            ', [$checkpoint->event_category_id, $eventId, $checkpoint->id]);

            if (empty($missing)) {
                $this->line("   ✓ Semua peserta sudah punya finish time. Skip.");
                $this->line('');
                continue;
            }

            $this->warn("   Ditemukan " . count($missing) . " peserta belum finish:");

            // ── Preview ───────────────────────────────────────────────────────
            $headers = ['BIB', 'Participant ID', 'Start Time', 'Synthetic Finish', 'Elapsed', 'Gun Elapsed'];
            $rows    = [];

            foreach ($missing as $i => $p) {
                // Sebar tipis: +1 detik per peserta agar tidak persis sama
                $finishTime = $syntheticFinish->copy()->addSeconds($i + 1);

                // Hitung elapsed (finish - start rfid, atau finish - gun_time)
                $startTs     = $p->start_time
                    ? Carbon::parse($p->start_time)
                    : ($p->gun_time ? Carbon::parse($p->gun_time) : null);

                $elapsedStr    = '-';
                $gunElapsedStr = '-';

                if ($startTs) {
                    $elapsedSecs = $finishTime->timestamp - $startTs->timestamp;
                    $elapsedStr  = $elapsedSecs >= 0 ? gmdate('H:i:s', $elapsedSecs) : 'NEGATIF';
                }

                if ($p->gun_time) {
                    $gunSecs       = $finishTime->timestamp - Carbon::parse($p->gun_time)->timestamp;
                    $gunElapsedStr = $gunSecs >= 0 ? gmdate('H:i:s', $gunSecs) : 'NEGATIF';
                }

                $rows[] = [
                    $p->bib,
                    $p->participant_id,
                    $p->start_time ?? '(tidak ada)',
                    $finishTime->format('Y-m-d H:i:s'),
                    $elapsedStr,
                    $gunElapsedStr,
                ];
            }

            $this->table($headers, $rows);

            if ($dryRun) {
                $this->line("   [DRY RUN] Tidak ada perubahan ke DB.");
                $this->line('');
                continue;
            }

            if (!$this->confirm("   Proses backfill finish untuk checkpoint ini?", true)) {
                $this->line("   Skip oleh user.");
                $this->line('');
                continue;
            }

            // ── Insert ke DB ──────────────────────────────────────────────────
            $count = count($missing);
            $bar   = $this->output->createProgressBar($count);
            $bar->start();

            foreach ($missing as $i => $p) {
                $finishTime = $syntheticFinish->copy()->addSeconds($i + 1);
                $now        = now()->format('Y-m-d H:i:s');

                // Hitung elapsed & gun_elapsed
                $startTs = $p->start_time
                    ? Carbon::parse($p->start_time)
                    : ($p->gun_time ? Carbon::parse($p->gun_time) : null);

                $elapsedTime    = null;
                $gunElapsedTime = null;

                if ($startTs) {
                    $elapsedSecs = $finishTime->timestamp - $startTs->timestamp;
                    if ($elapsedSecs >= 0) {
                        $elapsedTime = gmdate('H:i:s', $elapsedSecs);
                    }
                }

                if ($p->gun_time) {
                    $gunSecs = $finishTime->timestamp - Carbon::parse($p->gun_time)->timestamp;
                    if ($gunSecs >= 0) {
                        $gunElapsedTime = gmdate('H:i:s', $gunSecs);
                    }
                }

                DB::transaction(function () use (
                    $p, $checkpoint, $finishTime,
                    $elapsedTime, $gunElapsedTime,
                    $eventId, $now
                ) {
                    // Guard: cek sekali lagi dalam transaction — jangan timpa yang sudah ada
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
                        'scanned_at'         => $finishTime->format('Y-m-d H:i:s'),
                        'reader_id'          => 'BACKFILL',
                        'signal_strength'    => null,
                        'is_valid'           => true,
                        'notes'              => 'Backfill by rfid:backfill-finish command (tag miss at finish gate)',
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    ]);

                    // Posisi di checkpoint ini (append ke belakang)
                    $posRow   = DB::selectOne(
                        'SELECT COUNT(*) as cnt FROM rfid_validated_times WHERE rfid_checkpoint_id = ?',
                        [$checkpoint->id]
                    );
                    $position = ($posRow->cnt ?? 0) + 1;

                    // Insert validated time finish
                    DB::table('rfid_validated_times')->insert([
                        'participant_id'         => $p->participant_id,
                        'rfid_checkpoint_id'     => $checkpoint->id,
                        'rfid_raw_log_id'        => $rawLogId,
                        'checkpoint_time'        => $finishTime->format('Y-m-d H:i:s'),
                        'elapsed_time'           => $elapsedTime,
                        'split_time'             => null,
                        'position_at_checkpoint' => $position,
                        'validation_status'      => 'auto',
                        'created_at'             => $now,
                        'updated_at'             => $now,
                    ]);

                    // Update participants — elapsed & gun_elapsed
                    DB::update("
                        UPDATE participants
                        SET elapsed_time     = ?,
                            gun_elapsed_time = ?,
                            updated_at       = ?
                        WHERE id = ?
                    ", [
                        $elapsedTime,
                        $gunElapsedTime,
                        $now,
                        $p->participant_id,
                    ]);
                });

                $bar->advance();
            }

            $bar->finish();
            $this->line('');
            $this->info("   ✓ Backfill finish selesai: {$count} peserta.");
            $this->line('');

            $totalBackfilled += $count;
        }

        if (!$dryRun && $totalBackfilled > 0) {
            $this->info("══════════════════════════════════════════");
            $this->info("Total peserta di-backfill finish: {$totalBackfilled}");
            $this->info("══════════════════════════════════════════");
        }

        return self::SUCCESS;
    }
}
