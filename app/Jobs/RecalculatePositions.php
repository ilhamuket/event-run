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

    public int   $tries   = 10;
    public int   $timeout = 90;
    public array $backoff = [3, 5, 10, 15, 20, 30];

    public function __construct(
        public readonly int $categoryId,
        public readonly int $eventId,
    ) {}

    public function handle(): void
    {
        $lock = Cache::lock("recalc_positions_{$this->categoryId}", 60);

        try {
            // Tunggu sampai 10 detik untuk dapat lock
            // jauh lebih efisien daripada langsung throw + re-queue
            $lock->block(10);
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            Log::info('RecalculatePositions: lock timeout, retry', [
                'category_id' => $this->categoryId,
                'attempt'     => $this->attempts(),
            ]);
            $this->release(10);
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
        $allFinishers = DB::select(
            'SELECT id FROM participants
             WHERE event_id = ? AND gun_elapsed_time IS NOT NULL
             ORDER BY gun_elapsed_time ASC',
            [$this->eventId]
        );

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
