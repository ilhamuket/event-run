<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Production safety constraints untuk RFID timing system.
 *
 * Kenapa ini WAJIB dijalankan sebelum race day:
 * - Unique constraint di DB adalah last line of defense dari race condition.
 *   Kalau 2 request bersamaan lolos semua pengecekan PHP, DB yang akan reject.
 * - Index di rfid_raw_logs krusial karena tabel ini akan punya jutaan baris
 *   di tengah event (1300 peserta × banyak checkpoint).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── rfid_validated_times ──────────────────────────────────────────────
        // Satu peserta hanya boleh punya satu record per checkpoint.
        // Ini adalah safety net final dari race condition — kalau dua request
        // concurrent lolos pengecekan PHP, INSERT kedua akan throw QueryException
        // dengan error code 23000 (duplicate entry) yang kita tangkap di service.
        Schema::table('rfid_validated_times', function (Blueprint $table) {
            $table->unique(
                ['participant_id', 'rfid_checkpoint_id'],
                'uq_participant_checkpoint'
            );

            // Index untuk query posisi di checkpoint (dipakai saat insert validated time)
            $table->index('rfid_checkpoint_id', 'idx_validated_checkpoint');

            // Index untuk query history per peserta
            $table->index('participant_id', 'idx_validated_participant');
        });

        // ─── rfid_raw_logs ─────────────────────────────────────────────────────
        // Tabel ini tumbuh paling cepat. Setiap scan = 1 baris, valid atau tidak.
        // Index composite untuk rapid duplicate check di processScan().
        Schema::table('rfid_raw_logs', function (Blueprint $table) {
            // Dipakai oleh rapid duplicate check: WHERE event_id + checkpoint + rfid_tag + scanned_at >= ?
            $table->index(
                ['event_id', 'rfid_checkpoint_id', 'rfid_tag', 'scanned_at'],
                'idx_raw_rapid_dup'
            );

            // Dipakai oleh getRawLogs() untuk filter + sort
            $table->index(['event_id', 'scanned_at'], 'idx_raw_event_time');

            // Dipakai untuk monitoring dashboard per checkpoint
            $table->index(['rfid_checkpoint_id', 'scanned_at'], 'idx_raw_checkpoint_time');
        });

        // ─── participants ───────────────────────────────────────────────────────
        // Index untuk recalculatePositions() — query ini jalan setiap ada finisher baru.
        // Tanpa index, full table scan di 1300 baris. Dengan index, instant.
        Schema::table('participants', function (Blueprint $table) {
            // ORDER BY elapsed_time WHERE event_category_id + elapsed_time NOT NULL
            $table->index(
                ['event_category_id', 'elapsed_time'],
                'idx_participants_category_time'
            );

            // Untuk general position recalculation lintas kategori
            $table->index(
                ['event_id', 'elapsed_time'],
                'idx_participants_event_time'
            );
        });

        // ─── rfid_checkpoints ──────────────────────────────────────────────────
        // Index untuk query "previous checkpoint" saat hitung split time.
        Schema::table('rfid_checkpoints', function (Blueprint $table) {
            $table->index(
                ['event_category_id', 'checkpoint_order', 'is_active'],
                'idx_checkpoint_category_order'
            );
        });
    }

    public function down(): void
    {
        Schema::table('rfid_validated_times', function (Blueprint $table) {
            $table->dropUnique('uq_participant_checkpoint');
            $table->dropIndex('idx_validated_checkpoint');
            $table->dropIndex('idx_validated_participant');
        });

        Schema::table('rfid_raw_logs', function (Blueprint $table) {
            $table->dropIndex('idx_raw_rapid_dup');
            $table->dropIndex('idx_raw_event_time');
            $table->dropIndex('idx_raw_checkpoint_time');
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex('idx_participants_category_time');
            $table->dropIndex('idx_participants_event_time');
        });

        Schema::table('rfid_checkpoints', function (Blueprint $table) {
            $table->dropIndex('idx_checkpoint_category_order');
        });
    }
};
