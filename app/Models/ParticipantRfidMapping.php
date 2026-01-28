<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantRfidMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'rfid_tag',
        'assigned_at',
        'assigned_by',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the participant that owns the RFID mapping.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * Get the user who assigned this RFID (admin).
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope untuk filter hanya mapping aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter berdasarkan RFID tag
     */
    public function scopeByRfidTag($query, string $tag)
    {
        return $query->where('rfid_tag', $tag);
    }

    /**
     * Find participant by RFID tag
     */
    public static function findParticipantByRfid(string $rfidTag): ?Participant
    {
        $mapping = static::where('rfid_tag', $rfidTag)
            ->where('is_active', true)
            ->first();

        return $mapping ? $mapping->participant : null;
    }
}
