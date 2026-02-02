<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'participant_id',
        'event_id',
        'event_category_id',
        'merchant_ref',
        'tripay_reference',
        'payment_method',
        'payment_name',
        'amount',
        'fee',
        'total_amount',
        'qr_string',
        'qr_url',
        'status',
        'paid_at',
        'expired_at',
        'checkout_url',
        'tripay_response',
    ];

    protected $casts = [
        'amount' => 'integer',
        'fee' => 'integer',
        'total_amount' => 'integer',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'tripay_response' => 'array',
    ];

    // Status Constants
    public const STATUS_UNPAID = 'UNPAID';
    public const STATUS_PAID = 'PAID';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_REFUND = 'REFUND';

    // Relationships
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function eventCategory(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class);
    }

    // Helpers
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isUnpaid(): bool
    {
        return $this->status === self::STATUS_UNPAID;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function canBePaid(): bool
    {
        return $this->isUnpaid() && $this->expired_at->isFuture();
    }

    // Formatted Attributes
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getFormattedFeeAttribute(): string
    {
        return 'Rp ' . number_format($this->fee, 0, ',', '.');
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    // Status Badge
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'green',
            self::STATUS_UNPAID => 'yellow',
            self::STATUS_EXPIRED => 'gray',
            self::STATUS_FAILED => 'red',
            self::STATUS_REFUND => 'blue',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'Lunas',
            self::STATUS_UNPAID => 'Belum Bayar',
            self::STATUS_EXPIRED => 'Kadaluarsa',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_REFUND => 'Refund',
            default => $this->status,
        };
    }

    // Scopes
    public function scopeUnpaid($query)
    {
        return $query->where('status', self::STATUS_UNPAID);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    // Generate Merchant Ref
    public static function generateMerchantRef(): string
    {
        return 'INV-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
