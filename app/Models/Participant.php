<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = [
        'event_id',
        'bib',
        'name',
        'gender',
        'category',
        'city',
        'elapsed_time',
        'general_position',
        'category_position',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
