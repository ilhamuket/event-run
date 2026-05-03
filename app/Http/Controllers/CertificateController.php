<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CertificateController extends Controller
{
    /**
     * Halaman pencarian sertifikat — user input BIB number
     * GET /certificate
     */
    public function index()
    {
        return view('event.certificate-index');
    }

    /**
     * Lookup peserta berdasarkan BIB — dipanggil via AJAX
     * GET /certificate/lookup?bib=1234
     */
    public function lookup(Request $request)
    {
        $bib = trim($request->query('bib', ''));

        if (empty($bib)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor BIB tidak boleh kosong.',
            ], 400);
        }

        // Cache 60 detik per BIB — kurangi hit DB saat banyak yang akses
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
                    ec.name  AS category_name,
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

            // Gunakan gun_elapsed_time kalau kategori pakai gun time, fallback ke chip time
            $useGunTime  = $participant->category_gun_time !== null && $participant->gun_elapsed_time !== null;
            $finishTime  = $useGunTime
                ? \Carbon\Carbon::parse($participant->gun_elapsed_time)->format('H:i:s')
                : ($participant->elapsed_time
                    ? \Carbon\Carbon::parse($participant->elapsed_time)->format('H:i:s')
                    : null);

            // Potong nama panjang agar aman di sertifikat (maks 40 karakter tampilan)
            $displayName = mb_strtoupper(trim($participant->display_name));
            if (mb_strlen($displayName) > 40) {
                $displayName = mb_substr($displayName, 0, 40) . '…';
            }

            return [
                'bib'               => $participant->bib,
                'display_name'      => $displayName,
                'category'          => $participant->category_name ?? '-',
                'finish_time'       => $finishTime ?? '-',
                'general_position'  => $participant->general_position
                                        ? '#' . $participant->general_position
                                        : '-',
                'category_position' => $participant->category_position
                                        ? '#' . $participant->category_position
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
