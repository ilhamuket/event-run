<?php

namespace App\Http\Controllers;

use App\Mail\PaymentConfirmed;
use App\Models\Transaction;
use App\Services\TripayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;

class TripayCallbackController extends Controller
{
     public function __construct(
        protected TripayService $tripayService
    ) {}

    /**
     * Handle callback from Tripay
     */
    public function handle(Request $request)
    {
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json = $request->getContent();

        if (!$this->tripayService->validateCallback($callbackSignature, $json)) {
            Log::warning('Tripay callback: Invalid signature');
            return Response::json(['success' => false, 'message' => 'Invalid signature'], 403);
        }

        if ($request->server('HTTP_X_CALLBACK_EVENT') !== 'payment_status') {
            return Response::json(['success' => false, 'message' => 'Unrecognized callback event']);
        }

        try {
            $callback = $this->tripayService->parseCallback($json);
        } catch (\Exception $e) {
            Log::error('Tripay callback: Invalid JSON', ['error' => $e->getMessage()]);
            return Response::json(['success' => false, 'message' => 'Invalid callback data']);
        }

        $merchantRef = $callback->getMerchantRef();
        $tripayReference = $callback->getReference();

        // Raw query - faster than Eloquent for callback processing
        $tx = DB::selectOne(
            "SELECT id, status, participant_id, event_id
             FROM transactions
             WHERE merchant_ref = ? AND tripay_reference = ?
             LIMIT 1",
            [$merchantRef, $tripayReference]
        );

        if (!$tx) {
            Log::warning('Tripay callback: Transaction not found', compact('merchantRef', 'tripayReference'));
            return Response::json(['success' => false, 'message' => 'Transaction not found']);
        }

        // Skip if already processed
        if ($tx->status !== 'UNPAID') {
            return Response::json(['success' => true]);
        }

        if ($callback->isPaid()) {
            $this->handlePaid($tx, $callback);
        } elseif ($callback->isExpired()) {
            DB::update("UPDATE transactions SET status = 'EXPIRED', updated_at = NOW() WHERE id = ?", [$tx->id]);
            Log::info('Tripay callback: Payment expired', ['merchant_ref' => $merchantRef]);
        } elseif ($callback->isFailed()) {
            DB::update("UPDATE transactions SET status = 'FAILED', updated_at = NOW() WHERE id = ?", [$tx->id]);
            Log::info('Tripay callback: Payment failed', ['merchant_ref' => $merchantRef]);
        } elseif ($callback->isRefund()) {
            DB::update("UPDATE transactions SET status = 'REFUND', updated_at = NOW() WHERE id = ?", [$tx->id]);
            Log::info('Tripay callback: Payment refunded', ['merchant_ref' => $merchantRef]);
        } else {
            Log::warning('Tripay callback: Unknown status', ['status' => $callback->getStatus()]);
            return Response::json(['success' => false, 'message' => 'Unknown payment status']);
        }

        return Response::json(['success' => true]);
    }

    /**
     * Handle paid status - raw queries + queued email
     */
    protected function handlePaid(object $tx, $callback): void
    {
        $paidAt = $callback->getPaidAtDateTime() ?? now();

        // Raw update with WHERE status = UNPAID (idempotent)
        $affected = DB::update(
            "UPDATE transactions SET status = 'PAID', paid_at = ?, updated_at = NOW()
             WHERE id = ? AND status = 'UNPAID'",
            [$paidAt, $tx->id]
        );

        // Only proceed if we actually updated (prevents duplicate processing)
        if ($affected === 0) {
            return;
        }


        Log::info('Tripay callback: Payment successful', [
            'transaction_id' => $tx->id,
            'participant_id' => $tx->participant_id,
        ]);

        // Invalidate related caches
        Cache::forget("results:{$tx->event_id}:" . md5(":1"));

        // Queue email (non-blocking, won't fail callback)
        try {
            $transaction = Transaction::with(['participant.event', 'participant.category'])->find($tx->id);
            if ($transaction && $transaction->participant) {
                Mail::to($transaction->participant->email)
                    ->queue(new PaymentConfirmed($transaction->participant, $transaction));
            }
        } catch (\Exception $e) {
            Log::error('Failed to queue payment email: ' . $e->getMessage(), [
                'participant_id' => $tx->participant_id,
            ]);
        }
    }

    /**
     * Handle expired status
     */
    protected function handleExpired(Transaction $transaction): void
    {
        $transaction->update([
            'status' => Transaction::STATUS_EXPIRED,
        ]);

        Log::info('Tripay callback: Payment expired', [
            'merchant_ref' => $transaction->merchant_ref,
        ]);
    }

    /**
     * Handle failed status
     */
    protected function handleFailed(Transaction $transaction): void
    {
        $transaction->update([
            'status' => Transaction::STATUS_FAILED,
        ]);

        Log::info('Tripay callback: Payment failed', [
            'merchant_ref' => $transaction->merchant_ref,
        ]);
    }

    /**
     * Handle refund status
     */
    protected function handleRefund(Transaction $transaction): void
    {
        $transaction->update([
            'status' => Transaction::STATUS_REFUND,
        ]);

        // Revert participant status
        $transaction->participant->update([
            'status' => 'refunded',
        ]);

        Log::info('Tripay callback: Payment refunded', [
            'merchant_ref' => $transaction->merchant_ref,
        ]);
    }
}
