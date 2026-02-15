<?php

namespace App\Http\Controllers;

use App\Mail\PaymentConfirmed;
use App\Mail\RegistrationReceived;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Transaction;
use App\Services\TripayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class RegistrationController extends Controller
{
    public function __construct(
        protected TripayService $tripayService
    ) {}

    /**
     * Show registration form - cached categories + fees
     */
    public function create(string $slug)
    {
        $event = Event::where('slug', $slug)
            ->with(['categories' => fn($q) => $q->where('is_active', true)])
            ->firstOrFail();

        // Cache fee calculations (rarely change)
        $categories = Cache::remember("reg_cats:{$event->id}", 300, function () use ($event) {
            return $event->categories->map(function ($category) {
                $calculation = $this->tripayService->calculateTotal($category->price);
                $category->fee = $calculation['fee'];
                $category->total = $calculation['total'];
                return $category;
            });
        });

        return view('event.register', compact('event', 'categories'));
    }

    /**
     * Store registration - race-condition safe with rate limiting
     */
    public function store(Request $request, string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        // ── Rate limiting: max 3 attempts per IP per minute ──
        $rateLimitKey = 'register:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()
                ->withInput()
                ->with('error', "Terlalu banyak percobaan. Silakan coba lagi dalam {$seconds} detik.");
        }
        RateLimiter::hit($rateLimitKey, 60);

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

        // ── Duplicate check: same email + event already paid ──
        $existingPaid = DB::selectOne("
            SELECT p.id FROM participants p
            INNER JOIN transactions t ON t.participant_id = p.id AND t.status = 'PAID'
            WHERE p.event_id = ? AND p.email = ?
            LIMIT 1
        ", [$event->id, $validated['email']]);

        if ($existingPaid) {
            return back()
                ->withInput()
                ->with('error', 'Email ini sudah terdaftar dan sudah membayar untuk event ini.');
        }

        try {
            $transaction = DB::transaction(function () use ($validated, $event) {

                // ── BIB generation with MySQL advisory lock (prevents race condition) ──
                $lockName = "bib_lock_{$event->id}";
                $lockAcquired = DB::selectOne("SELECT GET_LOCK(?, 5) as acquired", [$lockName]);

                if (!$lockAcquired || !$lockAcquired->acquired) {
                    throw new \RuntimeException('Server sibuk, silakan coba lagi.');
                }

                try {
                    // Raw query for max BIB - faster than Eloquent
                    $lastBib = DB::selectOne(
                        "SELECT MAX(bib) as max_bib FROM participants WHERE event_id = ?",
                        [$event->id]
                    );
                    $bib = $lastBib && $lastBib->max_bib ? $lastBib->max_bib + 1 : 1001;

                    // Raw insert for speed
                    $participantId = DB::table('participants')->insertGetId([
                        'event_id' => $event->id,
                        'event_category_id' => $validated['event_category_id'],
                        'bib' => $bib,
                        'name' => $validated['name'],
                        'bib_name' => $validated['bib_name'],
                        'email' => $validated['email'],
                        'phone' => $validated['phone'],
                        'gender' => $validated['gender'],
                        'age' => $validated['age'],
                        'jersey_size' => $validated['jersey_size'],
                        'city' => $validated['city'] ?? null,
                        'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                        'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                        'community' => $validated['community'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } finally {
                    // Always release the advisory lock
                    DB::select("SELECT RELEASE_LOCK(?)", [$lockName]);
                }

                // Load participant model for Tripay (needs relationships)
                $participant = Participant::find($participantId);

                // Create QRIS payment
                return $this->tripayService->createQrisPayment($participant);
            });

            // ── Send email OUTSIDE transaction (non-blocking) ──
            try {
                $participant = $transaction->participant->load(['event', 'category']);
                Mail::to($participant->email)->queue(new RegistrationReceived($participant, $transaction));
            } catch (\Exception $e) {
                Log::warning('Failed to queue registration email', [
                    'participant_id' => $transaction->participant_id,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the registration if email fails
            }

            return redirect()->route('event.payment.show', [
                'event' => $event->slug,
                'ref' => $transaction->merchant_ref,
            ]);

        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Registration Error: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'email' => $validated['email'] ?? null,
                'stack' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memproses pendaftaran. Silakan coba lagi.');
        }
    }

    /**
     * Show payment page
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
     * Check payment status (AJAX) - lightweight, rate-limited
     */
    public function checkPaymentStatus(string $slug, string $ref)
    {
        // Rate limit polling: max 6 per minute per ref
        $rateLimitKey = "payment_check:{$ref}";
        if (RateLimiter::tooManyAttempts($rateLimitKey, 6)) {
            return response()->json([
                'status' => 'RATE_LIMITED',
                'is_paid' => false,
                'retry_after' => RateLimiter::availableIn($rateLimitKey),
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 60);

        // Quick raw check first - avoid loading full model
        $tx = DB::selectOne(
            "SELECT id, status, tripay_reference, participant_id, event_id
             FROM transactions WHERE merchant_ref = ? LIMIT 1",
            [$ref]
        );

        if (!$tx) {
            return response()->json(['status' => 'NOT_FOUND', 'is_paid' => false], 404);
        }

        // Already paid? Return immediately (no Tripay call)
        if ($tx->status === 'PAID') {
            return response()->json(['status' => 'PAID', 'is_paid' => true]);
        }

        // Only check Tripay if unpaid and has reference
        if ($tx->status === 'UNPAID' && $tx->tripay_reference) {
            try {
                $status = $this->tripayService->checkStatus($tx->tripay_reference);

                if ($status === 'PAID') {
                    // Raw updates - faster
                    DB::update(
                        "UPDATE transactions SET status = 'PAID', paid_at = NOW(), updated_at = NOW()
                         WHERE id = ? AND status = 'UNPAID'",
                        [$tx->id]
                    );


                    // Invalidate related caches
                    Cache::forget("results:{$tx->event_id}:" . md5(":1"));

                    // Queue payment confirmed email
                    try {
                        $transaction = Transaction::with(['participant.event', 'participant.category'])->find($tx->id);
                        if ($transaction && $transaction->participant) {
                            Mail::to($transaction->participant->email)
                                ->queue(new PaymentConfirmed($transaction->participant, $transaction));
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to queue payment email', ['error' => $e->getMessage()]);
                    }

                    return response()->json(['status' => 'PAID', 'is_paid' => true]);
                }
            } catch (\Exception $e) {
                Log::error('Check status error: ' . $e->getMessage());
            }
        }

        return response()->json(['status' => $tx->status, 'is_paid' => false]);
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
