<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'poster',
        'start_time',
        'location_name',
        'latitude',
        'longitude',
        'instagram_url',
        'strava_route_url',
        'contact_phone',
        'is_published',
    ];

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }
}
