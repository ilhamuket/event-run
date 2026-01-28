<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'bib',
        'bib_name',
        'name',
        'gender',
        'age',
        'email',
        'phone',
        'address',
        'country',
        'province',
        'regency',
        'category',
        'city',
        'jersey_size',
        'community',
        'emergency_contact_name',
        'emergency_contact_phone',
        'has_comorbid',
        'comorbid_details',
        'elapsed_time',
        'general_position',
        'category_position',
    ];

    protected $casts = [
        'age' => 'integer',
        'has_comorbid' => 'boolean',
        'elapsed_time' => 'datetime:H:i:s',
        'general_position' => 'integer',
        'category_position' => 'integer',
    ];

    /**
     * Get the event that owns the participant.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get RFID mapping for this participant. (NEW)
     */
    public function rfidMapping(): HasOne
    {
        return $this->hasOne(ParticipantRfidMapping::class);
    }

    /**
     * Get active RFID mapping for this participant. (NEW)
     */
    public function activeRfidMapping(): HasOne
    {
        return $this->rfidMapping()->where('is_active', true);
    }

    /**
     * Get all validated times for this participant. (NEW)
     */
    public function validatedTimes(): HasMany
    {
        return $this->hasMany(RfidValidatedTime::class);
    }

    /**
     * Get validated times ordered by checkpoint. (NEW)
     */
    public function orderedValidatedTimes(): HasMany
    {
        return $this->validatedTimes()
            ->join('rfid_checkpoints', 'rfid_validated_times.rfid_checkpoint_id', '=', 'rfid_checkpoints.id')
            ->orderBy('rfid_checkpoints.checkpoint_order')
            ->select('rfid_validated_times.*');
    }

    /**
     * Get start time for this participant. (NEW)
     */
    public function startTime()
    {
        return $this->validatedTimes()
            ->whereHas('checkpoint', function ($query) {
                $query->where('checkpoint_type', 'start');
            })
            ->first();
    }

    /**
     * Get finish time for this participant. (NEW)
     */
    public function finishTime()
    {
        return $this->validatedTimes()
            ->whereHas('checkpoint', function ($query) {
                $query->where('checkpoint_type', 'finish');
            })
            ->first();
    }

    /**
     * Check if participant has finished the race. (NEW)
     */
    public function hasFinished(): bool
    {
        return $this->finishTime() !== null;
    }

    /**
     * Check if participant has started the race. (NEW)
     */
    public function hasStarted(): bool
    {
        return $this->startTime() !== null;
    }

    /**
     * Get participant's RFID tag. (NEW)
     */
    public function getRfidTagAttribute(): ?string
    {
        return $this->activeRfidMapping?->rfid_tag;
    }

    /**
     * Scope untuk filter berdasarkan gender
     */
    public function scopeMale($query)
    {
        return $query->where('gender', 'M');
    }

    /**
     * Scope untuk filter berdasarkan gender
     */
    public function scopeFemale($query)
    {
        return $query->where('gender', 'F');
    }

    /**
     * Scope untuk filter berdasarkan kategori
     */
    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope untuk filter peserta yang sudah finish
     */
    public function scopeFinished($query)
    {
        return $query->whereNotNull('elapsed_time');
    }

    /**
     * Scope untuk filter peserta yang belum finish
     */
    public function scopeNotFinished($query)
    {
        return $query->whereNull('elapsed_time');
    }

    /**
     * Scope untuk order by position
     */
    public function scopeOrderByPosition($query)
    {
        return $query->orderBy('general_position');
    }

    /**
     * Scope untuk order by elapsed time (fastest first)
     */
    public function scopeFastest($query)
    {
        return $query->whereNotNull('elapsed_time')
            ->orderBy('elapsed_time');
    }

    /**
     * Check if participant has comorbid. (NEW)
     */
    public function hasComorbid(): bool
    {
        return $this->has_comorbid;
    }

    /**
     * Get formatted elapsed time
     */
    public function getFormattedElapsedTimeAttribute(): ?string
    {
        return $this->elapsed_time ? $this->elapsed_time->format('H:i:s') : null;
    }

    /**
     * Get display name (bib_name if available, otherwise name). (NEW)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->bib_name ?: $this->name;
    }

    /**
     * Get full address. (NEW)
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->regency,
            $this->province,
            $this->country,
        ]);

        return implode(', ', $parts);
    }
}
