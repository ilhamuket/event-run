<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecalculatePositions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly int $categoryId,
        public readonly int $eventId,
    ) {}

    public function handle(): void
    {
        $lock = Cache::lock("recalc_positions_{$this->categoryId}", 30);

        try {
            $lock->block(0);
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            Log::info('RecalculatePositions: lock busy, akan retry', [
                'category_id' => $this->categoryId,
            ]);
            $this->release(5);
            return;
        }

        try {
            $this->recalculate();
        } finally {
            $lock->release();
        }
    }

    private function recalculate(): void
    {
        // Tentukan kolom ranking yang dipakai untuk kategori ini.
        //
        // Kalau gun_time sudah di-set di kategori → ranking pakai gun_elapsed_time.
        // Kalau belum di-set → ranking tetap pakai elapsed_time (chip time).
        //
        // Ini penting: gun_time yang belum di-set berarti race belum dimulai
        // atau admin belum input, jadi jangan paksa ranking pakai gun time
        // yang nilainya null semua.
        $category = DB::selectOne(
            'SELECT gun_time FROM event_categories WHERE id = ? LIMIT 1',
            [$this->categoryId]
        );

        $rankColumn = ($category && $category->gun_time)
            ? 'gun_elapsed_time'
            : 'elapsed_time';

        Log::info('RecalculatePositions: ranking column', [
            'category_id' => $this->categoryId,
            'rank_column' => $rankColumn,
        ]);

        // ── Category positions ───────────────────────────────────────────────
        $categoryFinishers = DB::select(
            "SELECT id FROM participants
             WHERE event_category_id = ? AND {$rankColumn} IS NOT NULL
             ORDER BY {$rankColumn} ASC",
            [$this->categoryId]
        );

        if (!empty($categoryFinishers)) {
            $ids = array_column($categoryFinishers, 'id');
            $this->bulkUpdatePosition($ids, 'category_position');
        }

        // ── General positions (lintas kategori) ──────────────────────────────
        // General position selalu pakai gun_elapsed_time kalau ada,
        // karena perbandingan lintas kategori harus apple-to-apple.
        // Kalau sebagian kategori punya gun_time dan sebagian tidak,
        // hanya finisher yang punya gun_elapsed_time yang diikutkan.
        $allFinishers = DB::select(
            'SELECT id FROM participants
             WHERE event_id = ? AND gun_elapsed_time IS NOT NULL
             ORDER BY gun_elapsed_time ASC',
            [$this->eventId]
        );

        // Fallback: kalau tidak ada yang punya gun_elapsed_time sama sekali
        // (semua kategori belum set gun_time), pakai chip time
        if (empty($allFinishers)) {
            $allFinishers = DB::select(
                'SELECT id FROM participants
                 WHERE event_id = ? AND elapsed_time IS NOT NULL
                 ORDER BY elapsed_time ASC',
                [$this->eventId]
            );
        }

        if (!empty($allFinishers)) {
            $ids = array_column($allFinishers, 'id');
            $this->bulkUpdatePosition($ids, 'general_position');
        }

        Log::info('Positions recalculated', [
            'category_id'        => $this->categoryId,
            'event_id'           => $this->eventId,
            'rank_column'        => $rankColumn,
            'category_finishers' => count($categoryFinishers),
            'total_finishers'    => count($allFinishers),
        ]);
    }

    private function bulkUpdatePosition(array $orderedIds, string $column): void
    {
        if (empty($orderedIds)) {
            return;
        }

        $chunks   = array_chunk($orderedIds, 500);
        $position = 1;

        foreach ($chunks as $chunk) {
            $cases    = '';
            $bindings = [];

            foreach ($chunk as $id) {
                $cases     .= 'WHEN ? THEN ? ';
                $bindings[] = $id;
                $bindings[] = $position++;
            }

            $inPlaceholders = implode(',', array_fill(0, count($chunk), '?'));
            $allBindings    = array_merge($bindings, $chunk);

            DB::update(
                "UPDATE participants SET {$column} = CASE id {$cases} END
                 WHERE id IN ({$inPlaceholders})",
                $allBindings
            );
        }
    }
}
