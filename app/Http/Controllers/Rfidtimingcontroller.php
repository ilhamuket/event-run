<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use App\Models\RfidCheckpoint;
use App\Models\RfidRawLog;
use App\Models\RfidValidatedTime;
use App\Services\RfidTimingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Jobs\ProcessRfidScan;

class RfidTimingController extends Controller
{
    protected RfidTimingService $timingService;

    public function __construct(RfidTimingService $timingService)
    {
        $this->timingService = $timingService;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // SCANNER ENDPOINTS
    // Route: prefix('rfid'), no auth middleware (pakai device key)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Proses scan RFID dari Go scanner.
     *
     * POST /api/rfid/scan
     *
     * Header wajib: X-DEVICE-KEY
     *
     * HTTP status codes yang dikembalikan:
     * - 200: Sukses (scan valid, tersimpan)
     * - 200: Skip normal (duplicate, unknown RFID, category mismatch)
     *        → Scanner tidak perlu retry untuk ini
     * - 401: Device key salah → Scanner harus stop
     * - 422: Request tidak valid (missing field, format salah)
     * - 500: Error internal → Scanner bisa queue dan retry
     */
   public function processScan(Request $request): JsonResponse
    {
        $deviceKey = $request->header('X-DEVICE-KEY');
        if (!$deviceKey || $deviceKey !== config('rfid.device_key')) {
            Log::warning('RFID scan rejected: invalid device key', ['ip' => $request->ip()]);
            return response()->json(['success' => false, 'error' => 'unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'event_id'        => 'required|integer|exists:events,id',
            'checkpoint_type' => 'required|string|in:start,finish,checkpoint',
            'rfid_tag'        => 'required|string|max:255',
            'reader_id'       => 'nullable|string|max:100',
            'signal_strength' => 'nullable|integer|min:-100|max:0',
            'scanned_at'      => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error'   => 'validation_failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $v         = $validator->validated();
        $rfidTag   = strtoupper(trim($v['rfid_tag']));
        $scannedAt = isset($v['scanned_at']) ? Carbon::parse($v['scanned_at']) : Carbon::now();

        // ── Tulis raw log SEKARANG, di HTTP request ──────────────────────────
        // Ini yang bikin response cepat: tidak ada DB lock, tidak ada query berat.
        // Hanya satu INSERT sederhana, selesai dalam <5ms.
        $rawLog = RfidRawLog::create([
            'event_id'           => $v['event_id'],
            'rfid_checkpoint_id' => null,      // di-resolve di job nanti
            'rfid_tag'           => $rfidTag,
            'scanned_at'         => $scannedAt,
            'reader_id'          => $v['reader_id'] ?? null,
            'signal_strength'    => $v['signal_strength'] ?? null,
            'is_valid'           => true,      // asumsi valid, job yang update kalau tidak
        ]);

        // ── Dispatch ke queue — non-blocking ─────────────────────────────────
        ProcessRfidScan::dispatch(
            eventId:        $v['event_id'],
            checkpointType: $v['checkpoint_type'],
            rfidTag:        $rfidTag,
            readerId:       $v['reader_id'] ?? null,
            signalStrength: $v['signal_strength'] ?? null,
            scannedAt:      $scannedAt->format('Y-m-d H:i:s'),
            rawLogId:       $rawLog->id,
        )->onQueue('rfid'); // queue terpisah supaya tidak rebutan dengan queue lain

        // ── Response langsung ke scanner — <10ms ─────────────────────────────
        return response()->json([
            'success'    => true,
            'message'    => 'Scan received',
            'raw_log_id' => $rawLog->id,
        ]);
    }

    /**
     * Konfigurasi device/checkpoint berdasarkan IP reader.
     * Dipanggil Go scanner saat startup untuk tahu checkpoint mana yang harus dia handle.
     *
     * GET /api/rfid/device?ip=192.168.1.10
     */
    public function getDeviceConfig(Request $request): JsonResponse
    {
        $key = $request->header('X-DEVICE-KEY');
        if (!$key || $key !== config('rfid.device_key')) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $ip = $request->query('ip');

        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return response()->json([
                'status'  => false,
                'message' => 'Valid IP address required',
            ], 400);
        }

        // Cari checkpoint via JSON config di kolom reader_config
        $checkpoint = RfidCheckpoint::where('is_active', true)
            ->whereRaw("JSON_CONTAINS(COALESCE(reader_config, '{}'), ?)", [json_encode(['ip' => $ip])])
            ->first();

        // Fallback ke config file (untuk setup awal / testing)
        if (!$checkpoint) {
            $deviceMapping = config('rfid.device_mapping', []);
            if (isset($deviceMapping[$ip])) {
                $checkpoint = RfidCheckpoint::find($deviceMapping[$ip]['checkpoint_id']);
            }
        }

        if (!$checkpoint) {
            return response()->json([
                'status'  => false,
                'message' => 'Device not registered',
                'ip'      => $ip,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'checkpoint_id'      => $checkpoint->id,
                'checkpoint_name'    => $checkpoint->checkpoint_name,
                'checkpoint_type'    => $checkpoint->checkpoint_type,
                'event_id'           => $checkpoint->eventCategory->event_id,
                'event_category_id'  => $checkpoint->event_category_id,
                'rfid_start'         => config('rfid.rfid_start', 4),
                'rfid_length'        => config('rfid.rfid_length', 24),
                'device_key'         => config('rfid.device_key'), // scanner butuh ini untuk /scan
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PUBLIC READ ENDPOINTS
    // Tidak butuh auth — live results bisa publik
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Live results untuk kategori.
     *
     * GET /api/rfid/results/{categoryId}?gender=M
     */
    public function getLiveResults(int $categoryId, Request $request): JsonResponse
    {
        try {
            $gender  = $request->query('gender');
            $results = $this->timingService->getLiveResults($categoryId, $gender);

            return response()->json([
                'success' => true,
                'data'    => $results,
                'meta'    => [
                    'category_id'   => $categoryId,
                    'gender_filter' => $gender,
                    'count'         => count($results),
                    'timestamp'     => now()->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Status checkpoint untuk monitoring.
     *
     * GET /api/rfid/checkpoint/{checkpointId}/status
     */
    public function getCheckpointStatus(int $checkpointId): JsonResponse
    {
        try {
            $status = $this->timingService->getCheckpointStatus($checkpointId);

            return response()->json(['success' => true, 'data' => $status]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 404);
        }
    }

    /**
     * Semua checkpoint dalam satu event.
     *
     * GET /api/rfid/event/{eventId}/checkpoints
     */
    public function getEventCheckpoints(int $eventId): JsonResponse
    {
        $event = Event::with(['categories.rfidCheckpoints' => function ($q) {
            $q->where('is_active', true)->orderBy('checkpoint_order');
        }])->find($eventId);

        if (!$event) {
            return response()->json(['success' => false, 'error' => 'Event not found'], 404);
        }

        $checkpoints = [];
        foreach ($event->categories as $category) {
            foreach ($category->rfidCheckpoints as $checkpoint) {
                $checkpoints[] = [
                    'id'               => $checkpoint->id,
                    'category_id'      => $category->id,
                    'category_name'    => $category->name,
                    'checkpoint_name'  => $checkpoint->checkpoint_name,
                    'checkpoint_type'  => $checkpoint->checkpoint_type,
                    'checkpoint_order' => $checkpoint->checkpoint_order,
                    'distance_km'      => $checkpoint->distance_km,
                    'cutoff_time'      => $checkpoint->cutoff_time?->format('H:i'),
                ];
            }
        }

        return response()->json(['success' => true, 'data' => $checkpoints]);
    }

    /**
     * Info peserta berdasarkan RFID tag.
     *
     * GET /api/rfid/participant/by-rfid/{rfidTag}
     */
    public function getParticipantByRfid(string $rfidTag): JsonResponse
    {
        $rfidTag = strtoupper(trim($rfidTag));

        $participant = \App\Models\ParticipantRfidMapping::findParticipantByRfid($rfidTag);

        if (!$participant) {
            return response()->json(['success' => false, 'error' => 'Participant not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'        => $participant->id,
                'bib'       => $participant->bib,
                'name'      => $participant->name,
                'gender'    => $participant->gender,
                'age'       => $participant->age,
                'category'  => $participant->category?->name,
                'community' => $participant->community,
            ],
        ]);
    }

    /**
     * Detail waktu per checkpoint untuk satu peserta.
     *
     * GET /api/rfid/participant/{participantId}/times
     */
    public function getParticipantTimes(int $participantId): JsonResponse
    {
        $participant = Participant::with(['category', 'validatedTimes.checkpoint'])
            ->find($participantId);

        if (!$participant) {
            return response()->json(['success' => false, 'error' => 'Participant not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'participant' => [
                    'id'                => $participant->id,
                    'bib'               => $participant->bib,
                    'name'              => $participant->display_name,
                    'gender'            => $participant->gender,
                    'category'          => $participant->category?->name,
                    'elapsed_time'      => $participant->formatted_elapsed_time,
                    'general_position'  => $participant->general_position,
                    'category_position' => $participant->category_position,
                ],
                'checkpoints' => $participant->validatedTimes
                    ->sortBy('checkpoint.checkpoint_order')
                    ->values()
                    ->map(fn($vt) => [
                        'checkpoint_id'     => $vt->rfid_checkpoint_id,
                        'checkpoint_name'   => $vt->checkpoint->checkpoint_name,
                        'checkpoint_type'   => $vt->checkpoint->checkpoint_type,
                        'checkpoint_time'   => $vt->checkpoint_time->format('Y-m-d H:i:s'),
                        'elapsed_time'      => $vt->formatted_elapsed_time,
                        'split_time'        => $vt->formatted_split_time,
                        'position'          => $vt->position_at_checkpoint,
                        'validation_status' => $vt->validation_status,
                    ]),
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // ADMIN ENDPOINTS
    // Route: middleware(['auth:sanctum', 'role:admin'])
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Manual entry untuk peserta yang missed scan.
     *
     * POST /api/admin/rfid/manual-entry
     */
    public function manualEntry(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'participant_id'  => 'required|integer|exists:participants,id',
            'checkpoint_id'   => 'required|integer|exists:rfid_checkpoints,id',
            'checkpoint_time' => 'required|date_format:Y-m-d H:i:s',
            'notes'           => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error'   => 'validation_failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // Tidak ada fallback ke user ID 1 — route ini sudah dilindungi auth middleware.
            $adminUserId = Auth::id();

            $validatedTime = $this->timingService->manualEntry(
                participantId:  $request->participant_id,
                checkpointId:   $request->checkpoint_id,
                checkpointTime: $request->checkpoint_time,
                adminUserId:    $adminUserId,
                notes:          $request->notes
            );

            Log::info('RFID manual entry', [
                'admin_id'        => $adminUserId,
                'participant_id'  => $request->participant_id,
                'checkpoint_id'   => $request->checkpoint_id,
                'checkpoint_time' => $request->checkpoint_time,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Manual entry created',
                'data'    => [
                    'validated_time_id' => $validatedTime->id,
                    'checkpoint_time'   => $validatedTime->checkpoint_time->format('Y-m-d H:i:s'),
                    'elapsed_time'      => $validatedTime->formatted_elapsed_time,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Koreksi waktu yang sudah ada.
     *
     * PUT /api/admin/rfid/correct-time/{validatedTimeId}
     */
    public function correctTime(int $validatedTimeId, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'new_time' => 'required|date_format:Y-m-d H:i:s',
            'notes'    => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error'   => 'validation_failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $adminUserId = Auth::id();

            $validatedTime = $this->timingService->correctTime(
                validatedTimeId: $validatedTimeId,
                newTime:         $request->new_time,
                adminUserId:     $adminUserId,
                notes:           $request->notes
            );

            Log::info('RFID time corrected', [
                'admin_id'          => $adminUserId,
                'validated_time_id' => $validatedTimeId,
                'new_time'          => $request->new_time,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Time corrected successfully',
                'data'    => [
                    'validated_time_id' => $validatedTime->id,
                    'checkpoint_time'   => $validatedTime->checkpoint_time->format('Y-m-d H:i:s'),
                    'elapsed_time'      => $validatedTime->formatted_elapsed_time,
                    'validation_status' => $validatedTime->validation_status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Raw logs untuk debugging.
     *
     * GET /api/admin/rfid/raw-logs
     */
    public function getRawLogs(Request $request): JsonResponse
    {
        $query = RfidRawLog::with(['checkpoint', 'event'])
            ->orderBy('scanned_at', 'desc');

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->integer('event_id'));
        }

        if ($request->filled('checkpoint_id')) {
            $query->where('rfid_checkpoint_id', $request->integer('checkpoint_id'));
        }

        if ($request->filled('rfid_tag')) {
            $query->where('rfid_tag', strtoupper(trim($request->rfid_tag)));
        }

        if ($request->filled('is_valid')) {
            $query->where('is_valid', $request->boolean('is_valid'));
        }

        $limit = min($request->integer('limit', 100), 500); // cap 500
        $logs  = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data'    => $logs->map(fn($log) => [
                'id'              => $log->id,
                'event_id'        => $log->event_id,
                'checkpoint'      => $log->checkpoint?->checkpoint_name,
                'rfid_tag'        => $log->rfid_tag,
                'bib'             => $log->bib,
                'scanned_at'      => $log->scanned_at->format('Y-m-d H:i:s'),
                'reader_id'       => $log->reader_id,
                'signal_strength' => $log->signal_strength,
                'is_valid'        => $log->is_valid,
                'notes'           => $log->notes,
            ]),
        ]);
    }
}
