<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventHeroImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'image_path',
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
