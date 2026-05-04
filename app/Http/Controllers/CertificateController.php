<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CertificateController extends Controller
{
    public function index()
    {
        return view('event.certificate_index');
    }

    public function lookup(Request $request)
    {
        $bib = trim($request->query('bib', ''));

        if (empty($bib)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor BIB tidak boleh kosong.',
            ], 400);
        }

        $cacheKey = "certificate:bib:{$bib}";

        $data = Cache::remember($cacheKey, 60, function () use ($bib) {
            $participant = DB::selectOne("
                SELECT
                    p.id,
                    p.bib,
                    COALESCE(NULLIF(TRIM(p.bib_name), ''), NULLIF(TRIM(p.name), ''), 'PESERTA') AS display_name,
                    p.gender,
                    p.elapsed_time,
                    p.gun_elapsed_time,
                    p.general_position,
                    p.category_position,
                    p.event_category_id,
                    ec.name AS category_name,
                    ec.gun_time AS category_gun_time
                FROM participants p
                LEFT JOIN event_categories ec ON ec.id = p.event_category_id
                WHERE p.bib = ?
                  AND EXISTS (
                      SELECT 1 FROM transactions t
                      WHERE t.participant_id = p.id AND t.status = 'PAID'
                      LIMIT 1
                  )
                LIMIT 1
            ", [$bib]);

            if (! $participant) {
                return null;
            }

            // Hitung total finisher overall
            $totalOverall = Cache::remember('certificate:total_overall', 300, function () {
                return DB::table('participants')
                    ->whereExists(function ($q) {
                        $q->from('transactions')
                          ->whereColumn('transactions.participant_id', 'participants.id')
                          ->where('transactions.status', 'PAID');
                    })
                    ->count();
            });

            // Hitung total finisher kategori yang sama
            $totalCategory = Cache::remember(
                'certificate:total_category:' . $participant->event_category_id,
                300,
                function () use ($participant) {
                    return DB::table('participants')
                        ->where('event_category_id', $participant->event_category_id)
                        ->whereExists(function ($q) {
                            $q->from('transactions')
                              ->whereColumn('transactions.participant_id', 'participants.id')
                              ->where('transactions.status', 'PAID');
                        })
                        ->count();
                }
            );

            $finishTime = $participant->elapsed_time
                ? \Carbon\Carbon::parse($participant->elapsed_time)->format('H:i:s')
                : ($participant->gun_elapsed_time
                    ? \Carbon\Carbon::parse($participant->gun_elapsed_time)->format('H:i:s')
                    : null);

            $displayName = mb_strtoupper(trim($participant->display_name));
            if (mb_strlen($displayName) > 40) {
                $displayName = mb_substr($displayName, 0, 40) . '...';
            }

            return [
                'bib'               => $participant->bib,
                'display_name'      => $displayName,
                'category'          => $participant->category_name ?? '-',
                'finish_time'       => $finishTime ?? '-',
                'general_position'  => $participant->general_position
                                        ? $participant->general_position . ' / ' . $totalOverall
                                        : '-',
                'category_position' => $participant->category_position
                                        ? $participant->category_position . ' / ' . $totalCategory
                                        : '-',
            ];
        });

        if (! $data) {
            return response()->json([
                'success' => false,
                'message' => 'BIB tidak ditemukan atau pembayaran belum terverifikasi.',
            ]);
        }

        return response()->json([
            'success'     => true,
            'participant' => $data,
        ]);
    }
}
