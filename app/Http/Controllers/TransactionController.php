<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Transaction;
use App\Services\TripayService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        protected TripayService $tripayService
    ) {}

   /**
     * Page: hanya render view + list transaksi dari DB (tanpa hit Tripay)
     */
    public function checkPaymentStatus(): View
    {
        $transactions = Transaction::unpaid()
            ->whereNotNull('tripay_reference')
            ->with(['participant:id,name,email', 'event:id,name'])
            ->get(['id', 'participant_id', 'event_id', 'merchant_ref', 'tripay_reference', 'amount', 'total_amount', 'status', 'expired_at'])
            ->map(fn($t) => [
                'id'               => $t->id,
                'merchant_ref'     => $t->merchant_ref,
                'tripay_reference' => $t->tripay_reference,
                'participant_name' => $t->participant?->name,
                'event_name'       => $t->event?->name,
                'total_amount'     => $t->total_amount,
                'status_db'        => $t->status,
                'expired_at'       => $t->expired_at?->format('d M Y H:i'),
            ]);

        return view('event.check-payment-status', compact('transactions'));
    }

    /**
     * API: cek 1 transaksi ke Tripay (dipanggil JS satu per satu)
     */
    public function checkSingleStatus(string $tripayReference): JsonResponse
    {
        try {
            $status = $this->tripayService->checkStatus($tripayReference);

            return response()->json([
                'success'       => true,
                'status_tripay' => $status,
                'mismatch'      => $status !== Transaction::STATUS_UNPAID,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
