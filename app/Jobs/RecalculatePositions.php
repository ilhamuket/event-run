<?php
// app/Jobs/RecalculatePositions.php

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
        $lock = Cache::lock("recalc_positions_{$this->categoryId}", 10);
        if (!$lock->get()) {
            // Job lain sedang recalculate kategori ini, skip
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
        // ── Category positions ───────────────────────────────────────────────
        // Ambil ID saja — tidak perlu hydrate model
        $categoryFinishers = DB::select(
            'SELECT id FROM participants
             WHERE event_category_id = ? AND elapsed_time IS NOT NULL
             ORDER BY elapsed_time ASC',
            [$this->categoryId]
        );

        if (!empty($categoryFinishers)) {
            $ids = array_column($categoryFinishers, 'id');
            $this->bulkUpdatePosition($ids, 'category_position');
        }

        // ── General positions (lintas kategori) ──────────────────────────────
        $allFinishers = DB::select(
            'SELECT id FROM participants
             WHERE event_id = ? AND elapsed_time IS NOT NULL
             ORDER BY elapsed_time ASC',
            [$this->eventId]
        );

        if (!empty($allFinishers)) {
            $ids = array_column($allFinishers, 'id');
            $this->bulkUpdatePosition($ids, 'general_position');
        }

        Log::info('Positions recalculated', [
            'category_id'       => $this->categoryId,
            'event_id'          => $this->eventId,
            'category_finishers'=> count($categoryFinishers ?? []),
            'total_finishers'   => count($allFinishers ?? []),
        ]);
    }

    private function bulkUpdatePosition(array $orderedIds, string $column): void
    {
        if (empty($orderedIds)) return;

        // Chunk 500 untuk hindari binding limit MySQL
        $chunks   = array_chunk($orderedIds, 500);
        $position = 1;

        foreach ($chunks as $chunk) {
            $cases    = '';
            $bindings = [];

            foreach ($chunk as $id) {
                $cases      .= 'WHEN ? THEN ? ';
                $bindings[]  = $id;
                $bindings[]  = $position++;
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
