<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'poster',
        'banner_image',
        'race_guide',
        'start_time',
        'location_name',
        'latitude',
        'longitude',
        'instagram_url',
        'strava_route_url',
        'youtube_url',
        'contact_phone',
        'is_published',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_published' => 'boolean',
    ];

    /**
     * Get all categories for this event.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(EventCategory::class)->orderBy('order');
    }

    /**
     * Get all hero images for this event.
     */
    public function heroImages(): HasMany
    {
        return $this->hasMany(EventHeroImage::class)->orderBy('order');
    }

    /**
     * Get all active hero images for this event.
     */
    public function activeHeroImages(): HasMany
    {
        return $this->heroImages()->where('is_active', true);
    }

    /**
     * Get all racepack items for this event.
     */
    public function racepackItems(): HasMany
    {
        return $this->hasMany(EventRacepackItem::class)->orderBy('order');
    }

    /**
     * Get all active racepack items for this event.
     */
    public function activeRacepackItems(): HasMany
    {
        return $this->racepackItems()->where('is_active', true);
    }

    /**
     * Get all participants for this event.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    /**
     * Get all contacts for this event. (NEW)
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(EventContact::class)->orderBy('order');
    }

    /**
     * Get all active contacts for this event. (NEW)
     */
    public function activeContacts(): HasMany
    {
        return $this->contacts()->where('is_active', true);
    }

    /**
     * Get WhatsApp contacts for this event. (NEW)
     */
    public function whatsappContacts(): HasMany
    {
        return $this->contacts()->where('type', 'whatsapp')->where('is_active', true);
    }

    /**
     * Get email contacts for this event. (NEW)
     */
    public function emailContacts(): HasMany
    {
        return $this->contacts()->where('type', 'email')->where('is_active', true);
    }
    /**
     * Get all active operating hours for this event. (NEW)
     */
    public function activeOperatingHours(): HasMany
    {
        return $this->operatingHours()->where('is_active', true);
    }

    /**
     * Get all RFID raw logs for this event. (NEW)
     */
    public function rfidRawLogs(): HasMany
    {
        return $this->hasMany(RfidRawLog::class);
    }

    /**
     * Scope untuk filter hanya event published
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope untuk filter event upcoming
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    /**
     * Scope untuk filter event past
     */
    public function scopePast($query)
    {
        return $query->where('start_time', '<=', now());
    }

    /**
     * Check if event is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->start_time->isFuture();
    }

    /**
     * Check if event is past
     */
    public function isPast(): bool
    {
        return $this->start_time->isPast();
    }

    /**
     * Check if event has race guide (NEW)
     */
    public function hasRaceGuide(): bool
    {
        return !empty($this->race_guide);
    }

    /**
     * Get race guide URL (NEW)
     */
    public function getRaceGuideUrlAttribute(): ?string
    {
        return $this->race_guide ? asset('storage/' . $this->race_guide) : null;
    }
}
