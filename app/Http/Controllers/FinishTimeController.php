<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinishTimeController extends Controller
{
    /**
     * POST /admin/finish-time
     *
     * Body JSON:
     *   bib          string   required   nomor BIB peserta
     *   elapsed_time string   required   format HH:MM:SS (chip elapsed: start→finish)
     *
     * Prioritas elapsed_time:
     *   - Selalu disimpan as-is ke participants.elapsed_time & rfid_validated_times.elapsed_time
     *     (ini yang dipakai sertifikat)
     *
     * Kalkulasi chip_finish (untuk checkpoint_time & gun_elapsed):
     *   1. Cari start time dari rfid_validated_times (checkpoint_type = start)
     *   2. Fallback: gunakan gun_time kategori sebagai start
     *   3. Fallback terakhir: chip_finish = now() — elapsed_time tetap tersimpan
     *
     * gun_elapsed_time = chip_finish - event_categories.gun_time
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'bib'          => ['required', 'string'],
            'elapsed_time' => ['required', 'regex:/^\d{2}:\d{2}:\d{2}$/'],
        ]);

        $bib         = $request->input('bib');
        $chipElapsed = $request->input('elapsed_time'); // HH:MM:SS

        DB::transaction(function () use ($bib, $chipElapsed) {

            // ── 1. Ambil data participant + gun_time kategori ────────────────
            $participant = DB::selectOne("
                SELECT
                    p.id              AS participant_id,
                    p.bib,
                    p.event_category_id,
                    ec.gun_time
                FROM participants p
                JOIN event_categories ec ON ec.id = p.event_category_id
                WHERE p.bib = ?
                LIMIT 1
            ", [$bib]);

            abort_unless($participant, 404, "BIB {$bib} tidak ditemukan");

            // ── 2. Cari start time (chip start dari rfid_validated_times) ────
            $startRecord = DB::selectOne("
                SELECT vt.checkpoint_time
                FROM rfid_validated_times vt
                JOIN rfid_checkpoints cp ON cp.id = vt.rfid_checkpoint_id
                WHERE vt.participant_id = ?
                  AND cp.checkpoint_type = 'start'
                  AND cp.is_active = 1
                  AND cp.event_category_id = ?
                LIMIT 1
            ", [$participant->participant_id, $participant->event_category_id]);

            // ── 3. Tentukan startTime dengan fallback ────────────────────────
            //
            //   Prioritas:
            //     A. rfid_validated_times start  → chip start yg sebenarnya
            //     B. gun_time kategori            → tag miss saat start
            //     C. null                         → chip_finish tidak bisa dihitung
            //                                       tapi elapsed_time tetap disimpan
            $startSource = null;

            if ($startRecord) {
                $startTime   = Carbon::parse($startRecord->checkpoint_time);
                $startSource = 'rfid_start';
            } elseif ($participant->gun_time) {
                $startTime   = Carbon::parse($participant->gun_time);
                $startSource = 'gun_time_fallback';
                Log::warning('FinishTimeController: start rfid tidak ada, fallback ke gun_time', [
                    'bib'      => $bib,
                    'gun_time' => $participant->gun_time,
                ]);
            } else {
                $startTime   = null;
                $startSource = 'none';
                Log::warning('FinishTimeController: tidak ada start time maupun gun_time', [
                    'bib' => $bib,
                ]);
            }

            // ── 3b. Hitung chip_finish ───────────────────────────────────────
            [$h, $m, $s] = array_map('intval', explode(':', $chipElapsed));

            $chipFinish = $startTime
                ? $startTime->copy()->addHours($h)->addMinutes($m)->addSeconds($s)
                : now(); // last-resort agar checkpoint_time tidak null

            // ── 4. Hitung gun_elapsed_time ───────────────────────────────────
            //      gun_elapsed = chip_finish - gun_time
            $gunElapsedTime = null;

            if ($participant->gun_time) {
                $gunTime    = Carbon::parse($participant->gun_time);
                $gunSeconds = $chipFinish->timestamp - $gunTime->timestamp;

                if ($gunSeconds >= 0) {
                    $gunElapsedTime = gmdate('H:i:s', $gunSeconds);
                } else {
                    Log::warning('FinishTimeController: gun_elapsed negatif', [
                        'bib'         => $bib,
                        'gun_time'    => $participant->gun_time,
                        'chip_finish' => $chipFinish->toIso8601String(),
                    ]);
                }
            }

            // ── 5. Resolve finish checkpoint ─────────────────────────────────
            $finishCheckpoint = DB::selectOne("
                SELECT id
                FROM rfid_checkpoints
                WHERE event_category_id = ?
                  AND checkpoint_type = 'finish'
                  AND is_active = 1
                LIMIT 1
            ", [$participant->event_category_id]);

            abort_unless($finishCheckpoint, 422, "Finish checkpoint untuk kategori peserta tidak ditemukan");

            // ── 6. Upsert rfid_validated_times (finish) ──────────────────────
            $existing = DB::selectOne("
                SELECT id
                FROM rfid_validated_times
                WHERE participant_id     = ?
                  AND rfid_checkpoint_id = ?
                LIMIT 1
                FOR UPDATE
            ", [$participant->participant_id, $finishCheckpoint->id]);

            $now = now()->format('Y-m-d H:i:s');

            if ($existing) {
                DB::update("
                    UPDATE rfid_validated_times
                    SET checkpoint_time   = ?,
                        elapsed_time      = ?,
                        validation_status = 'manual_override',
                        updated_at        = ?
                    WHERE id = ?
                ", [
                    $chipFinish->format('Y-m-d H:i:s'),
                    $chipElapsed,
                    $now,
                    $existing->id,
                ]);
            } else {
                // Hitung posisi (append ke belakang)
                $posRow   = DB::selectOne(
                    'SELECT COUNT(*) as cnt FROM rfid_validated_times WHERE rfid_checkpoint_id = ?',
                    [$finishCheckpoint->id]
                );
                $position = ($posRow->cnt ?? 0) + 1;

                DB::insert("
                    INSERT INTO rfid_validated_times
                        (participant_id, rfid_checkpoint_id, rfid_raw_log_id,
                         checkpoint_time, elapsed_time, split_time,
                         position_at_checkpoint, validation_status, created_at, updated_at)
                    VALUES (?, ?, NULL, ?, ?, NULL, ?, 'manual_override', ?, ?)
                ", [
                    $participant->participant_id,
                    $finishCheckpoint->id,
                    $chipFinish->format('Y-m-d H:i:s'),
                    $chipElapsed,
                    $position,
                    $now,
                    $now,
                ]);
            }

            // ── 7. Update participants ────────────────────────────────────────
            DB::update("
                UPDATE participants
                SET elapsed_time     = ?,
                    gun_elapsed_time = ?,
                    updated_at       = ?
                WHERE id = ?
            ", [
                $chipElapsed,
                $gunElapsedTime,
                $now,
                $participant->participant_id,
            ]);

            Log::info('FinishTime manual override', [
                'bib'          => $bib,
                'chip_elapsed' => $chipElapsed,
                'chip_finish'  => $chipFinish->toIso8601String(),
                'gun_elapsed'  => $gunElapsedTime,
                'start_source' => $startSource,
                'start_time'   => $startTime?->toIso8601String(),
                'gun_time'     => $participant->gun_time,
            ]);
        });

        return response()->json([
            'message' => "Waktu finish BIB {$bib} berhasil diperbarui",
        ]);
    }
}
