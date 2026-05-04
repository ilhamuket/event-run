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

    protected $description = 'Normalisasi general_position & category_position berdasarkan elapsed_time di validated_times finish, dipisah per gender. Aman dijalankan berkali-kali.';

    public function handle(): int
    {
        $eventId    = (int) $this->argument('event_id');
        $categoryId = $this->option('category') ? (int) $this->option('category') : null;
        $dryRun     = $this->option('dry-run');

        $event = DB::selectOne('SELECT id, name FROM events WHERE id = ? LIMIT 1', [$eventId]);
        if (!$event) {
            $this->error("Event ID {$eventId} tidak ditemukan.");
            return self::FAILURE;
        }

        $this->info("Event   : [{$event->id}] {$event->name}");
        $this->info("Dry run : " . ($dryRun ? 'YA' : 'TIDAK'));
        $this->line('');

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

        // ══════════════════════════════════════════════════════
        // CATEGORY POSITION
        // Urutan: elapsed_time ASC di validated_times finish,
        // dipisah per gender dalam kategori yang sama.
        // Sama persis dengan sort di fetchLiveData finish group.
        // ══════════════════════════════════════════════════════
        foreach ($categories as $cat) {
            $this->line("── Kategori: [{$cat->id}] {$cat->name}");

            $cpFinish = DB::selectOne(
                'SELECT id FROM rfid_checkpoints
                 WHERE event_category_id = ? AND checkpoint_type = "finish" AND is_active = 1
                 LIMIT 1',
                [$cat->id]
            );

            if (!$cpFinish) {
                $this->warn("   ⚠ Tidak ada checkpoint FINISH aktif. Skip.");
                $this->line('');
                continue;
            }

            // Ambil semua peserta yang punya validated finish time,
            // urutkan persis seperti fetchLiveData: elapsed_time ASC,
            // fallback '99:99:99' kalau null (peserta paling belakang).
            $rows = DB::select("
                SELECT
                    p.id                    AS participant_id,
                    p.bib,
                    p.gender,
                    COALESCE(NULLIF(TRIM(p.bib_name),''), NULLIF(TRIM(p.name),''), 'PESERTA')
                                            AS display_name,
                    p.category_position     AS old_category,
                    vt.elapsed_time,
                    vt.checkpoint_time
                FROM participants p
                INNER JOIN rfid_validated_times vt
                    ON vt.participant_id     = p.id
                   AND vt.rfid_checkpoint_id = ?
                WHERE p.event_category_id = ?
                  AND p.event_id          = ?
                  AND p.gender IN ('M', 'F')
                  AND EXISTS (
                      SELECT 1 FROM transactions t
                      WHERE t.participant_id = p.id AND t.status = 'PAID' LIMIT 1
                  )
                ORDER BY
                    p.gender ASC,
                    COALESCE(vt.elapsed_time, '99:99:99') ASC,
                    vt.checkpoint_time ASC
            ", [$cpFinish->id, $cat->id, $eventId]);

            if (empty($rows)) {
                $this->line("   ✓ Tidak ada peserta finish di kategori ini. Skip.");
                $this->line('');
                continue;
            }

            // Pisah per gender, rank masing-masing dari 1
            $byGender = ['M' => [], 'F' => []];
            foreach ($rows as $row) {
                $byGender[$row->gender][] = $row;
            }

            $updates   = [];
            $tableRows = [];

            foreach (['M', 'F'] as $gender) {
                $gLabel = $gender === 'M' ? 'Pria' : 'Wanita';

                if (empty($byGender[$gender])) {
                    $this->line("   (tidak ada peserta {$gLabel})");
                    continue;
                }

                foreach ($byGender[$gender] as $rank => $row) {
                    $newCatPos = $rank + 1;
                    $updates[] = [
                        'participant_id' => $row->participant_id,
                        'new_category'   => $newCatPos,
                    ];
                    $tableRows[] = [
                        $gLabel,
                        $newCatPos,
                        $row->bib,
                        mb_substr($row->display_name, 0, 24),
                        $row->elapsed_time ?? '(null)',
                        $row->old_category ?? '(null)',
                    ];
                }
            }

            $this->table(['Gender', 'Cat.Pos', 'BIB', 'Nama', 'Elapsed Time', 'Old Cat.Pos'], $tableRows);

            if (!$dryRun) {
                DB::transaction(function () use ($updates) {
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
        // GENERAL POSITION — lintas semua kategori, TANPA pisah gender
        // ══════════════════════════════════════════════════════
        $this->line("── General Position (semua kategori, semua gender)");

        $allFinishCps = DB::select(
            'SELECT rc.id
             FROM rfid_checkpoints rc
             JOIN event_categories ec ON ec.id = rc.event_category_id
             WHERE ec.event_id = ?
               AND rc.checkpoint_type = "finish"
               AND rc.is_active = 1',
            [$eventId]
        );

        if (empty($allFinishCps)) {
            $this->warn("   ⚠ Tidak ada checkpoint FINISH aktif. General position dilewati.");
            $this->line('');
        } else {
            $finishCpIds   = array_column($allFinishCps, 'id');
            $fPlaceholders = implode(',', array_fill(0, count($finishCpIds), '?'));

            $bindings = array_merge($finishCpIds, [$eventId]);

            $allFinishers = DB::select("
                SELECT
                    p.id                AS participant_id,
                    p.bib,
                    p.gender,
                    p.event_category_id,
                    p.general_position  AS old_general,
                    vt.elapsed_time,
                    vt.checkpoint_time
                FROM participants p
                INNER JOIN rfid_validated_times vt
                    ON vt.participant_id     = p.id
                   AND vt.rfid_checkpoint_id IN ({$fPlaceholders})
                WHERE p.event_id = ?
                  AND EXISTS (
                      SELECT 1 FROM transactions t
                      WHERE t.participant_id = p.id AND t.status = 'PAID' LIMIT 1
                  )
                ORDER BY
                    COALESCE(vt.elapsed_time, '99:99:99') ASC,
                    vt.checkpoint_time ASC
            ", $bindings);

            $this->info("   Total finisher (semua kategori): " . count($allFinishers));

            $generalUpdates = [];
            $generalRows    = [];

            foreach ($allFinishers as $rank => $row) {
                $newGeneral   = $rank + 1;
                $shouldUpdate = !$categoryId || (int) $row->event_category_id === $categoryId;

                $generalUpdates[] = [
                    'participant_id' => $row->participant_id,
                    'new_general'    => $newGeneral,
                    'should_update'  => $shouldUpdate,
                ];

                $generalRows[] = [
                    $newGeneral,
                    $row->bib,
                    $row->gender,
                    $row->elapsed_time ?? '(null)',
                    $row->old_general  ?? '(null)',
                    $shouldUpdate ? 'YA' : 'skip',
                ];
            }

            $this->table(['Overall', 'BIB', 'Gender', 'Elapsed Time', 'Old Overall', 'Update?'], $generalRows);

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
