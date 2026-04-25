<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RfidRawLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'rfid_checkpoint_id',
        'rfid_tag',
        'bib',
        'scanned_at',
        'reader_id',
        'signal_strength',
        'is_valid',
        'notes',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'signal_strength' => 'integer',
        'is_valid' => 'boolean',
    ];

    /**
     * Get the event that owns the raw log.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the checkpoint where this scan occurred.
     */
    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(RfidCheckpoint::class, 'rfid_checkpoint_id');
    }

    /**
     * Get the validated time that was created from this raw log.
     */
    public function validatedTime(): HasOne
    {
        return $this->hasOne(RfidValidatedTime::class, 'rfid_raw_log_id');
    }

    /**
     * Scope untuk filter hanya scan valid
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', true);
    }

    /**
     * Scope untuk filter berdasarkan RFID tag
     */
    public function scopeByRfidTag($query, string $tag)
    {
        return $query->where('rfid_tag', $tag);
    }

    /**
     * Scope untuk filter berdasarkan BIB
     */
    public function scopeByBib($query, string $bib)
    {
        return $query->where('bib', $bib);
    }

    /**
     * Scope untuk filter yang belum divalidasi
     */
    public function scopeUnvalidated($query)
    {
        return $query->whereDoesntHave('validatedTime');
    }

    /**
     * Scope untuk order berdasarkan waktu scan
     */
    public function scopeOrderByScannedAt($query, $direction = 'asc')
    {
        return $query->orderBy('scanned_at', $direction);
    }

    /**
     * Get the participant through RFID mapping.
     * Menggunakan accessor karena join via rfid_tag (string) tidak didukung hasOneThrough.
     */
    public function getParticipantAttribute(): ?Participant
    {
        return ParticipantRfidMapping::findParticipantByRfid($this->rfid_tag);
    }

    /**
     * Alternatively, eager-loadable via mapping:
     */
    public function rfidMapping(): HasOne
    {
        return $this->hasOne(ParticipantRfidMapping::class, 'rfid_tag', 'rfid_tag')
            ->where('is_active', true);
    }
}
