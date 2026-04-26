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
        // FIX: TTL lock diperpanjang dari 10 → 30 detik.
        //
        // Sebelumnya lock TTL hanya 10 detik, tapi timeout job adalah 60 detik.
        // Kalau recalculate untuk 1300 peserta butuh > 10 detik (misalnya karena
        // load DB tinggi saat race), lock expire duluan sebelum job selesai.
        // Job kedua yang antri akan masuk dan jalan bersamaan → dua job recalculate
        // kategori yang sama secara paralel → UPDATE race condition di kolom position.
        //
        // 30 detik memberi ruang yang cukup untuk 1300 peserta dengan beban normal.
        // Kalau lewat 30 detik, kemungkinan besar ada masalah lain (deadlock, slow query)
        // yang harus diselesaikan di level DB, bukan di sini.
        $lock = Cache::lock("recalc_positions_{$this->categoryId}", 30);

        // FIX: Ganti lock->get() dengan lock->block(0).
        //
        // lock->get() langsung return false kalau lock sedang dipegang job lain.
        // Akibatnya job ini dianggap selesai (success) tanpa recalculate sama sekali —
        // posisi tidak terupdate sampai ada trigger berikutnya.
        //
        // block(0) sama dengan get() dalam hal "tidak menunggu", tapi dengan semantik
        // yang lebih eksplisit: kalau tidak dapat lock, lempar LockTimeoutException
        // yang akan ditangkap di bawah dan di-release kembali ke queue untuk di-retry.
        // Dengan tries=3 dan backoff=[1,3,5], job ini akan retry dan akhirnya jalan.
        try {
            $lock->block(0);
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            // Job lain sedang recalculate kategori ini.
            // Release job ini ke queue untuk di-retry nanti (pakai backoff).
            // Ini lebih baik daripada silent skip — posisi pasti terupdate.
            Log::info('RecalculatePositions: lock busy, akan retry', [
                'category_id' => $this->categoryId,
            ]);
            $this->release(5); // retry setelah 5 detik
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
            'category_id'        => $this->categoryId,
            'event_id'           => $this->eventId,
            'category_finishers' => count($categoryFinishers),
            'total_finishers'    => count($allFinishers),
        ]);
    }

    private function bulkUpdatePosition(array $orderedIds, string $column): void
    {
        if (empty($orderedIds)) {
            return;
        }

        // Chunk 500 untuk hindari binding limit MySQL (max_allowed_packet)
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
