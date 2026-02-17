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
use App\Models\EventCategory;
use App\Models\EventCoupon;

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

        // ── Rate limiting ──
        $rateLimitKey = 'register:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()->withInput()->with('error', "Terlalu banyak percobaan. Silakan coba lagi dalam {$seconds} detik.");
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
            'coupon_code' => 'nullable|string|max:50',
            'agreement' => 'accepted',
        ]);

        // ── Age validation ──
        $category = EventCategory::findOrFail($validated['event_category_id']);
        if ($category->min_age && $category->max_age) {
            if ($validated['age'] < $category->min_age || $validated['age'] > $category->max_age) {
                return back()->withInput()
                    ->withErrors(['age' => "Umur harus antara {$category->min_age} – {$category->max_age} tahun untuk kategori {$category->name}."]);
            }
        }

        // ── Duplicate check ──
        $existingPaid = DB::selectOne("
            SELECT p.id FROM participants p
            INNER JOIN transactions t ON t.participant_id = p.id AND t.status = 'PAID'
            WHERE p.event_id = ? AND p.email = ?
            LIMIT 1
        ", [$event->id, $validated['email']]);

        if ($existingPaid) {
            return back()->withInput()->with('error', 'Email ini sudah terdaftar dan sudah membayar untuk event ini.');
        }

        // ── Quota check ──
        if ($event->max_participants) {
            $quotaLock = "quota_lock_{$event->id}";
            $lockAcquired = DB::selectOne("SELECT GET_LOCK(?, 5) as acquired", [$quotaLock]);
            if (!$lockAcquired || !$lockAcquired->acquired) {
                return back()->withInput()->with('error', 'Server sibuk, silakan coba lagi.');
            }
            try {
                if ($event->isQuotaFull()) {
                    return back()->withInput()->with('error', 'Maaf, kuota peserta sudah penuh.');
                }
            } finally {
                DB::select("SELECT RELEASE_LOCK(?)", [$quotaLock]);
            }
        }

        // ── Validate & claim coupon (with atomic check) ──
        $coupon = null;
        $couponCode = strtoupper(trim($validated['coupon_code'] ?? ''));

        if ($couponCode !== '') {
            $coupon = EventCoupon::where('event_id', $event->id)
                ->where('code', $couponCode)
                ->where('is_active', true)
                ->first();

            if (!$coupon) {
                return back()->withInput()->withErrors(['coupon_code' => 'Kode kupon tidak ditemukan.']);
            }

            // Atomic claim — increment used_count only if still under max
            if (!$coupon->claimUsage()) {
                return back()->withInput()->withErrors(['coupon_code' => 'Kuota kupon ini sudah habis.']);
            }
        }

        try {
            $transaction = DB::transaction(function () use ($validated, $event, $coupon) {

                // ── BIB generation with lock ──
                $lockName = "bib_lock_{$event->id}";
                $lockAcquired = DB::selectOne("SELECT GET_LOCK(?, 5) as acquired", [$lockName]);
                if (!$lockAcquired || !$lockAcquired->acquired) {
                    throw new \RuntimeException('Server sibuk, silakan coba lagi.');
                }

                try {
                    $lastBib = DB::selectOne("SELECT MAX(bib) as max_bib FROM participants WHERE event_id = ?", [$event->id]);
                    $bib = $lastBib && $lastBib->max_bib ? $lastBib->max_bib + 1 : 1001;

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
                    DB::select("SELECT RELEASE_LOCK(?)", [$lockName]);
                }

                $participant = Participant::find($participantId);

                return $this->tripayService->createQrisPayment($participant, $coupon);
            });

            // ── Send email ──
            try {
                $participant = $transaction->participant->load(['event', 'category']);
                Mail::to($participant->email)->queue(new RegistrationReceived($participant, $transaction));
            } catch (\Exception $e) {
                Log::warning('Failed to queue registration email', [
                    'participant_id' => $transaction->participant_id,
                    'error' => $e->getMessage(),
                ]);
            }

            return redirect()->route('event.payment.show', [
                'event' => $event->slug,
                'ref' => $transaction->merchant_ref,
            ]);

        } catch (\RuntimeException $e) {
            // Release coupon if registration failed
            $coupon?->releaseUsage();
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            // Release coupon if registration failed
            $coupon?->releaseUsage();
            Log::error('Registration Error: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'email' => $validated['email'] ?? null,
                'stack' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memproses pendaftaran. Silakan coba lagi.');
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
                    // ── STRICT QUOTA CHECK before marking paid ──
                    $event = Event::find($tx->event_id);

                    if ($event && $event->max_participants) {
                        $quotaLock = "quota_lock_{$event->id}";
                        $lockAcquired = DB::selectOne("SELECT GET_LOCK(?, 5) as acquired", [$quotaLock]);

                        if (!$lockAcquired || !$lockAcquired->acquired) {
                            return response()->json(['status' => 'UNPAID', 'is_paid' => false]);
                        }

                        try {
                            $paidCount = $event->paidParticipantsCount();

                            if ($paidCount >= $event->max_participants) {
                                // Quota full — mark as FAILED, don't mark as PAID
                                DB::update(
                                    "UPDATE transactions SET status = 'FAILED', note = 'Kuota penuh saat pembayaran diterima', updated_at = NOW()
                                    WHERE id = ? AND status = 'UNPAID'",
                                    [$tx->id]
                                );

                                // Release coupon
                                $couponId = DB::selectOne("SELECT event_coupon_id FROM transactions WHERE id = ?", [$tx->id]);
                                if ($couponId && $couponId->event_coupon_id) {
                                   EventCoupon::find($couponId->event_coupon_id)?->releaseUsage();
                                }

                                Log::warning('Payment rejected: quota full', [
                                    'transaction_id' => $tx->id,
                                    'event_id' => $tx->event_id,
                                    'paid_count' => $paidCount,
                                    'max' => $event->max_participants,
                                ]);

                                return response()->json([
                                    'status' => 'FAILED',
                                    'is_paid' => false,
                                    'message' => 'Kuota peserta sudah penuh. Pembayaran Anda akan direfund.',
                                ]);
                            }

                            // Quota OK — mark as PAID
                            DB::update(
                                "UPDATE transactions SET status = 'PAID', paid_at = NOW(), updated_at = NOW()
                                WHERE id = ? AND status = 'UNPAID'",
                                [$tx->id]
                            );
                        } finally {
                            DB::select("SELECT RELEASE_LOCK(?)", [$quotaLock]);
                        }
                    } else {
                        // No quota limit — mark as PAID directly
                        DB::update(
                            "UPDATE transactions SET status = 'PAID', paid_at = NOW(), updated_at = NOW()
                            WHERE id = ? AND status = 'UNPAID'",
                            [$tx->id]
                        );
                    }

                    // Invalidate cache & send email (keep existing code)
                    Cache::forget("results:{$tx->event_id}:" . md5(":1"));

                    try {
                        $transaction = Transaction::with(['participant.event', 'participant.category'])->find($tx->id);
                        if ($transaction && $transaction->status === 'PAID' && $transaction->participant) {
                            Mail::to($transaction->participant->email)
                                ->queue(new PaymentConfirmed($transaction->participant, $transaction));
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to queue payment email', ['error' => $e->getMessage()]);
                    }

                    $txFresh = DB::selectOne("SELECT status FROM transactions WHERE id = ?", [$tx->id]);
                    return response()->json([
                        'status' => $txFresh->status,
                        'is_paid' => $txFresh->status === 'PAID',
                    ]);
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

    /**
     * Validate coupon code (AJAX)
     */
    public function validateCoupon(Request $request, string $slug)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'event_category_id' => 'required|exists:event_categories,id',
        ]);

        $event = Event::where('slug', $slug)->firstOrFail();

        $coupon = EventCoupon::where('event_id', $event->id)
            ->where('code', strtoupper(trim($request->code)))
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode kupon tidak ditemukan.',
            ]);
        }

        if (!$coupon->isAvailable()) {
            return response()->json([
                'valid' => false,
                'message' => 'Kuota kupon ini sudah habis.',
            ]);
        }

        $category = EventCategory::findOrFail($request->event_category_id);
        $basePrice = (int) $category->price;
        $discount = $coupon->calculateDiscount($basePrice);
        $discountedPrice = $basePrice - $discount;
        $calculation = $this->tripayService->calculateTotal($discountedPrice);

        return response()->json([
            'valid' => true,
            'message' => "Diskon {$coupon->discount_percent}% berhasil diterapkan!",
            'discount_percent' => $coupon->discount_percent,
            'discount_amount' => $discount,
            'original_price' => $basePrice,
            'discounted_price' => $discountedPrice,
            'fee' => $calculation['fee'],
            'total' => $calculation['total'],
        ]);
    }
}
