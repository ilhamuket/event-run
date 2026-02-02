<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Transaction;
use App\Services\TripayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    public function __construct(
        protected TripayService $tripayService
    ) {}

    /**
     * Show registration form
     */
    public function create(string $slug)
    {
        $event = Event::where('slug', $slug)
            ->with(['categories' => fn($q) => $q->where('is_active', true)])
            ->firstOrFail();

        // Calculate fee for each category
        $categories = $event->categories->map(function ($category) {
            $calculation = $this->tripayService->calculateTotal($category->price);
            $category->fee = $calculation['fee'];
            $category->total = $calculation['total'];
            return $category;
        });

        return view('event.register', compact('event', 'categories'));
    }

    /**
     * Store registration and create payment
     */
    public function store(Request $request, string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

       $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bib_name' => 'required|string|max:12',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:M,F',
            'age' => 'required|integer|min:5|max:100',
            'jersey_size' => 'required|in:S,M,L,XL,XXL',
            'city' => 'required|string|max:100',
            'community' => 'nullable|string|max:255',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'event_category_id' => 'required|exists:event_categories,id',
            'agreement' => 'accepted',
        ]);


        try {
            $transaction = DB::transaction(function () use ($validated, $event) {
                // Generate BIB number
                $lastBib = Participant::where('event_id', $event->id)
                    ->lockForUpdate()
                    ->max('bib');
                $bib = $lastBib ? $lastBib + 1 : 1001;

                // Create participant
                $participant = Participant::create([
                    'event_id' => $event->id,
                    'event_category_id' => $validated['event_category_id'],
                    'bib' => $bib,
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'gender' => $validated['gender'],
                    'jersey_size' => $validated['jersey_size'],
                    'city' => $validated['city'] ?? null,
                    'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                    'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                    'community' => $validated['community'] ?? null,
                    'status' => 'pending',
                ]);

                // Create QRIS payment
                $transaction = $this->tripayService->createQrisPayment($participant);

                return $transaction;
            });

            // Redirect to payment page
            return redirect()->route('event.payment.show', [
                'event' => $event->slug,
                'ref' => $transaction->merchant_ref,
            ]);

        } catch (\Exception $e) {
            Log::error('Registration Error: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'data' => $validated,
                'stack' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memproses pendaftaran. Silakan coba lagi.');
        }
    }

    /**
     * Show payment page with QR code
     */
    public function showPayment(string $slug, string $ref)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $transaction = Transaction::where('merchant_ref', $ref)
            ->where('event_id', $event->id)
            ->with(['participant', 'eventCategory'])
            ->firstOrFail();

        return view('event.payment', compact('event', 'transaction'));
    }

    /**
     * Check payment status (AJAX)
     */
    public function checkPaymentStatus(string $slug, string $ref)
    {
        $transaction = Transaction::where('merchant_ref', $ref)->firstOrFail();

        // If still unpaid, check from Tripay
        if ($transaction->isUnpaid() && $transaction->tripay_reference) {
            try {
                $status = $this->tripayService->checkStatus($transaction->tripay_reference);

                if ($status === 'PAID' && $transaction->status !== Transaction::STATUS_PAID) {
                    $transaction->update([
                        'status' => Transaction::STATUS_PAID,
                        'paid_at' => now(),
                    ]);

                    // Update participant status
                    $transaction->participant->update(['status' => 'confirmed']);
                }
            } catch (\Exception $e) {
                Log::error('Check status error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'status' => $transaction->fresh()->status,
            'is_paid' => $transaction->fresh()->isPaid(),
        ]);
    }

    /**
     * Show payment success page
     */
    public function paymentSuccess(string $slug, string $ref)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $transaction = Transaction::where('merchant_ref', $ref)
            ->where('event_id', $event->id)
            ->where('status', Transaction::STATUS_PAID)
            ->with(['participant', 'eventCategory'])
            ->firstOrFail();

        return view('event.payment-success', compact('event', 'transaction'));
    }
}
