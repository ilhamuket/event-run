<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'type',
        'name',
        'value',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the event that owns the contact.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Scope untuk filter hanya kontak aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter berdasarkan tipe
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
