<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\EventHeroImage;
use App\Models\EventCategory;
use App\Models\EventRacepackItem;

class EventCompleteDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil event yang sudah ada atau buat baru
        $event = Event::first();

        if (!$event) {
            $event = Event::create([
                'slug' => 'jakarta-marathon-2025',
                'name' => 'Jakarta Marathon 2025',
                'description' => 'Event lari terbesar di Jakarta dengan berbagai kategori untuk semua level pelari.',
                'start_time' => '2025-03-15 06:00:00',
                'location_name' => 'Gelora Bung Karno, Jakarta',
                'latitude' => -6.218481,
                'longitude' => 106.802146,
                'instagram_url' => 'https://instagram.com/jakartamarathon',
                'strava_route_url' => 'https://www.strava.com/routes/123456',
                'youtube_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'is_published' => true
            ]);
        }

        // 1. Hero Slider Images
        $heroImages = [
            ['image_path' => 'hero/slide-1.jpg', 'order' => 1],
            ['image_path' => 'hero/slide-2.jpg', 'order' => 2],
            ['image_path' => 'hero/slide-3.jpg', 'order' => 3],
            ['image_path' => 'hero/slide-4.jpg', 'order' => 4],
        ];

        foreach ($heroImages as $image) {
            EventHeroImage::create([
                'event_id' => $event->id,
                'image_path' => $image['image_path'],
                'order' => $image['order'],
                'is_active' => true
            ]);
        }

        // 2. Event Categories
        $categories = [
            [
                'name' => '5K Run',
                'slug' => '5k',
                'distance' => '5 Km',
                'level' => 'Beginner',
                'elevation' => '+50m',
                'terrain' => 'Aspal',
                'cut_off_time' => '60 Min',
                'course_map_image' => 'maps/5k-course.jpg',
                'description' => 'Kategori untuk pelari pemula yang ingin merasakan pengalaman lomba lari.',
                'color_from' => 'green-400',
                'color_to' => 'green-600',
                'order' => 1
            ],
            [
                'name' => '10K Run',
                'slug' => '10k',
                'distance' => '10 Km',
                'level' => 'Intermediate',
                'elevation' => '+120m',
                'terrain' => 'Mixed',
                'cut_off_time' => '90 Min',
                'course_map_image' => 'maps/10k-course.jpg',
                'description' => 'Kategori untuk pelari dengan pengalaman menengah yang mencari tantangan.',
                'color_from' => 'blue-400',
                'color_to' => 'blue-600',
                'order' => 2
            ],
            [
                'name' => 'Half Marathon',
                'slug' => '21k',
                'distance' => '21 Km',
                'level' => 'Advanced',
                'elevation' => '+250m',
                'terrain' => 'Challenging',
                'cut_off_time' => '180 Min',
                'course_map_image' => 'maps/21k-course.jpg',
                'description' => 'Kategori untuk pelari advanced yang siap menghadapi tantangan jarak jauh.',
                'color_from' => 'orange-400',
                'color_to' => 'red-500',
                'order' => 3
            ]
        ];

        foreach ($categories as $category) {
            EventCategory::create(array_merge(['event_id' => $event->id], $category));
        }

        // 3. Race Pack Items
        $racepackItems = [
            [
                'item_name' => 'Jersey Teknis Premium',
                'item_number' => 'Item #1',
                'description' => 'Jersey running berkualitas tinggi dengan teknologi quick-dry dan breathable material. Desain eksklusif event dengan logo official di bagian dada.',
                'image_path' => 'racepack/jersey.jpg',
                'features' => [
                    'Material Polyester Premium',
                    'Quick Dry Technology',
                    'Ukuran S - XXL'
                ],
                'badge_color' => 'blue',
                'order' => 1
            ],
            [
                'item_name' => 'Medali Finisher',
                'item_number' => 'Item #2',
                'description' => 'Medali eksklusif untuk semua finisher dengan desain premium dan detail yang menawan. Setiap medali dibuat khusus untuk kategori masing-masing.',
                'image_path' => 'racepack/medal.jpg',
                'features' => [
                    'Material Metal Berkualitas',
                    'Desain Eksklusif Event',
                    'Tali Premium'
                ],
                'badge_color' => 'yellow',
                'order' => 2
            ],
            [
                'item_name' => 'Race BIB & Timing Chip',
                'item_number' => 'Item #3',
                'description' => 'BIB number eksklusif dengan timing chip terintegrasi untuk tracking waktu yang akurat. Dilengkapi dengan safety pins berkualitas.',
                'image_path' => 'racepack/bib.jpg',
                'features' => [
                    'RFID Timing Chip',
                    'Waterproof Material',
                    'Nomor Unik Personal'
                ],
                'badge_color' => 'green',
                'order' => 3
            ],
            [
                'item_name' => 'Goodie Bag & Merchandise',
                'item_number' => 'Item #4',
                'description' => 'Tas eksklusif berisi berbagai merchandise dari sponsor dan partner official event. Termasuk voucher, sampel produk, dan merchandise eksklusif.',
                'image_path' => 'racepack/goodiebag.jpg',
                'features' => [
                    'Tas Berkualitas Premium',
                    'Merchandise dari Sponsor',
                    'Voucher & Special Offers'
                ],
                'badge_color' => 'purple',
                'order' => 4
            ]
        ];

        foreach ($racepackItems as $item) {
            EventRacepackItem::create(array_merge(['event_id' => $event->id], $item));
        }

        $this->command->info('Event complete data seeded successfully!');
    }
}
