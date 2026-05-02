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
     * Page: cek status pembayaran vs Tripay
     */
    public function checkPaymentStatus(): View
    {
        $unpaidTransactions = Transaction::unpaid()
            ->whereNotNull('tripay_reference')
            ->with(['participant:id,name,email', 'event:id,name'])
            ->get(['id', 'participant_id', 'event_id', 'merchant_ref', 'tripay_reference', 'amount', 'total_amount', 'status', 'expired_at']);

        $results = [];

        foreach ($unpaidTransactions as $transaction) {
            try {
                $tripayStatus = $this->tripayService->checkStatus($transaction->tripay_reference);

                $results[] = [
                    'id'               => $transaction->id,
                    'merchant_ref'     => $transaction->merchant_ref,
                    'tripay_reference' => $transaction->tripay_reference,
                    'participant_id'   => $transaction->participant_id,
                    'participant_name' => $transaction->participant?->name,
                    'event_id'         => $transaction->event_id,
                    'event_name'       => $transaction->event?->name,
                    'total_amount'     => $transaction->total_amount,
                    'status_db'        => $transaction->status,
                    'status_tripay'    => $tripayStatus,
                    'mismatch'         => $transaction->status !== $tripayStatus,
                    'expired_at'       => $transaction->expired_at,
                ];

            } catch (\Exception $e) {
                $results[] = [
                    'id'               => $transaction->id,
                    'merchant_ref'     => $transaction->merchant_ref,
                    'tripay_reference' => $transaction->tripay_reference,
                    'participant_id'   => $transaction->participant_id,
                    'participant_name' => $transaction->participant?->name,
                    'event_id'         => $transaction->event_id,
                    'event_name'       => $transaction->event?->name,
                    'total_amount'     => $transaction->total_amount,
                    'status_db'        => $transaction->status,
                    'status_tripay'    => null,
                    'mismatch'         => null,
                    'expired_at'       => $transaction->expired_at,
                    'error'            => $e->getMessage(),
                ];
            }
        }

        $mismatched = array_values(array_filter($results, fn($r) => ($r['mismatch'] ?? false) === true && ($r['status_tripay'] ?? null) === 'PAID'));
        $errors     = array_values(array_filter($results, fn($r) => isset($r['error'])));
        $synced     = array_values(array_filter($results, fn($r) => ($r['mismatch'] ?? null) === false));

        $summary = [
            'total_checked'  => count($results),
            'total_mismatch' => count($mismatched),
            'total_synced'   => count($synced),
            'total_error'    => count($errors),
        ];

        return view('event.check-payment-status', compact(
            'summary',
            'mismatched',
            'errors',
            'results' // dipakai sebagai $all di blade
        ))->with('all', $results);
    }
}
