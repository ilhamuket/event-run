<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeParticipantPositions extends Command
{
    protected $signature = 'rfid:normalize-positions
                            {event_id : ID event}
                            {--category= : Hanya kategori tertentu (event_category_id), opsional}
                            {--dry-run : Tampilkan preview tanpa simpan ke DB}';

    protected $description = 'Normalisasi general_position & category_position berdasarkan chip time (finish - start rfid). Aman dijalankan berkali-kali.';

    public function handle(): int
    {
        $eventId    = (int) $this->argument('event_id');
        $categoryId = $this->option('category') ? (int) $this->option('category') : null;
        $dryRun     = $this->option('dry-run');

        // ── Validasi event ──────────────────────────────────────────────
        $event = DB::selectOne(
            'SELECT id, name FROM events WHERE id = ? LIMIT 1',
            [$eventId]
        );
        if (!$event) {
            $this->error("Event ID {$eventId} tidak ditemukan.");
            return self::FAILURE;
        }

        $this->info("Event    : [{$event->id}] {$event->name}");
        $this->info("Dry run  : " . ($dryRun ? 'YA' : 'TIDAK'));
        $this->line('');

        // ── Ambil semua kategori aktif ──────────────────────────────────
        $catQuery    = 'SELECT id, name FROM event_categories WHERE event_id = ? AND is_active = 1';
        $catBindings = [$eventId];

        if ($categoryId) {
            $catQuery    .= ' AND id = ?';
            $catBindings[] = $categoryId;
        }

        $categories = DB::select($catQuery, $catBindings);

        if (empty($categories)) {
            $this->error('Tidak ada kategori aktif ditemukan.');
            return self::FAILURE;
        }

        $totalUpdated = 0;

        foreach ($categories as $cat) {
            $this->line("── Kategori: [{$cat->id}] {$cat->name}");

            // ── Ambil checkpoint FINISH & START aktif untuk kategori ini ─
            $cpFinish = DB::selectOne(
                'SELECT id FROM rfid_checkpoints
                 WHERE event_category_id = ? AND checkpoint_type = "finish" AND is_active = 1
                 LIMIT 1',
                [$cat->id]
            );

            $cpStart = DB::selectOne(
                'SELECT id FROM rfid_checkpoints
                 WHERE event_category_id = ? AND checkpoint_type = "start" AND is_active = 1
                 LIMIT 1',
                [$cat->id]
            );

            if (!$cpFinish) {
                $this->warn("   ⚠ Tidak ada checkpoint FINISH aktif. Skip.");
                $this->line('');
                continue;
            }

            // ── Ambil semua peserta yang punya validated finish time ─────
            // Chip time = finish_checkpoint_time - start_checkpoint_time
            // Kalau tidak ada start checkpoint / tidak ada scan start,
            // fallback ke elapsed_time yang tersimpan di participants.
            $rows = DB::select("
                SELECT
                    p.id                         AS participant_id,
                    p.bib,
                    COALESCE(NULLIF(TRIM(p.bib_name),''), NULLIF(TRIM(p.name),''), 'PESERTA')
                                                AS display_name,
                    p.general_position           AS old_general,
                    p.category_position          AS old_category,
                    vt_finish.checkpoint_time    AS finish_time,
                    vt_start.checkpoint_time     AS start_time,
                    CASE
                        WHEN vt_start.checkpoint_time IS NOT NULL
                        THEN TIMESTAMPDIFF(SECOND, vt_start.checkpoint_time, vt_finish.checkpoint_time)
                        ELSE NULL
                    END                          AS chip_seconds
                FROM participants p
                INNER JOIN rfid_validated_times vt_finish
                    ON vt_finish.participant_id    = p.id
                AND vt_finish.rfid_checkpoint_id = ?
                LEFT JOIN rfid_validated_times vt_start
                    ON vt_start.participant_id    = p.id
                AND vt_start.rfid_checkpoint_id = ?
                WHERE p.event_category_id = ?
                AND p.event_id          = ?
                AND EXISTS (
                    SELECT 1 FROM transactions t
                    WHERE t.participant_id = p.id AND t.status = 'PAID' LIMIT 1
                )
                ORDER BY chip_seconds ASC, vt_finish.checkpoint_time ASC
            ", [
                $cpFinish->id,
                $cpStart ? $cpStart->id : 0,
                $cat->id,
                $eventId,
            ]);

            if (empty($rows)) {
                $this->line("   ✓ Tidak ada peserta finish di kategori ini. Skip.");
                $this->line('');
                continue;
            }

            $this->info("   Peserta finish: " . count($rows));

            // ── Assign category_position berdasarkan urutan chip time ────
            $headers = ['Cat.Pos', 'BIB', 'Nama', 'Chip Time (s)', 'Finish Time', 'Old Cat.Pos'];
            $tableRows = [];

            $updates = [];
            foreach ($rows as $rank => $row) {
                $newCatPos = $rank + 1;
                $updates[] = [
                    'participant_id' => $row->participant_id,
                    'new_category'   => $newCatPos,
                    'old_category'   => $row->old_category,
                ];

                $tableRows[] = [
                    $newCatPos,
                    $row->bib,
                    mb_substr($row->display_name, 0, 28),
                    $row->chip_seconds,
                    $row->finish_time,
                    $row->old_category ?? '(null)',
                ];
            }

            $this->table($headers, $tableRows);

            if (!$dryRun) {
                // Wrap seluruh kategori dalam satu transaksi
                DB::transaction(function () use ($updates, $cat) {
                    foreach ($updates as $u) {
                        DB::update(
                            'UPDATE participants SET category_position = ?, updated_at = NOW() WHERE id = ?',
                            [$u['new_category'], $u['participant_id']]
                        );
                    }
                });

                $this->info("   ✓ category_position diperbarui: " . count($updates) . " peserta.");
            } else {
                $this->line("   [DRY RUN] category_position tidak disimpan.");
            }

            $totalUpdated += count($updates);
            $this->line('');
        }

        // ══════════════════════════════════════════════════════
        // GENERAL POSITION — lintas semua kategori dalam event
        // Urutkan semua peserta finish berdasarkan chip time
        // ══════════════════════════════════════════════════════
        $this->line("── General Position (semua kategori)");

        // Kumpulkan semua checkpoint finish aktif dalam event
        $allFinishCps = DB::select(
            'SELECT rc.id, rc.event_category_id
             FROM rfid_checkpoints rc
             JOIN event_categories ec ON ec.id = rc.event_category_id
             WHERE ec.event_id = ? AND rc.checkpoint_type = "finish" AND rc.is_active = 1',
            [$eventId]
        );

        // Buat map: event_category_id → finish_cp_id dan start_cp_id
        $finishCpMap = [];
        $startCpMap  = [];
        foreach ($allFinishCps as $cp) {
            $finishCpMap[$cp->event_category_id] = $cp->id;
        }

        $allStartCps = DB::select(
            'SELECT rc.id, rc.event_category_id
             FROM rfid_checkpoints rc
             JOIN event_categories ec ON ec.id = rc.event_category_id
             WHERE ec.event_id = ? AND rc.checkpoint_type = "start" AND rc.is_active = 1',
            [$eventId]
        );
        foreach ($allStartCps as $cp) {
            $startCpMap[$cp->event_category_id] = $cp->id;
        }

        if (empty($finishCpMap)) {
            $this->warn("   ⚠ Tidak ada checkpoint FINISH aktif di event ini. General position dilewati.");
            $this->line('');
        } else {
            // Satu query besar — semua peserta finish di event, diurutkan chip time
            $finishIds = array_values($finishCpMap);
            $startIds  = array_values($startCpMap);

            $fPlaceholders = implode(',', array_fill(0, count($finishIds), '?'));
            $sPlaceholders = count($startIds)
                ? implode(',', array_fill(0, count($startIds), '?'))
                : '0';   // dummy agar LEFT JOIN tidak error

            $bindings = array_merge(
                $finishIds,
                $startIds,
                [$eventId]
            );

            // Filter event_category_id kalau ada --category
            $catFilter = '';
            if ($categoryId) {
                // Dalam mode --category kita tetap hitung semua untuk konsistensi,
                // tapi hanya update peserta di kategori tersebut.
                // General position harus konsisten lintas kategori, jadi tetap
                // ambil semua peserta, filter update saja.
            }

            $allFinishers = DB::select("
                SELECT
                    p.id                      AS participant_id,
                    p.bib,
                    p.event_category_id,
                    p.general_position        AS old_general,
                    vt_finish.checkpoint_time AS finish_time,
                    vt_start.checkpoint_time  AS start_time,
                    CASE
                        WHEN vt_start.checkpoint_time IS NOT NULL
                        THEN TIMESTAMPDIFF(SECOND, vt_start.checkpoint_time, vt_finish.checkpoint_time)
                        ELSE NULL
                    END                       AS chip_seconds
                FROM participants p
                INNER JOIN rfid_validated_times vt_finish
                    ON vt_finish.participant_id     = p.id
                AND vt_finish.rfid_checkpoint_id IN ({$fPlaceholders})
                LEFT JOIN rfid_validated_times vt_start
                    ON vt_start.participant_id     = p.id
                AND vt_start.rfid_checkpoint_id IN ({$sPlaceholders})
                WHERE p.event_id = ?
                AND EXISTS (
                    SELECT 1 FROM transactions t
                    WHERE t.participant_id = p.id AND t.status = 'PAID' LIMIT 1
                )
                ORDER BY chip_seconds ASC, vt_finish.checkpoint_time ASC
            ", $bindings);

            $this->info("   Total peserta finish (semua kategori): " . count($allFinishers));

            $generalHeaders = ['Overall', 'BIB', 'Chip (s)', 'Finish Time', 'Old Overall'];
            $generalRows    = [];
            $generalUpdates = [];

            foreach ($allFinishers as $rank => $row) {
                $newGeneral = $rank + 1;

                // Kalau mode --category, skip update peserta kategori lain
                $shouldUpdate = !$categoryId || $row->event_category_id === $categoryId;

                $generalUpdates[] = [
                    'participant_id' => $row->participant_id,
                    'new_general'    => $newGeneral,
                    'old_general'    => $row->old_general,
                    'should_update'  => $shouldUpdate,
                ];

                $generalRows[] = [
                    $newGeneral,
                    $row->bib,
                    $row->chip_seconds,
                    $row->finish_time,
                    $row->old_general ?? '(null)',
                ];
            }

            $this->table($generalHeaders, $generalRows);

            if (!$dryRun) {
                DB::transaction(function () use ($generalUpdates) {
                    foreach ($generalUpdates as $u) {
                        if (!$u['should_update']) continue;
                        DB::update(
                            'UPDATE participants SET general_position = ?, updated_at = NOW() WHERE id = ?',
                            [$u['new_general'], $u['participant_id']]
                        );
                    }
                });

                $updated = collect($generalUpdates)->where('should_update', true)->count();
                $this->info("   ✓ general_position diperbarui: {$updated} peserta.");
            } else {
                $this->line("   [DRY RUN] general_position tidak disimpan.");
            }

            $this->line('');
        }

        if (!$dryRun && $totalUpdated > 0) {
            $this->info("══════════════════════════════════════════");
            $this->info("Selesai. Total diproses: {$totalUpdated} peserta (category_position).");
            $this->info("Jalankan ulang kapan saja — aman, selalu recalculate dari awal.");
            $this->info("══════════════════════════════════════════");
        }

        return self::SUCCESS;
    }
}
