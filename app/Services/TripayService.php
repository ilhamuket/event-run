<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Transaction;
use Ilhamuket\Tripay\Facades\Tripay;
use Ilhamuket\Tripay\Data\TransactionData;
use Ilhamuket\Tripay\Data\OrderItem;
use Ilhamuket\Tripay\PaymentMethod;
use Ilhamuket\Tripay\Exceptions\TripayApiException;
use Illuminate\Support\Facades\Log;

class TripayService
{
    /**
     * Create QRIS payment for participant
     */
    public function createQrisPayment(Participant $participant): Transaction
    {
        $event = $participant->event;
        $category = $participant->category;

        // Calculate fee (QRIS: flat 750 + 0.7%)
        $amount = $category->price;
        $fee = $this->calculateFee($amount);
        $totalAmount = $amount + $fee;

        // Generate merchant ref
        $merchantRef = Transaction::generateMerchantRef();

        // Prepare transaction data using SDK
        $transactionData = new TransactionData();
        $transactionData
            ->setMethod(PaymentMethod::QRISC)
            ->setMerchantRef($merchantRef)
            ->setAmount($totalAmount)
            ->setCustomerName($participant->name)
            ->setCustomerEmail($participant->email)
            ->setCustomerPhone($participant->phone)
            ->addOrderItem([
                'sku' => 'EVT-' . $event->id . '-' . $category->id,
                'name' => $event->name . ' - ' . $category->name,
                'price' => $totalAmount,
                'quantity' => 1,
            ])
            ->setCallbackUrl(config('tripay.callback_url'))
            ->setReturnUrl(route('event.payment.show', [
                'event' => $event->slug,
                'ref'   => $merchantRef
            ]))
            ->setExpiryHours(config('tripay.expiry_hours', 24));

        try {
            // Create transaction via SDK
            $response = Tripay::createTransaction($transactionData);

            // Save to database
            $transaction = Transaction::create([
                'participant_id' => $participant->id,
                'event_id' => $event->id,
                'event_category_id' => $category->id,
                'merchant_ref' => $merchantRef,
                'tripay_reference' => $response->getReference(),
                'payment_method' => $response->getPaymentMethod(),
                'payment_name' => $response->getPaymentName(),
                'amount' => $amount,
                'fee' => $fee,
                'total_amount' => $totalAmount,
                'qr_string' => $response->getQrString(),
                'qr_url' => $response->getQrUrl(),
                'status' => Transaction::STATUS_UNPAID,
                'expired_at' => $response->getExpiredAt(),
                'checkout_url' => $response->getCheckoutUrl(),
                'tripay_response' => $response->toArray(),
            ]);

            return $transaction;

        } catch (TripayApiException $e) {
            Log::error('Tripay API Error: ' . $e->getMessage(), [
                'participant_id' => $participant->id,
                'merchant_ref' => $merchantRef,
            ]);
            throw $e;
        }
    }

    /**
     * Get transaction detail from Tripay
     */
    public function getTransactionDetail(string $reference): array
    {
        try {
            $response = Tripay::getTransactionDetail($reference);
            return $response->toArray();
        } catch (TripayApiException $e) {
            Log::error('Tripay Get Detail Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check transaction status
     */
    public function checkStatus(string $reference): string
    {
        try {
            $response = Tripay::getTransactionDetail($reference);
            return $response->getStatus();
        } catch (TripayApiException $e) {
            Log::error('Tripay Check Status Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate callback signature
     */
    public function validateCallback(string $signature, string $jsonBody): bool
    {
        return Tripay::validateCallback($signature, $jsonBody);
    }

    /**
     * Parse callback data
     */
    public function parseCallback(string $jsonBody)
    {
        return Tripay::parseCallback($jsonBody);
    }

    /**
     * Get available payment channels
     */
    public function getPaymentChannels()
    {
        return Tripay::getPaymentChannels();
    }

    /**
     * Calculate QRIS fee
     * QRIS fee: flat 750 + 0.7%
     */
    public function calculateFee(int $amount): int
    {
        $feeFlat = 750;
        $feePercent = 0.7;

        return $feeFlat + (int) ceil($amount * $feePercent / 100);
    }

    /**
     * Calculate total with fee
     */
    public function calculateTotal(int $amount): array
    {
        $fee = $this->calculateFee($amount);

        return [
            'amount' => $amount,
            'fee' => $fee,
            'total' => $amount + $fee,
        ];
    }
}
