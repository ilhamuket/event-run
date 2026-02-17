<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class EventCoupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'code',
        'description',
        'discount_percent',
        'max_usage',
        'used_count',
        'is_active',
    ];

    protected $casts = [
        'discount_percent' => 'integer',
        'max_usage' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Check if coupon can still be used
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->used_count < $this->max_usage;
    }

    /**
     * Remaining usage
     */
    public function remainingUsage(): int
    {
        return max(0, $this->max_usage - $this->used_count);
    }

    /**
     * Calculate discount amount from a base price
     */
    public function calculateDiscount(int $amount): int
    {
        return (int) floor($amount * $this->discount_percent / 100);
    }

    /**
     * Atomically increment used_count with lock (returns true if successful)
     */
    public function claimUsage(): bool
    {
        $affected = DB::update(
            "UPDATE event_coupons SET used_count = used_count + 1, updated_at = NOW()
             WHERE id = ? AND is_active = 1 AND used_count < max_usage",
            [$this->id]
        );

        return $affected > 0;
    }

    /**
     * Atomically decrement used_count (for failed/expired transactions)
     */
    public function releaseUsage(): void
    {
        DB::update(
            "UPDATE event_coupons SET used_count = GREATEST(used_count - 1, 0), updated_at = NOW()
             WHERE id = ?",
            [$this->id]
        );
    }
}
