<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Participant;
use App\Models\ParticipantRfidMapping;
use App\Models\RfidCheckpoint;
use App\Models\RfidRawLog;
use App\Models\RfidValidatedTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RfidTimingService
{
    /**
     * Minimum detik antara scan yang sama di checkpoint yang sama.
     * Ini adalah debounce di sisi server — Go scanner juga punya debounce sendiri
     * (2–3 detik), jadi ini adalah layer kedua.
     */
    const DUPLICATE_THRESHOLD_SECONDS = 10;

    // ═══════════════════════════════════════════════════════════════════════════
    // PUBLIC: PROCESS SCAN
    // Dipanggil oleh Go scanner via POST /api/rfid/scan.
    // Dirancang untuk tahan burst — 1300 peserta, finish line serentak.
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Proses RFID scan masuk dari reader.
     *
     * Flow:
     * 1. Tulis raw log dulu — SELALU, apapun hasilnya.
     * 2. Validasi satu per satu, update raw log kalau invalid, return early.
     * 3. Kalau semua lolos, buat validated time dalam transaksi.
     * 4. DB unique constraint sebagai last-line-of-defense dari race condition.
     *
     * @param  Carbon|null  $scannedAt  Waktu dari hardware scanner. Kalau null, pakai Carbon::now().
     */
    /**
     * Proses RFID scan masuk dari reader.
     *
     * Flow baru (multi-kategori):
     * 1. Tulis raw log dulu (tanpa checkpoint ID dulu).
     * 2. Cari peserta dari RFID tag.
     * 3. Dari kategori peserta, cari checkpoint yang sesuai dengan checkpoint_type.
     * 4. Lanjutkan validasi seperti biasa.
     *
     * @param  string  $checkpointType  'start' | 'finish' | 'checkpoint'
     */
    public function processScan(
        int $eventId,
        string $checkpointType,  // ← berubah dari checkpointId
        string $rfidTag,
        ?string $readerId = null,
        ?int $signalStrength = null,
        ?Carbon $scannedAt = null
    ): array {
        $scannedAt = $this->resolveScannedAt($scannedAt);
        $rfidTag   = strtoupper(trim($rfidTag));

        return DB::transaction(function () use (
            $eventId,
            $checkpointType,
            $rfidTag,
            $readerId,
            $signalStrength,
            $scannedAt
        ) {
            // ── 1. EVENT AKTIF ──────────────────────────────────────────────────
            $event = Event::find($eventId);
            if (!$event || !$event->is_published) {
                // Raw log tanpa checkpoint ID — tulis dulu baru return
                $rawLog = $this->createRawLogWithoutCheckpoint(
                    $eventId, $rfidTag, $scannedAt, $readerId, $signalStrength
                );
                return $this->invalidResult($rawLog, 'event_not_active', 'Event is not active');
            }

            // ── 2. PARTICIPANT ──────────────────────────────────────────────────
            // Cari peserta SEBELUM cari checkpoint, karena checkpoint bergantung
            // pada kategori peserta.
            $participant = ParticipantRfidMapping::findParticipantByRfid($rfidTag);

            if (!$participant) {
                $rawLog = $this->createRawLogWithoutCheckpoint(
                    $eventId, $rfidTag, $scannedAt, $readerId, $signalStrength
                );
                return $this->invalidResult($rawLog, 'unknown_rfid', 'RFID tag not registered');
            }

            // ── 3. RESOLVE CHECKPOINT BERDASARKAN KATEGORI ──────────────────────
            // Ini inti dari perubahan: satu scanner bisa handle semua kategori
            // karena checkpoint di-resolve dari sisi server, bukan dari config scanner.
            $checkpoint = RfidCheckpoint::where('event_category_id', $participant->event_category_id)
                ->where('checkpoint_type', $checkpointType)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$checkpoint) {
                $rawLog = $this->createRawLogWithoutCheckpoint(
                    $eventId, $rfidTag, $scannedAt, $readerId, $signalStrength,
                    $participant->bib
                );
                return $this->invalidResult(
                    $rawLog,
                    'no_checkpoint_for_category',
                    sprintf(
                        'No active %s checkpoint for category %d',
                        $checkpointType,
                        $participant->event_category_id
                    )
                );
            }

            // ── 4. TULIS RAW LOG (sekarang sudah punya checkpoint ID) ───────────
            $rawLog = $this->createRawLog(
                $eventId,
                $checkpoint->id,
                $rfidTag,
                $scannedAt,
                $readerId,
                $signalStrength
            );
            $rawLog->update(['bib' => $participant->bib]);

            // ── 5. CUTOFF TIME ──────────────────────────────────────────────────
            // if ($checkpoint->cutoff_time && $scannedAt->gt($checkpoint->cutoff_time)) {
            //     return $this->invalidResult($rawLog, 'past_cutoff', 'Past checkpoint cutoff time');
            // }

            // ── 6. RAPID DUPLICATE ──────────────────────────────────────────────
            $recentScan = RfidRawLog::where('event_id', $eventId)
                ->where('rfid_checkpoint_id', $checkpoint->id)
                ->where('rfid_tag', $rfidTag)
                ->where('is_valid', true)
                ->where('scanned_at', '>=', $scannedAt->copy()->subSeconds(self::DUPLICATE_THRESHOLD_SECONDS))
                ->where('id', '!=', $rawLog->id)
                ->exists();

            if ($recentScan) {
                return $this->invalidResult($rawLog, 'rapid_duplicate', 'Too soon after previous scan');
            }

            // ── 7. ALREADY VALIDATED ────────────────────────────────────────────
            $alreadyValidated = RfidValidatedTime::where('participant_id', $participant->id)
                ->where('rfid_checkpoint_id', $checkpoint->id)
                ->lockForUpdate()
                ->exists();

            if ($alreadyValidated) {
                return $this->invalidResult($rawLog, 'already_validated', 'Already scanned at this checkpoint');
            }

            // ── 8. SIMPAN VALIDATED TIME ─────────────────────────────────────────
            try {
                $validated = $this->createValidatedTime(
                    $participant,
                    $checkpoint,
                    $rawLog,
                    $scannedAt
                );
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() === '23000') {
                    Log::info('RFID race condition caught by DB constraint', [
                        'participant_id'  => $participant->id,
                        'checkpoint_id'   => $checkpoint->id,
                        'checkpoint_type' => $checkpointType,
                        'rfid_tag'        => $rfidTag,
                    ]);
                    return $this->invalidResult($rawLog, 'duplicate_concurrent', 'Concurrent duplicate rejected by DB');
                }
                throw $e;
            }

            // ── 9. FINISH LINE PROCESSING ────────────────────────────────────────
            if ($checkpoint->isFinish()) {
                $this->processFinish($participant, $validated);
            }

            // ── RESPONSE ─────────────────────────────────────────────────────────
            return [
                'success'   => true,
                'is_finish' => $checkpoint->isFinish(),
                'message'   => $checkpoint->isFinish() ? 'FINISH!' : 'Checkpoint recorded',

                'participant' => [
                    'id'   => $participant->id,
                    'bib'  => $participant->bib,
                    'name' => $participant->name,
                ],

                'checkpoint' => [
                    'id'       => $checkpoint->id,
                    'name'     => $checkpoint->checkpoint_name,
                    'type'     => $checkpoint->checkpoint_type,
                    'category' => $participant->event_category_id,
                ],

                'timing' => [
                    'scanned_at' => $scannedAt->format('Y-m-d H:i:s'),
                    'elapsed'    => $validated->formatted_elapsed_time,
                    'split'      => $validated->formatted_split_time,
                    'position'   => $validated->position_at_checkpoint,
                ],

                'raw_log_id'        => $rawLog->id,
                'validated_time_id' => $validated->id,
            ];
        });
    }

     /**
     * Tulis raw log tanpa checkpoint ID.
     * Dipakai untuk kasus error sebelum checkpoint berhasil di-resolve
     * (unknown RFID, event tidak aktif, dll).
     */
    protected function createRawLogWithoutCheckpoint(
        int $eventId,
        string $rfidTag,
        Carbon $scannedAt,
        ?string $readerId,
        ?int $signalStrength,
        ?string $bib = null
    ): RfidRawLog {
        return RfidRawLog::create([
            'event_id'           => $eventId,
            'rfid_checkpoint_id' => null, // ← nullable, pastikan kolom ini nullable di migration
            'rfid_tag'           => $rfidTag,
            'bib'                => $bib,
            'scanned_at'         => $scannedAt,
            'reader_id'          => $readerId,
            'signal_strength'    => $signalStrength,
            'is_valid'           => false,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PUBLIC: MANUAL ENTRY (admin)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Entry manual untuk peserta yang missed scan RFID.
     */
    public function manualEntry(
        int $participantId,
        int $checkpointId,
        string $checkpointTime,
        int $adminUserId,
        ?string $notes = null
    ): RfidValidatedTime {
        $participant = Participant::findOrFail($participantId);
        $checkpoint  = RfidCheckpoint::findOrFail($checkpointId);

        $existing = RfidValidatedTime::where('participant_id', $participantId)
            ->where('rfid_checkpoint_id', $checkpointId)
            ->first();

        if ($existing) {
            throw new \Exception('Participant already has a time for this checkpoint. Use correctTime() instead.');
        }

        $checkpointTimeCarbon = Carbon::parse($checkpointTime);

        [$elapsedTime, $splitTime] = $this->calculateTimes($participant, $checkpoint, $checkpointTimeCarbon);

        // Hitung posisi berdasarkan urutan waktu yang masuk sebelumnya.
        $position = RfidValidatedTime::where('rfid_checkpoint_id', $checkpointId)
            ->where('checkpoint_time', '<', $checkpointTimeCarbon)
            ->count() + 1;

        $validatedTime = RfidValidatedTime::create([
            'participant_id'         => $participantId,
            'rfid_checkpoint_id'     => $checkpointId,
            'rfid_raw_log_id'        => null,
            'checkpoint_time'        => $checkpointTimeCarbon,
            'elapsed_time'           => $elapsedTime,
            'split_time'             => $splitTime,
            'position_at_checkpoint' => $position,
            'validation_status'      => 'manual',
            'validation_notes'       => $notes ?? 'Manual entry by admin',
            'validated_by'           => $adminUserId,
        ]);

        if ($checkpoint->isFinish()) {
            $participant->update(['elapsed_time' => $elapsedTime]);
            $this->recalculatePositions($participant->event_category_id);
        }

        return $validatedTime;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PUBLIC: CORRECT TIME (admin)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Koreksi waktu oleh admin.
     */
    public function correctTime(
        int $validatedTimeId,
        string $newTime,
        int $adminUserId,
        ?string $notes = null
    ): RfidValidatedTime {
        $validatedTime = RfidValidatedTime::with(['participant.category', 'checkpoint'])
            ->findOrFail($validatedTimeId);

        $validatedTime->update([
            'checkpoint_time'   => Carbon::parse($newTime),
            'validation_status' => 'corrected',
            'validation_notes'  => $notes,
            'validated_by'      => $adminUserId,
        ]);

        // Hitung ulang elapsed dari start ke waktu baru.
        $this->recalculateElapsedTime($validatedTime->fresh());

        $validatedTime->refresh();

        if ($validatedTime->checkpoint->isFinish()) {
            $participant = $validatedTime->participant;
            $participant->update(['elapsed_time' => $validatedTime->elapsed_time]);
            $this->recalculatePositions($participant->event_category_id);
        }

        return $validatedTime->fresh();
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PUBLIC: QUERIES
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Live results untuk kategori tertentu.
     * Bisa difilter per gender.
     */
    public function getLiveResults(int $categoryId, ?string $gender = null): array
    {
        $query = Participant::where('event_category_id', $categoryId)
            ->with(['validatedTimes.checkpoint', 'category'])
            ->whereNotNull('elapsed_time')
            ->orderBy('elapsed_time');

        if ($gender) {
            $query->where('gender', $gender);
        }

        $participants = $query->get();

        $position = 1;
        return $participants->map(function ($p) use (&$position, $gender) {
            return [
                'position'     => $gender ? $position++ : $p->category_position,
                'bib'          => $p->bib,
                'name'         => $p->display_name,
                'gender'       => $p->gender,
                'age'          => $p->age,
                'community'    => $p->community,
                'elapsed_time' => $p->formatted_elapsed_time,
                'checkpoints'  => $p->validatedTimes
                    ->sortBy('checkpoint.checkpoint_order')
                    ->values()
                    ->map(fn($vt) => [
                        'checkpoint' => $vt->checkpoint->checkpoint_name,
                        'time'       => $vt->checkpoint_time->format('H:i:s'),
                        'elapsed'    => $vt->formatted_elapsed_time,
                        'split'      => $vt->formatted_split_time,
                    ]),
            ];
        })->toArray();
    }

    /**
     * Status checkpoint untuk monitoring dashboard.
     */
    public function getCheckpointStatus(int $checkpointId): array
    {
        $checkpoint = RfidCheckpoint::with('eventCategory')->findOrFail($checkpointId);

        $totalParticipants = Participant::where('event_category_id', $checkpoint->event_category_id)->count();
        $passedCount       = RfidValidatedTime::where('rfid_checkpoint_id', $checkpointId)->count();

        $recentScans = RfidRawLog::where('rfid_checkpoint_id', $checkpointId)
            ->orderBy('scanned_at', 'desc')
            ->limit(20)
            ->get();

        return [
            'checkpoint' => [
                'id'          => $checkpoint->id,
                'name'        => $checkpoint->checkpoint_name,
                'type'        => $checkpoint->checkpoint_type,
                'distance_km' => $checkpoint->distance_km,
            ],
            'statistics' => [
                'total_participants' => $totalParticipants,
                'passed'             => $passedCount,
                'remaining'          => $totalParticipants - $passedCount,
                'percentage'         => $totalParticipants > 0
                    ? round(($passedCount / $totalParticipants) * 100, 1)
                    : 0,
            ],
            'recent_scans' => $recentScans->map(fn($scan) => [
                'rfid_tag'   => $scan->rfid_tag,
                'bib'        => $scan->bib,
                'scanned_at' => $scan->scanned_at->format('H:i:s'),
                'is_valid'   => $scan->is_valid,
                'notes'      => $scan->notes,
            ]),
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PROTECTED: CORE HELPERS
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Tulis raw log. Selalu berhasil — tidak ada kondisi yang skip ini.
     */
    protected function createRawLog(
        int $eventId,
        int $checkpointId,
        string $rfidTag,
        Carbon $scannedAt,
        ?string $readerId,
        ?int $signalStrength
    ): RfidRawLog {
        return RfidRawLog::create([
            'event_id'           => $eventId,
            'rfid_checkpoint_id' => $checkpointId,
            'rfid_tag'           => $rfidTag,
            'scanned_at'         => $scannedAt,
            'reader_id'          => $readerId,
            'signal_strength'    => $signalStrength,
            'is_valid'           => true, // default valid, di-update kalau ternyata tidak
        ]);
    }

    /**
     * Buat validated time dengan elapsed dan split yang sudah dihitung.
     * Race condition dari concurrent request akan ditangkap oleh DB unique constraint.
     */
    protected function createValidatedTime(
        Participant $participant,
        RfidCheckpoint $checkpoint,
        RfidRawLog $rawLog,
        Carbon $checkpointTime
    ): RfidValidatedTime {
        [$elapsedTime, $splitTime] = $this->calculateTimes($participant, $checkpoint, $checkpointTime);

        // Hitung posisi dengan lock — important: lockForUpdate() di sini efektif
        // karena kita lock existing rows, bukan row yang belum ada.
        // Kalau ada 10 orang finish hampir bersamaan, mereka antri di sini.
        $position = RfidValidatedTime::where('rfid_checkpoint_id', $checkpoint->id)
            ->lockForUpdate()
            ->count() + 1;

        return RfidValidatedTime::create([
            'participant_id'         => $participant->id,
            'rfid_checkpoint_id'     => $checkpoint->id,
            'rfid_raw_log_id'        => $rawLog->id,
            'checkpoint_time'        => $checkpointTime,
            'elapsed_time'           => $elapsedTime,
            'split_time'             => $splitTime,
            'position_at_checkpoint' => $position,
            'validation_status'      => 'auto',
        ]);
    }

    /**
     * Hitung elapsed time (dari start) dan split time (dari checkpoint sebelumnya).
     *
     * FIX dari versi sebelumnya:
     * - diffInSeconds() dibalik: harusnya checkpointTime - startTime, bukan sebaliknya.
     *   Carbon::diffInSeconds() return nilai absolut kalau tidak pakai parameter false.
     *   Kita pakai timestamp arithmetic langsung untuk eksplisit dan aman.
     *
     * @return array{0: string|null, 1: string|null} [$elapsedTime, $splitTime]
     */
    protected function calculateTimes(
        Participant $participant,
        RfidCheckpoint $checkpoint,
        Carbon $checkpointTime
    ): array {
        $elapsedTime = null;
        $splitTime   = null;

        $category = $participant->category;

        if (!$category) {
            Log::error('Participant has no category', ['participant_id' => $participant->id]);
            throw new \RuntimeException('Invalid participant: missing category');
        }

        // ── Elapsed time ─────────────────────────────────────────────────────
        // Elapsed = checkpointTime - startTime
        $startCheckpoint = $category->startCheckpoint();

        if ($startCheckpoint) {
            $startRecord = RfidValidatedTime::where('participant_id', $participant->id)
                ->where('rfid_checkpoint_id', $startCheckpoint->id)
                ->first();

            if ($startRecord) {
                $elapsedSeconds = $checkpointTime->timestamp - $startRecord->checkpoint_time->timestamp;

                if ($elapsedSeconds >= 0) {
                    $elapsedTime = gmdate('H:i:s', $elapsedSeconds);
                } else {
                    // Waktu checkpoint lebih awal dari start — data tidak konsisten.
                    Log::warning('Negative elapsed time detected', [
                        'participant_id' => $participant->id,
                        'checkpoint_id'  => $checkpoint->id,
                        'start_time'     => $startRecord->checkpoint_time->toIso8601String(),
                        'checkpoint_time' => $checkpointTime->toIso8601String(),
                    ]);
                }
            }
        }

        // ── Split time ───────────────────────────────────────────────────────
        // Split = checkpointTime - previous checkpointTime
        $previousCheckpoint = RfidCheckpoint::where('event_category_id', $checkpoint->event_category_id)
            ->where('checkpoint_order', '<', $checkpoint->checkpoint_order)
            ->where('is_active', true)
            ->orderBy('checkpoint_order', 'desc')
            ->first();

        if ($previousCheckpoint) {
            $previousRecord = RfidValidatedTime::where('participant_id', $participant->id)
                ->where('rfid_checkpoint_id', $previousCheckpoint->id)
                ->first();

            if ($previousRecord) {
                $splitSeconds = $checkpointTime->timestamp - $previousRecord->checkpoint_time->timestamp;

                if ($splitSeconds >= 0) {
                    $splitTime = gmdate('H:i:s', $splitSeconds);
                }
            }
        }

        return [$elapsedTime, $splitTime];
    }

    /**
     * Proses finish: update elapsed_time peserta, lalu recalculate posisi.
     */
    protected function processFinish(Participant $participant, RfidValidatedTime $validatedTime): void
    {
        $participant->update([
            'elapsed_time' => $validatedTime->elapsed_time,
        ]);

        $this->recalculatePositions($participant->event_category_id);
    }

    /**
     * Hitung ulang elapsed time untuk satu validated time entry.
     * Dipanggil setelah admin koreksi waktu.
     */
    protected function recalculateElapsedTime(RfidValidatedTime $validatedTime): void
    {
        $participant = $validatedTime->participant;
        $category    = $participant->category;

        if (!$category) {
            Log::error('Missing category on recalculate', ['participant_id' => $participant->id]);
            return;
        }

        $startCheckpoint = $category->startCheckpoint();
        if (!$startCheckpoint) return;

        $startRecord = RfidValidatedTime::where('participant_id', $participant->id)
            ->where('rfid_checkpoint_id', $startCheckpoint->id)
            ->first();

        if (!$startRecord) return;

        $elapsedSeconds = $validatedTime->checkpoint_time->timestamp - $startRecord->checkpoint_time->timestamp;

        if ($elapsedSeconds < 0) {
            Log::warning('Negative elapsed on recalculate', ['validated_time_id' => $validatedTime->id]);
            return;
        }

        $validatedTime->update([
            'elapsed_time' => gmdate('H:i:s', $elapsedSeconds),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PUBLIC: RECALCULATE POSITIONS
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Hitung ulang posisi kategori dan posisi umum untuk semua finisher.
     *
     * FIX dari versi sebelumnya:
     * - Sebelumnya: foreach + $p->update() = N+1 queries (1300 UPDATE terpisah).
     * - Sekarang: Satu CASE WHEN UPDATE per operasi = 2 query total.
     *
     * Di finish line race, method ini dipanggil setiap ada finisher baru.
     * Dengan 1300 peserta finish dalam waktu berdekatan, N+1 = bencana.
     * Bulk update menyelesaikan ini.
     */
    public function recalculatePositions(int $categoryId): void
    {
        $category = EventCategory::find($categoryId);
        if (!$category) return;

        $finishCheckpoint = $category->finishCheckpoint();
        if (!$finishCheckpoint) return;

        // ── Category positions ───────────────────────────────────────────────
        $finishedInCategory = Participant::where('event_category_id', $categoryId)
            ->whereNotNull('elapsed_time')
            ->orderBy('elapsed_time')
            ->pluck('id'); // hanya ambil ID, tidak butuh full model

        $this->bulkUpdatePosition($finishedInCategory->toArray(), 'category_position');

        // ── General positions (lintas semua kategori dalam event) ────────────
        $allFinished = Participant::where('event_id', $category->event_id)
            ->whereNotNull('elapsed_time')
            ->orderBy('elapsed_time')
            ->pluck('id');

        $this->bulkUpdatePosition($allFinished->toArray(), 'general_position');
    }

    /**
     * Bulk UPDATE posisi menggunakan CASE WHEN — satu query untuk semua peserta.
     *
     * @param  int[]   $orderedIds  ID peserta sudah diurutkan berdasarkan elapsed time.
     * @param  string  $column      'category_position' atau 'general_position'.
     */
    protected function bulkUpdatePosition(array $orderedIds, string $column): void
    {
        if (empty($orderedIds)) return;

        // Bangun: CASE id WHEN 10 THEN 1 WHEN 25 THEN 2 ... END
        $cases       = '';
        $bindings    = [];
        $position    = 1;

        foreach ($orderedIds as $id) {
            $cases    .= "WHEN ? THEN ? ";
            $bindings[] = $id;
            $bindings[] = $position;
            $position++;
        }

        // Tambahkan IDs untuk klausa WHERE IN
        $inPlaceholders = implode(',', array_fill(0, count($orderedIds), '?'));
        $allBindings    = array_merge($bindings, $orderedIds);

        DB::update(
            "UPDATE participants SET {$column} = CASE id {$cases} END WHERE id IN ({$inPlaceholders})",
            $allBindings
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PRIVATE: UTILITIES
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Kembalikan hasil scan yang tidak valid.
     * Update raw log, return array response yang konsisten.
     */
    private function invalidResult(RfidRawLog $rawLog, string $errorCode, string $notes): array
    {
        $rawLog->update([
            'is_valid' => false,
            'notes'    => $notes,
        ]);

        return [
            'success'    => false,
            'error'      => $errorCode,
            'message'    => $notes,
            'raw_log_id' => $rawLog->id,
        ];
    }

    /**
     * Tentukan timestamp yang digunakan untuk scan.
     *
     * Prioritas: hardware scanner timestamp > server time.
     * Guard: kalau hardware timestamp lebih dari 5 menit beda dengan server,
     * pakai server time (kemungkinan clock drift atau kiriman yang salah).
     */
    private function resolveScannedAt(?Carbon $scannerTime): Carbon
    {
        $now = Carbon::now();

        if (!$scannerTime) {
            return $now;
        }

        $diffSeconds = abs($now->timestamp - $scannerTime->timestamp);

        if ($diffSeconds > 300) { // > 5 menit
            Log::warning('Scanner timestamp too far from server time, using server time', [
                'scanner_time'  => $scannerTime->toIso8601String(),
                'server_time'   => $now->toIso8601String(),
                'diff_seconds'  => $diffSeconds,
            ]);
            return $now;
        }

        return $scannerTime;
    }
}
