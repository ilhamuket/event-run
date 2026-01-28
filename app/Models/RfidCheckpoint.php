<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RfidCheckpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_category_id',
        'checkpoint_type',
        'checkpoint_name',
        'checkpoint_order',
        'distance_km',
        'location_name',
        'latitude',
        'longitude',
        'cutoff_time',
        'is_active',
    ];

    protected $casts = [
        'distance_km' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'cutoff_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    /**
     * Get the event category that owns the checkpoint.
     */
    public function eventCategory(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class);
    }

    /**
     * Get all raw logs for this checkpoint.
     */
    public function rawLogs(): HasMany
    {
        return $this->hasMany(RfidRawLog::class);
    }

    /**
     * Get all validated times for this checkpoint.
     */
    public function validatedTimes(): HasMany
    {
        return $this->hasMany(RfidValidatedTime::class);
    }

    /**
     * Scope untuk filter hanya checkpoint aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk order berdasarkan checkpoint_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('checkpoint_order');
    }

    /**
     * Scope untuk filter berdasarkan tipe
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('checkpoint_type', $type);
    }

    /**
     * Check if this is a start checkpoint
     */
    public function isStart(): bool
    {
        return $this->checkpoint_type === 'start';
    }

    /**
     * Check if this is a finish checkpoint
     */
    public function isFinish(): bool
    {
        return $this->checkpoint_type === 'finish';
    }

    /**
     * Check if this is a regular checkpoint
     */
    public function isCheckpoint(): bool
    {
        return $this->checkpoint_type === 'checkpoint';
    }
}
