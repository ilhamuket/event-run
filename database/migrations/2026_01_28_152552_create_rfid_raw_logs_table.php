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
        Schema::create('rfid_raw_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('rfid_checkpoint_id')->nullable()->constrained('rfid_checkpoints')->onDelete('set null');
            $table->string('rfid_tag'); // nomor RFID yang ke-scan
            $table->string('bib')->nullable(); // bib peserta (bisa dipetakan dari RFID)
            $table->dateTime('scanned_at'); // waktu scan
            $table->string('reader_id')->nullable(); // ID alat RFID reader
            $table->integer('signal_strength')->nullable(); // kekuatan sinyal RFID
            $table->boolean('is_valid')->default(true); // apakah scan ini valid
            $table->text('notes')->nullable(); // catatan tambahan
            $table->timestamps();

            $table->index(['event_id', 'rfid_tag', 'scanned_at']);
            $table->index(['bib', 'rfid_checkpoint_id']);
            $table->index('scanned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfid_raw_logs');
    }
};
