<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'color_from',
        'color_to',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }


}
