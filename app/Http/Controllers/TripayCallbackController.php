<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\TripayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        // Get callback signature from header
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json = $request->getContent();

        // Validate signature using SDK
        if (!$this->tripayService->validateCallback($callbackSignature, $json)) {
            Log::warning('Tripay callback: Invalid signature', [
                'signature' => $callbackSignature,
            ]);

            return Response::json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 403);
        }

        // Check callback event
        if ($request->server('HTTP_X_CALLBACK_EVENT') !== 'payment_status') {
            return Response::json([
                'success' => false,
                'message' => 'Unrecognized callback event',
            ]);
        }

        // Parse callback data using SDK
        try {
            $callback = $this->tripayService->parseCallback($json);
        } catch (\Exception $e) {
            Log::error('Tripay callback: Invalid JSON', ['error' => $e->getMessage()]);

            return Response::json([
                'success' => false,
                'message' => 'Invalid callback data',
            ]);
        }

        $merchantRef = $callback->getMerchantRef();
        $tripayReference = $callback->getReference();

        // Find transaction
        $transaction = Transaction::where('merchant_ref', $merchantRef)
            ->where('tripay_reference', $tripayReference)
            ->first();

        if (!$transaction) {
            Log::warning('Tripay callback: Transaction not found', [
                'merchant_ref' => $merchantRef,
                'tripay_reference' => $tripayReference,
            ]);

            return Response::json([
                'success' => false,
                'message' => 'Transaction not found: ' . $merchantRef,
            ]);
        }

        // Skip if already processed
        if ($transaction->status !== Transaction::STATUS_UNPAID) {
            Log::info('Tripay callback: Transaction already processed', [
                'merchant_ref' => $merchantRef,
                'status' => $transaction->status,
            ]);

            return Response::json(['success' => true]);
        }

        // Process based on status
        if ($callback->isPaid()) {
            $this->handlePaid($transaction, $callback);
        } elseif ($callback->isExpired()) {
            $this->handleExpired($transaction);
        } elseif ($callback->isFailed()) {
            $this->handleFailed($transaction);
        } elseif ($callback->isRefund()) {
            $this->handleRefund($transaction);
        } else {
            Log::warning('Tripay callback: Unknown status', [
                'merchant_ref' => $merchantRef,
                'status' => $callback->getStatus(),
            ]);

            return Response::json([
                'success' => false,
                'message' => 'Unknown payment status',
            ]);
        }

        return Response::json(['success' => true]);
    }

    /**
     * Handle paid status
     */
    protected function handlePaid(Transaction $transaction, $callback): void
    {
        $transaction->update([
            'status' => Transaction::STATUS_PAID,
            'paid_at' => $callback->getPaidAtDateTime() ?? now(),
        ]);

        // Update participant status to confirmed
        $transaction->participant->update([
            'status' => 'confirmed',
        ]);

        Log::info('Tripay callback: Payment successful', [
            'merchant_ref' => $transaction->merchant_ref,
            'participant_id' => $transaction->participant_id,
        ]);

        // TODO: Send email notification
        // TODO: Send WhatsApp notification
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
