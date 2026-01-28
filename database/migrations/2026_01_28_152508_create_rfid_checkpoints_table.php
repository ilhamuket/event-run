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
        Schema::create('rfid_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_category_id')->constrained('event_categories')->onDelete('cascade');
            $table->string('checkpoint_type'); // start, checkpoint, finish
            $table->string('checkpoint_name'); // e.g., "Start", "CP1", "CP2", "Finish"
            $table->integer('checkpoint_order'); // urutan: 0=start, 1=cp1, 2=cp2, dst, 999=finish
            $table->decimal('distance_km', 8, 2)->nullable(); // jarak dari start dalam km
            $table->string('location_name')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->time('cutoff_time')->nullable(); // batas waktu untuk checkpoint ini
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['event_category_id', 'checkpoint_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfid_checkpoints');
    }
};
