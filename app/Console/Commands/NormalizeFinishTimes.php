<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NormalizeFinishTimes extends Command
{
    protected $signature = 'rfid:normalize-finish
                            {event_id : ID event}
                            {--category= : Hanya kategori tertentu (event_category_id), opsional}
                            {--force : Timpa elapsed_time & gun_elapsed_time yang sudah ada}
                            {--dry-run : Tampilkan saja tanpa simpan ke DB}';

    protected $description = 'Hitung ulang elapsed_time & gun_elapsed_time untuk finisher yang datanya kosong (atau semua jika --force).';

    public function handle(): int
    {
        $eventId    = (int) $this->argument('event_id');
        $categoryId = $this->option('category') ? (int) $this->option('category') : null;
        $force      = $this->option('force');
        $dryRun     = $this->option('dry-run');

        // ── Validasi event ────────────────────────────────────────────────────
        $event = DB::selectOne(
            'SELECT id, name FROM events WHERE id = ? AND is_published = 1 LIMIT 1',
            [$eventId]
        );
        if (!$event) {
            $this->error("Event ID {$eventId} tidak ditemukan atau belum dipublish.");
            return self::FAILURE;
        }

        $this->info("Event     : [{$event->id}] {$event->name}");
        $this->info("Force     : " . ($force  ? 'YA (timpa semua)' : 'TIDAK (hanya yang kosong)'));
        $this->info("Dry run   : " . ($dryRun ? 'YA' : 'TIDAK'));
        $this->line('');

        // ── Ambil finisher yang perlu dinormalize ─────────────────────────────
        //
        // Kondisi target:
        //   • punya finish validated_time
        //   • punya start  validated_time  (atau akan di-fallback ke gun_time)
        //   • elapsed_time / gun_elapsed_time masih NULL  — kecuali --force
        $nullCondition = $force ? '' : 'AND (p.elapsed_time IS NULL OR p.gun_elapsed_time IS NULL)';
        $categoryBind  = '';
        $bindings      = [$eventId];

        if ($categoryId) {
            $categoryBind = 'AND p.event_category_id = ?';
            $bindings[]   = $categoryId;
        }

        $participants = DB::select("
            SELECT
                p.id             AS participant_id,
                p.bib,
                p.event_category_id,
                p.elapsed_time,
                p.gun_elapsed_time,

                -- finish time dari rfid_validated_times
                (
                    SELECT vt.checkpoint_time
                    FROM rfid_validated_times vt
                    JOIN rfid_checkpoints cp ON cp.id = vt.rfid_checkpoint_id
                    WHERE vt.participant_id = p.id
                      AND cp.checkpoint_type = 'finish'
                      AND cp.is_active = 1
                    ORDER BY vt.checkpoint_time ASC
                    LIMIT 1
                ) AS finish_time,

                -- start time dari rfid_validated_times
                (
                    SELECT vt.checkpoint_time
                    FROM rfid_validated_times vt
                    JOIN rfid_checkpoints cp ON cp.id = vt.rfid_checkpoint_id
                    WHERE vt.participant_id = p.id
                      AND cp.checkpoint_type = 'start'
                      AND cp.is_active = 1
                    ORDER BY vt.checkpoint_time ASC
                    LIMIT 1
                ) AS start_time,

                -- gun_time dari event_categories
                ec.gun_time

            FROM participants p
            LEFT JOIN event_categories ec ON ec.id = p.event_category_id
            WHERE p.event_id = ?
              {$categoryBind}
              {$nullCondition}
              -- harus sudah ada finish time
              AND EXISTS (
                  SELECT 1
                  FROM rfid_validated_times vt2
                  JOIN rfid_checkpoints cp2 ON cp2.id = vt2.rfid_checkpoint_id
                  WHERE vt2.participant_id = p.id
                    AND cp2.checkpoint_type = 'finish'
                    AND cp2.is_active = 1
              )
            ORDER BY p.event_category_id ASC, p.bib ASC
        ", $bindings);

        if (empty($participants)) {
            $this->info('Tidak ada peserta yang perlu dinormalize. Selesai.');
            return self::SUCCESS;
        }

        $this->warn("Ditemukan " . count($participants) . " peserta yang akan diproses:");
        $this->line('');

        // ── Preview tabel ─────────────────────────────────────────────────────
        $previewRows = [];
        foreach ($participants as $p) {
            [$chipTime, $gunTime, $note] = $this->compute($p);
            $previewRows[] = [
                $p->bib,
                $p->participant_id,
                $p->start_time  ?? '(null)',
                $p->finish_time ?? '(null)',
                $p->gun_time    ?? '(null)',
                $chipTime ?? '—',
                $gunTime  ?? '—',
                $note,
            ];
        }

        $this->table(
            ['BIB', 'Participant ID', 'Start Time', 'Finish Time', 'Gun Time', 'Chip Time (baru)', 'Gun Time (baru)', 'Keterangan'],
            $previewRows
        );

        if ($dryRun) {
            $this->line('');
            $this->info('[DRY RUN] Tidak ada perubahan ke database.');
            return self::SUCCESS;
        }

        if (!$this->confirm('Proses update ke database?', true)) {
            $this->info('Dibatalkan.');
            return self::SUCCESS;
        }

        // ── Proses update ─────────────────────────────────────────────────────
        $bar     = $this->output->createProgressBar(count($participants));
        $updated = 0;
        $skipped = 0;
        $errors  = 0;

        $bar->start();

        foreach ($participants as $p) {
            [$chipTime, $gunTime] = $this->compute($p);

            if ($chipTime === null && $gunTime === null) {
                // Tidak bisa hitung apa-apa, lewati
                $skipped++;
                $bar->advance();
                continue;
            }

            try {
                // Bangun SET clause dinamis — jangan timpa nilai yang sudah ada
                // kecuali --force
                $sets     = [];
                $vals     = [];

                if ($chipTime !== null && ($force || $p->elapsed_time === null)) {
                    $sets[] = 'elapsed_time = ?';
                    $vals[] = $chipTime;
                }

                if ($gunTime !== null && ($force || $p->gun_elapsed_time === null)) {
                    $sets[] = 'gun_elapsed_time = ?';
                    $vals[] = $gunTime;
                }

                if (empty($sets)) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $vals[] = $p->participant_id;

                DB::update(
                    'UPDATE participants SET ' . implode(', ', $sets) . ' WHERE id = ?',
                    $vals
                );

                Log::info('NormalizeFinishTimes: updated', [
                    'participant_id' => $p->participant_id,
                    'bib'            => $p->bib,
                    'chip_time'      => $chipTime,
                    'gun_time'       => $gunTime,
                ]);

                $updated++;
            } catch (\Throwable $e) {
                Log::error('NormalizeFinishTimes: error', [
                    'participant_id' => $p->participant_id,
                    'error'          => $e->getMessage(),
                ]);
                $errors++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line('');
        $this->line('');

        // ── Ringkasan ─────────────────────────────────────────────────────────
        $this->info('══════════════════════════════════════════');
        $this->info("✓ Updated  : {$updated}");
        $this->warn("⚠ Skipped  : {$skipped}  (finish time ada, start & gun_time tidak)");
        if ($errors > 0) {
            $this->error("✗ Error    : {$errors}");
        }
        $this->info('══════════════════════════════════════════');

        // Hint: re-run RecalculatePositions kalau perlu
        if ($updated > 0) {
            $this->line('');
            $this->line('Posisi belum otomatis di-recalculate.');
            $this->line('Jalankan juga jika perlu:');
            $this->line("  php artisan rfid:recalculate-positions {$eventId}");
        }

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Hitung chip_time (elapsed) dan gun_time untuk satu peserta.
     *
     * Prioritas chip_time  : finish_time − start_time
     * Fallback chip_time   : finish_time − gun_time  (jika start tidak ada)
     * gun_elapsed_time     : finish_time − gun_time  (selalu dari gun_time kategori)
     *
     * Return: [chipTime|null, gunTime|null, $note]
     */
    private function compute(object $p): array
    {
        if (!$p->finish_time) {
            return [null, null, 'no finish time'];
        }

        $finishTs = Carbon::parse($p->finish_time)->timestamp;

        // ── Chip time ──────────────────────────────────────────────────────────
        $chipTime = null;
        $note     = '';

        if ($p->start_time) {
            $startTs = Carbon::parse($p->start_time)->timestamp;
            $elapsed = $finishTs - $startTs;

            if ($elapsed >= 0) {
                $chipTime = gmdate('H:i:s', $elapsed);
                $note     = 'finish − start';
            } else {
                $note = 'start > finish (skip chip)';
            }
        } elseif ($p->gun_time) {
            // Tidak ada start time → fallback ke gun_time sebagai start
            $gunTs   = Carbon::parse($p->gun_time)->timestamp;
            $elapsed = $finishTs - $gunTs;

            if ($elapsed >= 0) {
                $chipTime = gmdate('H:i:s', $elapsed);
                $note     = 'finish − gun_time (start missing)';
            } else {
                $note = 'gun_time > finish (skip chip)';
            }
        } else {
            $note = 'no start & no gun_time';
        }

        // ── Gun elapsed time ───────────────────────────────────────────────────
        $gunTime = null;

        if ($p->gun_time) {
            $gunTs   = Carbon::parse($p->gun_time)->timestamp;
            $elapsed = $finishTs - $gunTs;

            if ($elapsed >= 0) {
                $gunTime = gmdate('H:i:s', $elapsed);
            }
        }

        return [$chipTime, $gunTime, $note];
    }
}
