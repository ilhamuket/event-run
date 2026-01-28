<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfidValidatedTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'rfid_checkpoint_id',
        'rfid_raw_log_id',
        'checkpoint_time',
        'elapsed_time',
        'split_time',
        'position_at_checkpoint',
        'validation_status',
        'validation_notes',
        'validated_by',
    ];

    protected $casts = [
        'checkpoint_time' => 'datetime',
        'elapsed_time' => 'datetime:H:i:s',
        'split_time' => 'datetime:H:i:s',
        'position_at_checkpoint' => 'integer',
    ];

    /**
     * Get the participant that owns the validated time.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * Get the checkpoint for this validated time.
     */
    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(RfidCheckpoint::class, 'rfid_checkpoint_id');
    }

    /**
     * Get the raw log that was used for this validation.
     */
    public function rawLog(): BelongsTo
    {
        return $this->belongsTo(RfidRawLog::class, 'rfid_raw_log_id');
    }

    /**
     * Get the user who validated this time (admin).
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Scope untuk filter berdasarkan status validasi
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('validation_status', $status);
    }

    /**
     * Scope untuk filter auto-validated times
     */
    public function scopeAutoValidated($query)
    {
        return $query->where('validation_status', 'auto');
    }

    /**
     * Scope untuk filter manually validated times
     */
    public function scopeManuallyValidated($query)
    {
        return $query->where('validation_status', 'manual');
    }

    /**
     * Scope untuk filter corrected times
     */
    public function scopeCorrected($query)
    {
        return $query->where('validation_status', 'corrected');
    }

    /**
     * Scope untuk order berdasarkan elapsed time (fastest first)
     */
    public function scopeFastest($query)
    {
        return $query->orderBy('elapsed_time');
    }

    /**
     * Scope untuk order berdasarkan checkpoint time
     */
    public function scopeOrderByCheckpointTime($query, $direction = 'asc')
    {
        return $query->orderBy('checkpoint_time', $direction);
    }

    /**
     * Check if this is auto-validated
     */
    public function isAutoValidated(): bool
    {
        return $this->validation_status === 'auto';
    }

    /**
     * Check if this is manually validated
     */
    public function isManuallyValidated(): bool
    {
        return $this->validation_status === 'manual';
    }

    /**
     * Check if this is corrected
     */
    public function isCorrected(): bool
    {
        return $this->validation_status === 'corrected';
    }

    /**
     * Get formatted elapsed time (HH:MM:SS)
     */
    public function getFormattedElapsedTimeAttribute(): ?string
    {
        return $this->elapsed_time ? $this->elapsed_time->format('H:i:s') : null;
    }

    /**
     * Get formatted split time (HH:MM:SS)
     */
    public function getFormattedSplitTimeAttribute(): ?string
    {
        return $this->split_time ? $this->split_time->format('H:i:s') : null;
    }
}
