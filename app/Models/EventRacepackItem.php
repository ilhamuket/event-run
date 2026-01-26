<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRacepackItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'item_name',
        'item_number',
        'description',
        'image_path',
        'features',
        'badge_color',
        'order',
        'is_active'
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get badge color classes for Tailwind
     */
    public function getBadgeColorClassesAttribute()
    {
        $colors = [
            'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
            'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
            'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-700'],
            'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700'],
            'orange' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700'],
            'red' => ['bg' => 'bg-red-100', 'text' => 'text-red-700'],
        ];

        return $colors[$this->badge_color] ?? $colors['blue'];
    }
}
