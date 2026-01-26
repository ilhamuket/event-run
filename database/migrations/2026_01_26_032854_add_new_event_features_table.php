<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom ke tabel events untuk YouTube dan banner
        Schema::table('events', function (Blueprint $table) {
            $table->string('youtube_url')->nullable()->after('strava_route_url');
            $table->string('banner_image')->nullable()->after('poster');
        });

        // 2. Tabel untuk Hero Slider Images
        Schema::create('event_hero_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->string('image_path');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['event_id', 'order']);
        });

        // 3. Tabel untuk Event Categories (5K, 10K, 21K)
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->string('name'); // 5K Run, 10K Run, Half Marathon
            $table->string('slug'); // 5k, 10k, 21k
            $table->string('distance'); // 5 Km, 10 Km, 21 Km
            $table->string('level'); // Beginner, Intermediate, Advanced
            $table->string('elevation')->nullable(); // +50m, +120m, +250m
            $table->string('terrain')->nullable(); // Aspal, Mixed, Challenging
            $table->string('cut_off_time')->nullable(); // 60 Min, 90 Min, 180 Min
            $table->string('course_map_image')->nullable();
            $table->text('description')->nullable();
            $table->string('color_from')->default('blue-400'); // untuk gradient
            $table->string('color_to')->default('blue-600'); // untuk gradient
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['event_id', 'order']);
        });

        // 4. Tabel untuk Race Pack Items
        Schema::create('event_racepack_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->string('item_name'); // Jersey Premium, Medali Finisher, etc
            $table->string('item_number')->nullable(); // Item #1, Item #2
            $table->text('description'); // Short description
            $table->string('image_path')->nullable();
            $table->json('features')->nullable(); // Array of features/details
            $table->string('badge_color')->default('blue'); // blue, yellow, green, purple
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['event_id', 'order']);
        });

        // 5. Tabel untuk Registration Prices per Category (optional, untuk future)
        Schema::create('event_category_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_category_id')->constrained('event_categories')->onDelete('cascade');
            $table->string('phase_name'); // Early Bird, Normal, Last Minute
            $table->decimal('price', 10, 2);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_category_prices');
        Schema::dropIfExists('event_racepack_items');
        Schema::dropIfExists('event_categories');
        Schema::dropIfExists('event_hero_images');

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['youtube_url', 'banner_image']);
        });
    }
};
