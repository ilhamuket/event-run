<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'slug',
        'distance',
        'level',
        'elevation',
        'terrain',
        'cut_off_time',
        'course_map_image',
        'description',
        'price',
        'color_from',
        'color_to',
        'order',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the event that owns the category.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get all RFID checkpoints for this category. (NEW)
     */
    public function rfidCheckpoints(): HasMany
    {
        return $this->hasMany(RfidCheckpoint::class)->orderBy('checkpoint_order');
    }

    /**
     * Get all active RFID checkpoints for this category. (NEW)
     */
    public function activeRfidCheckpoints(): HasMany
    {
        return $this->rfidCheckpoints()->where('is_active', true);
    }

    /**
     * Get start checkpoint. (NEW)
     */
    public function startCheckpoint()
    {
        return $this->rfidCheckpoints()
            ->where('checkpoint_type', 'start')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get finish checkpoint. (NEW)
     */
    public function finishCheckpoint()
    {
        return $this->rfidCheckpoints()
            ->where('checkpoint_type', 'finish')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all intermediate checkpoints (not start/finish). (NEW)
     */
    public function intermediateCheckpoints(): HasMany
    {
        return $this->rfidCheckpoints()
            ->where('checkpoint_type', 'checkpoint')
            ->where('is_active', true);
    }

    /**
     * Scope untuk filter hanya kategori aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk order berdasarkan order column
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get gradient color CSS
     */
    public function getGradientAttribute(): string
    {
        return "from-{$this->color_from} to-{$this->color_to}";
    }

    /**
     * Get formatted price with Rupiah currency
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
}
