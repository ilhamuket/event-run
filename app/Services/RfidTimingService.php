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
     * Minimum seconds between scans at same checkpoint to be considered valid
     * This prevents double-scanning when participant is near the reader
     */
    const DUPLICATE_THRESHOLD_SECONDS = 10;

    /**
     * Process incoming RFID scan from reader
     *
     * @param int $eventId
     * @param int $checkpointId
     * @param string $rfidTag
     * @param string|null $readerId
     * @param int|null $signalStrength
     * @return array
     */
    public function processScan(
        int $eventId,
        int $checkpointId,
        string $rfidTag,
        ?string $readerId = null,
        ?int $signalStrength = null
    ): array {
        $scannedAt = Carbon::now();

        // 1. Always log the raw scan first
        $rawLog = $this->createRawLog(
            $eventId,
            $checkpointId,
            $rfidTag,
            $scannedAt,
            $readerId,
            $signalStrength
        );

        // 2. Find participant by RFID tag
        $participant = ParticipantRfidMapping::findParticipantByRfid($rfidTag);

        if (!$participant) {
            $rawLog->update([
                'is_valid' => false,
                'notes' => 'RFID tag not mapped to any participant'
            ]);

            return [
                'success' => false,
                'error' => 'unknown_rfid',
                'message' => 'RFID tag not registered',
                'rfid_tag' => $rfidTag,
                'raw_log_id' => $rawLog->id
            ];
        }

        // Update raw log with BIB
        $rawLog->update(['bib' => $participant->bib]);

        // 3. Get checkpoint info
        $checkpoint = RfidCheckpoint::find($checkpointId);
        if (!$checkpoint) {
            $rawLog->update([
                'is_valid' => false,
                'notes' => 'Invalid checkpoint ID'
            ]);

            return [
                'success' => false,
                'error' => 'invalid_checkpoint',
                'message' => 'Checkpoint not found',
                'raw_log_id' => $rawLog->id
            ];
        }

        // 4. Check if participant belongs to the correct category
        if ($participant->event_category_id !== $checkpoint->event_category_id) {
            $rawLog->update([
                'is_valid' => false,
                'notes' => 'Participant category mismatch with checkpoint'
            ]);

            return [
                'success' => false,
                'error' => 'category_mismatch',
                'message' => 'Participant not registered for this category',
                'participant' => $participant->name,
                'bib' => $participant->bib,
                'raw_log_id' => $rawLog->id
            ];
        }

        // 5. Check for duplicate scan at same checkpoint
        $existingValidated = RfidValidatedTime::where('participant_id', $participant->id)
            ->where('rfid_checkpoint_id', $checkpointId)
            ->first();

        if ($existingValidated) {
            $rawLog->update([
                'is_valid' => false,
                'notes' => 'Duplicate scan - participant already recorded at this checkpoint'
            ]);

            return [
                'success' => false,
                'error' => 'duplicate_scan',
                'message' => 'Already recorded at this checkpoint',
                'participant' => $participant->name,
                'bib' => $participant->bib,
                'existing_time' => $existingValidated->checkpoint_time->format('H:i:s'),
                'raw_log_id' => $rawLog->id
            ];
        }

        // 6. Check for rapid duplicate (within threshold seconds)
        $recentScan = RfidRawLog::where('event_id', $eventId)
            ->where('rfid_checkpoint_id', $checkpointId)
            ->where('rfid_tag', $rfidTag)
            ->where('id', '!=', $rawLog->id)
            ->where('scanned_at', '>=', $scannedAt->copy()->subSeconds(self::DUPLICATE_THRESHOLD_SECONDS))
            ->first();

        if ($recentScan) {
            $rawLog->update([
                'is_valid' => false,
                'notes' => 'Rapid duplicate scan within ' . self::DUPLICATE_THRESHOLD_SECONDS . ' seconds'
            ]);

            return [
                'success' => false,
                'error' => 'rapid_duplicate',
                'message' => 'Duplicate scan detected',
                'participant' => $participant->name,
                'bib' => $participant->bib,
                'raw_log_id' => $rawLog->id
            ];
        }

        // 7. Create validated time record
        $validatedTime = $this->createValidatedTime(
            $participant,
            $checkpoint,
            $rawLog,
            $scannedAt
        );

        // 8. If this is finish checkpoint, update participant's elapsed time and positions
        if ($checkpoint->isFinish()) {
            $this->processFinish($participant, $validatedTime);
        }

        return [
            'success' => true,
            'message' => $checkpoint->isFinish() ? 'FINISH!' : 'Checkpoint recorded',
            'participant' => [
                'id' => $participant->id,
                'name' => $participant->name,
                'bib' => $participant->bib,
                'category' => $participant->category->name ?? null,
            ],
            'checkpoint' => [
                'name' => $checkpoint->checkpoint_name,
                'type' => $checkpoint->checkpoint_type,
                'distance_km' => $checkpoint->distance_km,
            ],
            'timing' => [
                'checkpoint_time' => $scannedAt->format('Y-m-d H:i:s'),
                'elapsed_time' => $validatedTime->formatted_elapsed_time,
                'split_time' => $validatedTime->formatted_split_time,
                'position' => $validatedTime->position_at_checkpoint,
            ],
            'raw_log_id' => $rawLog->id,
            'validated_time_id' => $validatedTime->id,
        ];
    }

    /**
     * Create raw log entry
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
            'event_id' => $eventId,
            'rfid_checkpoint_id' => $checkpointId,
            'rfid_tag' => $rfidTag,
            'scanned_at' => $scannedAt,
            'reader_id' => $readerId,
            'signal_strength' => $signalStrength,
            'is_valid' => true,
        ]);
    }

    /**
     * Create validated time entry with calculated elapsed and split times
     */
    protected function createValidatedTime(
        Participant $participant,
        RfidCheckpoint $checkpoint,
        RfidRawLog $rawLog,
        Carbon $checkpointTime
    ): RfidValidatedTime {
        $elapsedTime = null;
        $splitTime = null;

        // Get start time for elapsed time calculation
        $startCheckpoint = $participant->category->startCheckpoint();
        if ($startCheckpoint) {
            $startTime = RfidValidatedTime::where('participant_id', $participant->id)
                ->where('rfid_checkpoint_id', $startCheckpoint->id)
                ->first();

            if ($startTime) {
                $elapsedSeconds = $checkpointTime->diffInSeconds($startTime->checkpoint_time);
                $elapsedTime = gmdate('H:i:s', $elapsedSeconds);
            }
        }

        // Get previous checkpoint time for split time calculation
        $previousCheckpoint = RfidCheckpoint::where('event_category_id', $checkpoint->event_category_id)
            ->where('checkpoint_order', '<', $checkpoint->checkpoint_order)
            ->where('is_active', true)
            ->orderBy('checkpoint_order', 'desc')
            ->first();

        if ($previousCheckpoint) {
            $previousTime = RfidValidatedTime::where('participant_id', $participant->id)
                ->where('rfid_checkpoint_id', $previousCheckpoint->id)
                ->first();

            if ($previousTime) {
                $splitSeconds = $checkpointTime->diffInSeconds($previousTime->checkpoint_time);
                $splitTime = gmdate('H:i:s', $splitSeconds);
            }
        }

        // Calculate position at this checkpoint
        $position = RfidValidatedTime::where('rfid_checkpoint_id', $checkpoint->id)->count() + 1;

        return RfidValidatedTime::create([
            'participant_id' => $participant->id,
            'rfid_checkpoint_id' => $checkpoint->id,
            'rfid_raw_log_id' => $rawLog->id,
            'checkpoint_time' => $checkpointTime,
            'elapsed_time' => $elapsedTime,
            'split_time' => $splitTime,
            'position_at_checkpoint' => $position,
            'validation_status' => 'auto',
        ]);
    }

    /**
     * Process finish - update participant record and calculate positions
     */
    protected function processFinish(Participant $participant, RfidValidatedTime $validatedTime): void
    {
        // Update participant's elapsed time
        $participant->update([
            'elapsed_time' => $validatedTime->elapsed_time,
        ]);

        // Recalculate positions for all finished participants in this category
        $this->recalculatePositions($participant->event_category_id);
    }

    /**
     * Recalculate general and category positions for all finished participants
     */
    public function recalculatePositions(int $categoryId): void
    {
        $category = EventCategory::find($categoryId);
        if (!$category) return;

        // Get finish checkpoint
        $finishCheckpoint = $category->finishCheckpoint();
        if (!$finishCheckpoint) return;

        // Get all participants who finished, ordered by elapsed time
        $finishedParticipants = Participant::where('event_category_id', $categoryId)
            ->whereNotNull('elapsed_time')
            ->orderBy('elapsed_time')
            ->get();

        // Update category positions
        $categoryPosition = 1;
        foreach ($finishedParticipants as $p) {
            $p->update(['category_position' => $categoryPosition]);
            $categoryPosition++;
        }

        // Recalculate general positions (across all categories in the event)
        $allFinished = Participant::where('event_id', $category->event_id)
            ->whereNotNull('elapsed_time')
            ->orderBy('elapsed_time')
            ->get();

        $generalPosition = 1;
        foreach ($allFinished as $p) {
            $p->update(['general_position' => $generalPosition]);
            $generalPosition++;
        }
    }

    /**
     * Manual time correction by admin
     */
    public function correctTime(
        int $validatedTimeId,
        string $newTime,
        int $adminUserId,
        ?string $notes = null
    ): RfidValidatedTime {
        $validatedTime = RfidValidatedTime::findOrFail($validatedTimeId);

        $validatedTime->update([
            'checkpoint_time' => Carbon::parse($newTime),
            'validation_status' => 'corrected',
            'validation_notes' => $notes,
            'validated_by' => $adminUserId,
        ]);

        // Recalculate elapsed time if needed
        $this->recalculateElapsedTime($validatedTime);

        // If finish checkpoint, recalculate positions
        if ($validatedTime->checkpoint->isFinish()) {
            $participant = $validatedTime->participant;
            $participant->update(['elapsed_time' => $validatedTime->elapsed_time]);
            $this->recalculatePositions($participant->event_category_id);
        }

        return $validatedTime->fresh();
    }

    /**
     * Recalculate elapsed time for a validated time entry
     */
    protected function recalculateElapsedTime(RfidValidatedTime $validatedTime): void
    {
        $participant = $validatedTime->participant;
        $startCheckpoint = $participant->category->startCheckpoint();

        if (!$startCheckpoint) return;

        $startTime = RfidValidatedTime::where('participant_id', $participant->id)
            ->where('rfid_checkpoint_id', $startCheckpoint->id)
            ->first();

        if (!$startTime) return;

        $elapsedSeconds = $validatedTime->checkpoint_time->diffInSeconds($startTime->checkpoint_time);
        $validatedTime->update([
            'elapsed_time' => gmdate('H:i:s', $elapsedSeconds),
        ]);
    }

    /**
     * Manual entry for participant who missed RFID scan
     */
    public function manualEntry(
        int $participantId,
        int $checkpointId,
        string $checkpointTime,
        int $adminUserId,
        ?string $notes = null
    ): RfidValidatedTime {
        $participant = Participant::findOrFail($participantId);
        $checkpoint = RfidCheckpoint::findOrFail($checkpointId);

        // Check if already recorded
        $existing = RfidValidatedTime::where('participant_id', $participantId)
            ->where('rfid_checkpoint_id', $checkpointId)
            ->first();

        if ($existing) {
            throw new \Exception('Participant already has a time for this checkpoint');
        }

        $checkpointTimeCarbon = Carbon::parse($checkpointTime);

        // Calculate elapsed time
        $elapsedTime = null;
        $startCheckpoint = $participant->category->startCheckpoint();
        if ($startCheckpoint) {
            $startTime = RfidValidatedTime::where('participant_id', $participant->id)
                ->where('rfid_checkpoint_id', $startCheckpoint->id)
                ->first();

            if ($startTime) {
                $elapsedSeconds = $checkpointTimeCarbon->diffInSeconds($startTime->checkpoint_time);
                $elapsedTime = gmdate('H:i:s', $elapsedSeconds);
            }
        }

        // Calculate split time
        $splitTime = null;
        $previousCheckpoint = RfidCheckpoint::where('event_category_id', $checkpoint->event_category_id)
            ->where('checkpoint_order', '<', $checkpoint->checkpoint_order)
            ->where('is_active', true)
            ->orderBy('checkpoint_order', 'desc')
            ->first();

        if ($previousCheckpoint) {
            $previousTime = RfidValidatedTime::where('participant_id', $participant->id)
                ->where('rfid_checkpoint_id', $previousCheckpoint->id)
                ->first();

            if ($previousTime) {
                $splitSeconds = $checkpointTimeCarbon->diffInSeconds($previousTime->checkpoint_time);
                $splitTime = gmdate('H:i:s', $splitSeconds);
            }
        }

        // Calculate position
        $position = RfidValidatedTime::where('rfid_checkpoint_id', $checkpointId)
            ->where('checkpoint_time', '<', $checkpointTimeCarbon)
            ->count() + 1;

        $validatedTime = RfidValidatedTime::create([
            'participant_id' => $participantId,
            'rfid_checkpoint_id' => $checkpointId,
            'rfid_raw_log_id' => null,
            'checkpoint_time' => $checkpointTimeCarbon,
            'elapsed_time' => $elapsedTime,
            'split_time' => $splitTime,
            'position_at_checkpoint' => $position,
            'validation_status' => 'manual',
            'validation_notes' => $notes ?? 'Manual entry by admin',
            'validated_by' => $adminUserId,
        ]);

        // If finish, update participant and recalculate
        if ($checkpoint->isFinish()) {
            $participant->update(['elapsed_time' => $elapsedTime]);
            $this->recalculatePositions($participant->event_category_id);
        }

        return $validatedTime;
    }

    /**
     * Get live results for an event category
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
                'position' => $gender ? $position++ : $p->category_position,
                'bib' => $p->bib,
                'name' => $p->display_name,
                'gender' => $p->gender,
                'age' => $p->age,
                'community' => $p->community,
                'elapsed_time' => $p->formatted_elapsed_time,
                'checkpoints' => $p->validatedTimes->map(function ($vt) {
                    return [
                        'checkpoint' => $vt->checkpoint->checkpoint_name,
                        'time' => $vt->checkpoint_time->format('H:i:s'),
                        'elapsed' => $vt->formatted_elapsed_time,
                        'split' => $vt->formatted_split_time,
                    ];
                }),
            ];
        })->toArray();
    }

    /**
     * Get checkpoint status for monitoring
     */
    public function getCheckpointStatus(int $checkpointId): array
    {
        $checkpoint = RfidCheckpoint::with('eventCategory')->findOrFail($checkpointId);

        $totalParticipants = Participant::where('event_category_id', $checkpoint->event_category_id)->count();
        $passedCount = RfidValidatedTime::where('rfid_checkpoint_id', $checkpointId)->count();

        $recentScans = RfidRawLog::where('rfid_checkpoint_id', $checkpointId)
            ->orderBy('scanned_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'checkpoint' => [
                'id' => $checkpoint->id,
                'name' => $checkpoint->checkpoint_name,
                'type' => $checkpoint->checkpoint_type,
                'distance_km' => $checkpoint->distance_km,
            ],
            'statistics' => [
                'total_participants' => $totalParticipants,
                'passed' => $passedCount,
                'remaining' => $totalParticipants - $passedCount,
                'percentage' => $totalParticipants > 0
                    ? round(($passedCount / $totalParticipants) * 100, 1)
                    : 0,
            ],
            'recent_scans' => $recentScans->map(function ($scan) {
                return [
                    'rfid_tag' => $scan->rfid_tag,
                    'bib' => $scan->bib,
                    'scanned_at' => $scan->scanned_at->format('H:i:s'),
                    'is_valid' => $scan->is_valid,
                    'notes' => $scan->notes,
                ];
            }),
        ];
    }
}
