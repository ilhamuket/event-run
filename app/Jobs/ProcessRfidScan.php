<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessRfidScan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int   $tries   = 3;
    public int   $timeout = 30;
    public array $backoff = [1, 3, 5];

    public function __construct(
        public readonly int     $eventId,
        public readonly string  $checkpointType,
        public readonly string  $rfidTag,
        public readonly ?string $readerId,
        public readonly ?int    $signalStrength,
        public readonly string  $scannedAt,
        public readonly int     $rawLogId,
    ) {}

    public function handle(): void
    {
        $scannedAt = Carbon::parse($this->scannedAt);

        DB::transaction(function () use ($scannedAt) {

            // ── 1. EVENT ────────────────────────────────────────────────────
            $event = DB::selectOne(
                'SELECT id FROM events WHERE id = ? AND is_published = 1 LIMIT 1',
                [$this->eventId]
            );
            if (!$event) {
                $this->markInvalid('event_not_active', 'Event is not active');
                return;
            }

            // ── 2. PARTICIPANT via RFID mapping ─────────────────────────────
            $participant = DB::selectOne(
                'SELECT p.id, p.bib, p.event_category_id, p.event_id
                 FROM participant_rfid_mappings m
                 JOIN participants p ON p.id = m.participant_id
                 WHERE m.rfid_tag = ? AND m.is_active = 1
                 LIMIT 1',
                [$this->rfidTag]
            );
            if (!$participant) {
                $this->markInvalid('unknown_rfid', 'RFID tag not registered');
                return;
            }

            DB::update(
                'UPDATE rfid_raw_logs SET bib = ? WHERE id = ?',
                [$participant->bib, $this->rawLogId]
            );

            // ── 3. RESOLVE CHECKPOINT ───────────────────────────────────────
            $checkpoint = DB::selectOne(
                'SELECT id, checkpoint_name, checkpoint_type, checkpoint_order, event_category_id
                 FROM rfid_checkpoints
                 WHERE event_category_id = ? AND checkpoint_type = ? AND is_active = 1
                 LIMIT 1',
                [$participant->event_category_id, $this->checkpointType]
            );
            if (!$checkpoint) {
                $this->markInvalid(
                    'no_checkpoint_for_category',
                    "No active {$this->checkpointType} checkpoint for category {$participant->event_category_id}"
                );
                return;
            }

            DB::update(
                'UPDATE rfid_raw_logs SET rfid_checkpoint_id = ? WHERE id = ?',
                [$checkpoint->id, $this->rawLogId]
            );

            // ── 4. GUARD: scan START sebelum gun time → buang ───────────────
            // Peserta sering scan sebelum race benar-benar mulai.
            // Kalau gun_time sudah di-set dan scan terjadi sebelumnya → abaikan.
            if ($this->checkpointType === 'start') {
                $category = DB::selectOne(
                    'SELECT gun_time FROM event_categories WHERE id = ? LIMIT 1',
                    [$participant->event_category_id]
                );

                if ($category && $category->gun_time) {
                    $gunTime = Carbon::parse($category->gun_time);
                    if ($scannedAt->lt($gunTime)) {
                        $this->markInvalid('before_gun_time', 'Scanned before race start (gun time)');
                        return;
                    }
                }
            }

            // ── 5. RAPID DUPLICATE ──────────────────────────────────────────
            $duplicateThreshold = $scannedAt->copy()->subSeconds(10)->format('Y-m-d H:i:s');
            $recentScan = DB::selectOne(
                'SELECT id FROM rfid_raw_logs
                 WHERE event_id = ?
                   AND rfid_checkpoint_id = ?
                   AND rfid_tag = ?
                   AND is_valid = 1
                   AND scanned_at >= ?
                   AND id != ?
                 LIMIT 1',
                [$this->eventId, $checkpoint->id, $this->rfidTag, $duplicateThreshold, $this->rawLogId]
            );
            if ($recentScan) {
                $this->markInvalid('rapid_duplicate', 'Too soon after previous scan');
                return;
            }

            // ── 6. ALREADY VALIDATED ────────────────────────────────────────
            //
            // START  → nimpa terus (last-write-wins), pakai scan paling akhir
            //          supaya peserta yang masih antri di garis tidak salah start time
            //
            // FINISH / CHECKPOINT → ambil yang pertama, scan berikutnya diabaikan
            $alreadyValidated = DB::selectOne(
                'SELECT id FROM rfid_validated_times
                 WHERE participant_id = ? AND rfid_checkpoint_id = ?
                 LIMIT 1
                 FOR UPDATE',
                [$participant->id, $checkpoint->id]
            );

            if ($alreadyValidated) {
                if ($this->checkpointType === 'start') {
                    // Nimpa start time dengan scan terbaru
                    DB::update(
                        'UPDATE rfid_validated_times
                         SET checkpoint_time = ?, rfid_raw_log_id = ?, updated_at = ?
                         WHERE id = ?',
                        [
                            $scannedAt->format('Y-m-d H:i:s'),
                            $this->rawLogId,
                            now()->format('Y-m-d H:i:s'),
                            $alreadyValidated->id,
                        ]
                    );

                    Log::info('RFID start time updated (last-write-wins)', [
                        'bib'       => $participant->bib,
                        'new_start' => $scannedAt->toIso8601String(),
                        'raw_log'   => $this->rawLogId,
                    ]);

                    return; // selesai, tidak perlu lanjut ke insert
                }

                // Finish & checkpoint: scan pertama yang menang
                $this->markInvalid('already_validated', 'Already scanned at this checkpoint');
                return;
            }

            // ── 7. HITUNG ELAPSED & SPLIT ───────────────────────────────────
            [$elapsedTime, $splitTime] = $this->calculateTimes(
                $participant,
                $checkpoint,
                $scannedAt
            );

            // ── 8. HITUNG POSISI ────────────────────────────────────────────
            $positionRow = DB::selectOne(
                'SELECT COUNT(*) as cnt FROM rfid_validated_times
                 WHERE rfid_checkpoint_id = ?',
                [$checkpoint->id]
            );
            $position = ($positionRow->cnt ?? 0) + 1;

            // ── 9. INSERT VALIDATED TIME ────────────────────────────────────
            $now = now()->format('Y-m-d H:i:s');
            try {
                DB::insert(
                    'INSERT INTO rfid_validated_times
                        (participant_id, rfid_checkpoint_id, rfid_raw_log_id,
                         checkpoint_time, elapsed_time, split_time,
                         position_at_checkpoint, validation_status, created_at, updated_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [
                        $participant->id,
                        $checkpoint->id,
                        $this->rawLogId,
                        $scannedAt->format('Y-m-d H:i:s'),
                        $elapsedTime,
                        $splitTime,
                        $position,
                        'auto',
                        $now,
                        $now,
                    ]
                );
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() === '23000') {
                    $this->markInvalid('duplicate_concurrent', 'Concurrent duplicate rejected by DB');
                    return;
                }
                throw $e;
            }

            // ── 10. FINISH ──────────────────────────────────────────────────
            if ($this->checkpointType === 'finish') {

                // Chip time  = finish RFID - start RFID peserta ini
                // Gun time   = finish RFID - gun_time kategori (acuan resmi)
                $gunElapsedTime = $this->calculateGunElapsedTime(
                    $participant->event_category_id,
                    $scannedAt
                );

                DB::update(
                    'UPDATE participants
                     SET elapsed_time = ?, gun_elapsed_time = ?
                     WHERE id = ?',
                    [$elapsedTime, $gunElapsedTime, $participant->id]
                );

                $dispatchKey = "recalc_dispatch_{$participant->event_category_id}";
                if (Cache::add($dispatchKey, 1, 8)) {
                    RecalculatePositions::dispatch(
                        $participant->event_category_id,
                        $participant->event_id
                    )
                        ->onQueue('positions')
                        ->delay(now()->addSeconds(5));
                }
            }

            Log::info('RFID processed', [
                'bib'      => $participant->bib,
                'cp'       => $checkpoint->checkpoint_name,
                'type'     => $this->checkpointType,
                'elapsed'  => $elapsedTime,
                'position' => $position,
            ]);
        });
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ProcessRfidScan failed permanently', [
            'raw_log_id'      => $this->rawLogId,
            'rfid_tag'        => $this->rfidTag,
            'checkpoint_type' => $this->checkpointType,
            'error'           => $e->getMessage(),
        ]);

        DB::update(
            "UPDATE rfid_raw_logs SET is_valid = 0, notes = ? WHERE id = ?",
            ['Job failed: ' . $e->getMessage(), $this->rawLogId]
        );
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function markInvalid(string $code, string $notes): void
    {
        DB::update(
            'UPDATE rfid_raw_logs SET is_valid = 0, notes = ? WHERE id = ?',
            [$notes, $this->rawLogId]
        );

        Log::info('RFID scan skipped', [
            'raw_log_id' => $this->rawLogId,
            'rfid_tag'   => $this->rfidTag,
            'reason'     => $code,
        ]);
    }

    /**
     * Hitung gun elapsed time = waktu finish RFID - gun_time kategori.
     *
     * Berbeda dengan chip elapsed time yang pakai start RFID per peserta,
     * gun time pakai satu acuan yang sama untuk semua peserta dalam kategori.
     * Kalau gun_time belum di-set di kategori, return null (tidak menghitung).
     */
    private function calculateGunElapsedTime(int $categoryId, Carbon $finishTime): ?string
    {
        $category = DB::selectOne(
            'SELECT gun_time FROM event_categories WHERE id = ? LIMIT 1',
            [$categoryId]
        );

        if (!$category || !$category->gun_time) {
            return null;
        }

        $gunTime = Carbon::parse($category->gun_time);
        $elapsed = $finishTime->timestamp - $gunTime->timestamp;

        if ($elapsed < 0) {
            Log::warning('Gun elapsed time negatif', [
                'category_id' => $categoryId,
                'gun_time'    => $category->gun_time,
                'finish_time' => $finishTime->toIso8601String(),
            ]);
            return null;
        }

        return gmdate('H:i:s', $elapsed);
    }

    private function calculateTimes(
        object $participant,
        object $checkpoint,
        Carbon $checkpointTime
    ): array {
        $elapsedTime = null;
        $splitTime   = null;

        // ── Elapsed: checkpointTime - start time peserta ini (chip time) ─────
        $startRecord = DB::selectOne(
            'SELECT vt.checkpoint_time
             FROM rfid_validated_times vt
             JOIN rfid_checkpoints cp ON cp.id = vt.rfid_checkpoint_id
             WHERE vt.participant_id = ?
               AND cp.event_category_id = ?
               AND cp.checkpoint_type = "start"
               AND cp.is_active = 1
             LIMIT 1',
            [$participant->id, $checkpoint->event_category_id]
        );

        if ($startRecord) {
            $startTs = Carbon::parse($startRecord->checkpoint_time)->timestamp;
            $elapsed = $checkpointTime->timestamp - $startTs;
            if ($elapsed >= 0) {
                $elapsedTime = gmdate('H:i:s', $elapsed);
            } else {
                Log::warning('Negative elapsed time', [
                    'participant_id'  => $participant->id,
                    'checkpoint_id'   => $checkpoint->id,
                    'start_time'      => $startRecord->checkpoint_time,
                    'checkpoint_time' => $checkpointTime->toIso8601String(),
                ]);
            }
        }

        // ── Split: checkpointTime - waktu checkpoint sebelumnya ──────────────
        $prevRecord = DB::selectOne(
            'SELECT vt.checkpoint_time
             FROM rfid_validated_times vt
             JOIN rfid_checkpoints cp ON cp.id = vt.rfid_checkpoint_id
             WHERE vt.participant_id = ?
               AND cp.event_category_id = ?
               AND cp.checkpoint_order < ?
               AND cp.is_active = 1
             ORDER BY cp.checkpoint_order DESC
             LIMIT 1',
            [$participant->id, $checkpoint->event_category_id, $checkpoint->checkpoint_order]
        );

        if ($prevRecord) {
            $prevTs = Carbon::parse($prevRecord->checkpoint_time)->timestamp;
            $split  = $checkpointTime->timestamp - $prevTs;
            if ($split >= 0) {
                $splitTime = gmdate('H:i:s', $split);
            }
        }

        return [$elapsedTime, $splitTime];
    }
}
