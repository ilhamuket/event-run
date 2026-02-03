<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Participant;
use App\Models\RfidCheckpoint;
use App\Models\RfidRawLog;
use App\Models\RfidValidatedTime;
use App\Services\RfidTimingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RfidTimingController extends Controller
{
    protected RfidTimingService $timingService;

    public function __construct(RfidTimingService $timingService)
    {
        $this->timingService = $timingService;
    }

    /**
     * Process RFID scan from reader device
     * This is the main endpoint called by the Python scanner script
     *
     * POST /api/rfid/scan
     */
    public function processScan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|integer|exists:events,id',
            'checkpoint_id' => 'required|integer|exists:rfid_checkpoints,id',
            'rfid_tag' => 'required|string|max:255',
            'reader_id' => 'nullable|string|max:255',
            'signal_strength' => 'nullable|integer',
        ]);

        Log::info('RFID Scan Received', $request->all());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'validation_failed',
                'message' => 'Invalid request data',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->timingService->processScan(
                $request->event_id,
                $request->checkpoint_id,
                $request->rfid_tag,
                $request->reader_id,
                $request->signal_strength
            );

            $statusCode = $result['success'] ? 200 : 200; // Always 200 for valid responses, error info in body
            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'processing_error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get device/checkpoint configuration by IP address
     * Called by Python script to get checkpoint info
     *
     * GET /api/rfid/device
     */
    public function getDeviceConfig(Request $request): JsonResponse
    {
        $ip = $request->query('ip');

        if (!$ip) {
            return response()->json([
                'status' => false,
                'message' => 'IP address required',
            ], 400);
        }

        // Find checkpoint by reader_id (IP address) or you can create a separate device table
        // For now, we'll use a simple config approach
        // You might want to create a device registration system

        // Example: Find checkpoint by matching reader location
        // This is a simplified approach - in production you'd have a device registration table
        $checkpoint = RfidCheckpoint::where('is_active', true)
            ->whereRaw("JSON_CONTAINS(COALESCE(reader_config, '{}'), ?)", [json_encode(['ip' => $ip])])
            ->first();

        if (!$checkpoint) {
            // Alternative: Check environment or config for device mapping
            $deviceMapping = config('rfid.device_mapping', []);

            if (isset($deviceMapping[$ip])) {
                $checkpointId = $deviceMapping[$ip]['checkpoint_id'];
                $checkpoint = RfidCheckpoint::find($checkpointId);
            }
        }

        if (!$checkpoint) {
            return response()->json([
                'status' => false,
                'message' => 'Device not registered',
                'ip' => $ip,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'checkpoint_id' => $checkpoint->id,
                'checkpoint_name' => $checkpoint->checkpoint_name,
                'checkpoint_type' => $checkpoint->checkpoint_type,
                'event_id' => $checkpoint->eventCategory->event_id,
                'event_category_id' => $checkpoint->event_category_id,
                'rfid_start' => config('rfid.rfid_start', 4),   // byte position where RFID starts
                'rfid_length' => config('rfid.rfid_length', 24), // RFID tag length
            ],
        ]);
    }

    /**
     * Get live results for a category
     *
     * GET /api/rfid/results/{categoryId}
     */
    public function getLiveResults(int $categoryId, Request $request): JsonResponse
    {
        try {
            $gender = $request->query('gender');
            $results = $this->timingService->getLiveResults($categoryId, $gender);

            return response()->json([
                'success' => true,
                'data' => $results,
                'meta' => [
                    'category_id' => $categoryId,
                    'gender_filter' => $gender,
                    'count' => count($results),
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get checkpoint monitoring status
     *
     * GET /api/rfid/checkpoint/{checkpointId}/status
     */
    public function getCheckpointStatus(int $checkpointId): JsonResponse
    {
        try {
            $status = $this->timingService->getCheckpointStatus($checkpointId);

            return response()->json([
                'success' => true,
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get all checkpoints for an event
     *
     * GET /api/rfid/event/{eventId}/checkpoints
     */
    public function getEventCheckpoints(int $eventId): JsonResponse
    {
        $event = Event::with(['categories.rfidCheckpoints' => function ($query) {
            $query->where('is_active', true)->orderBy('checkpoint_order');
        }])->find($eventId);

        if (!$event) {
            return response()->json([
                'success' => false,
                'error' => 'Event not found',
            ], 404);
        }

        $checkpoints = [];
        foreach ($event->categories as $category) {
            foreach ($category->rfidCheckpoints as $checkpoint) {
                $checkpoints[] = [
                    'id' => $checkpoint->id,
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'checkpoint_name' => $checkpoint->checkpoint_name,
                    'checkpoint_type' => $checkpoint->checkpoint_type,
                    'checkpoint_order' => $checkpoint->checkpoint_order,
                    'distance_km' => $checkpoint->distance_km,
                    'cutoff_time' => $checkpoint->cutoff_time?->format('H:i'),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $checkpoints,
        ]);
    }

    /**
     * Get participant info by RFID tag
     *
     * GET /api/rfid/participant/by-rfid/{rfidTag}
     */
    public function getParticipantByRfid(string $rfidTag): JsonResponse
    {
        $participant = \App\Models\ParticipantRfidMapping::findParticipantByRfid($rfidTag);

        if (!$participant) {
            return response()->json([
                'success' => false,
                'error' => 'Participant not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $participant->id,
                'bib' => $participant->bib,
                'name' => $participant->name,
                'gender' => $participant->gender,
                'age' => $participant->age,
                'category' => $participant->category?->name,
                'community' => $participant->community,
            ],
        ]);
    }

    /**
     * Get participant timing details
     *
     * GET /api/rfid/participant/{participantId}/times
     */
    public function getParticipantTimes(int $participantId): JsonResponse
    {
        $participant = Participant::with([
            'category',
            'validatedTimes.checkpoint',
        ])->find($participantId);

        if (!$participant) {
            return response()->json([
                'success' => false,
                'error' => 'Participant not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'participant' => [
                    'id' => $participant->id,
                    'bib' => $participant->bib,
                    'name' => $participant->display_name,
                    'gender' => $participant->gender,
                    'category' => $participant->category?->name,
                    'elapsed_time' => $participant->formatted_elapsed_time,
                    'general_position' => $participant->general_position,
                    'category_position' => $participant->category_position,
                ],
                'checkpoints' => $participant->validatedTimes
                    ->sortBy('checkpoint.checkpoint_order')
                    ->values()
                    ->map(function ($vt) {
                        return [
                            'checkpoint_id' => $vt->rfid_checkpoint_id,
                            'checkpoint_name' => $vt->checkpoint->checkpoint_name,
                            'checkpoint_type' => $vt->checkpoint->checkpoint_type,
                            'checkpoint_time' => $vt->checkpoint_time->format('Y-m-d H:i:s'),
                            'elapsed_time' => $vt->formatted_elapsed_time,
                            'split_time' => $vt->formatted_split_time,
                            'position' => $vt->position_at_checkpoint,
                            'validation_status' => $vt->validation_status,
                        ];
                    }),
            ],
        ]);
    }

    /**
     * Manual time entry by admin
     *
     * POST /api/rfid/manual-entry
     */
    public function manualEntry(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'participant_id' => 'required|integer|exists:participants,id',
            'checkpoint_id' => 'required|integer|exists:rfid_checkpoints,id',
            'checkpoint_time' => 'required|date_format:Y-m-d H:i:s',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'validation_failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Get admin user ID from auth (adjust based on your auth system)
            $adminUserId = Auth::user()?->id ?? 1;

            $validatedTime = $this->timingService->manualEntry(
                $request->participant_id,
                $request->checkpoint_id,
                $request->checkpoint_time,
                $adminUserId,
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Manual entry created successfully',
                'data' => [
                    'validated_time_id' => $validatedTime->id,
                    'checkpoint_time' => $validatedTime->checkpoint_time->format('Y-m-d H:i:s'),
                    'elapsed_time' => $validatedTime->formatted_elapsed_time,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Correct existing time
     *
     * PUT /api/rfid/correct-time/{validatedTimeId}
     */
    public function correctTime(int $validatedTimeId, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'new_time' => 'required|date_format:Y-m-d H:i:s',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'validation_failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $adminUserId = Auth::user()?->id ?? 1;

            $validatedTime = $this->timingService->correctTime(
                $validatedTimeId,
                $request->new_time,
                $adminUserId,
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Time corrected successfully',
                'data' => [
                    'validated_time_id' => $validatedTime->id,
                    'checkpoint_time' => $validatedTime->checkpoint_time->format('Y-m-d H:i:s'),
                    'elapsed_time' => $validatedTime->formatted_elapsed_time,
                    'validation_status' => $validatedTime->validation_status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get raw logs for debugging
     *
     * GET /api/rfid/raw-logs
     */
    public function getRawLogs(Request $request): JsonResponse
    {
        $query = RfidRawLog::with(['checkpoint', 'event'])
            ->orderBy('scanned_at', 'desc');

        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->has('checkpoint_id')) {
            $query->where('rfid_checkpoint_id', $request->checkpoint_id);
        }

        if ($request->has('rfid_tag')) {
            $query->where('rfid_tag', $request->rfid_tag);
        }

        if ($request->has('is_valid')) {
            $query->where('is_valid', $request->boolean('is_valid'));
        }

        $logs = $query->limit($request->get('limit', 100))->get();

        return response()->json([
            'success' => true,
            'data' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'event_id' => $log->event_id,
                    'checkpoint' => $log->checkpoint?->checkpoint_name,
                    'rfid_tag' => $log->rfid_tag,
                    'bib' => $log->bib,
                    'scanned_at' => $log->scanned_at->format('Y-m-d H:i:s'),
                    'reader_id' => $log->reader_id,
                    'signal_strength' => $log->signal_strength,
                    'is_valid' => $log->is_valid,
                    'notes' => $log->notes,
                ];
            }),
        ]);
    }
}
