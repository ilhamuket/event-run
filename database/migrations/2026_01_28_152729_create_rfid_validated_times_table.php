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
        Schema::create('rfid_validated_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('participants')->onDelete('cascade');
            $table->foreignId('rfid_checkpoint_id')->constrained('rfid_checkpoints')->onDelete('cascade');
            $table->foreignId('rfid_raw_log_id')->nullable()->constrained('rfid_raw_logs')->onDelete('set null');
            $table->dateTime('checkpoint_time'); // waktu yang sudah divalidasi untuk checkpoint ini
            $table->time('elapsed_time')->nullable(); // waktu tempuh dari start sampai checkpoint ini
            $table->time('split_time')->nullable(); // waktu tempuh dari checkpoint sebelumnya
            $table->integer('position_at_checkpoint')->nullable(); // posisi runner saat di checkpoint ini
            $table->enum('validation_status', ['auto', 'manual', 'corrected'])->default('auto');
            $table->text('validation_notes')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null'); // admin yang validasi
            $table->timestamps();

            // Unique constraint: satu peserta hanya boleh punya 1 waktu valid per checkpoint
            $table->unique(['participant_id', 'rfid_checkpoint_id'], 'unique_participant_checkpoint');

            $table->index(['participant_id', 'checkpoint_time']);
            $table->index('rfid_checkpoint_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfid_validated_times');
    }
};
