<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::create([
        'slug' => 'biduk-biduk-run-2026',
        'name' => 'Biduk Biduk Run 2026',
        'description' => 'Event lari santai di pesisir Biduk-Biduk',
        'start_time' => '2026-02-15 05:30:00',
        'location_name' => 'Pantai Biduk-Biduk',
        'latitude' => -1.234567,
        'longitude' => 118.123456,
        'instagram_url' => 'https://instagram.com/bidukbidukrun',
        'strava_route_url' => 'https://strava.com/routes/xxxx',
        'contact_phone' => '08123456789',
        'is_published' => true,
        ]);
    }
}
