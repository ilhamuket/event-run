<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'poster',
        'banner_image',
        'start_time',
        'location_name',
        'latitude',
        'longitude',
        'instagram_url',
        'strava_route_url',
        'youtube_url',
        'contact_phone',
        'is_published'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'is_published' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7'
    ];

    /**
     * Hero slider images
     */
    public function heroImages()
    {
        return $this->hasMany(EventHeroImage::class)->where('is_active', true)->orderBy('order');
    }

    /**
     * Event categories (5K, 10K, 21K, etc)
     */
    public function categories()
    {
        return $this->hasMany(EventCategory::class)->where('is_active', true)->orderBy('order');
    }

    /**
     * Race pack items
     */
    public function racepackItems()
    {
        return $this->hasMany(EventRacepackItem::class)->where('is_active', true)->orderBy('order');
    }

    /**
     * Existing relationships (if any)
     */
    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('start_time', '<=', now());
    }
}
