<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Index list:
     *  - rfid_raw_logs:           idx_rapid_dup (event_id, rfid_checkpoint_id, rfid_tag, scanned_at, is_valid)
     *  - rfid_validated_times:    idx_participant_cp (participant_id, rfid_checkpoint_id)
     *  - rfid_validated_times:    idx_checkpoint_order (rfid_checkpoint_id)
     *  - participant_rfid_mappings: idx_rfid_tag_active (rfid_tag, is_active)
     *  - participants:            idx_event_elapsed (event_id, elapsed_time)
     *  - participants:            idx_category_elapsed (event_category_id, elapsed_time)
     */
    public function up(): void
    {
        // ── rfid_raw_logs ──────────────────────────────────────────────────────
        Schema::table('rfid_raw_logs', function (Blueprint $table) {
            if (!$this->hasIndex('rfid_raw_logs', 'idx_rapid_dup')) {
                $table->index(
                    ['event_id', 'rfid_checkpoint_id', 'rfid_tag', 'scanned_at', 'is_valid'],
                    'idx_rapid_dup'
                );
            }
        });

        // ── rfid_validated_times ───────────────────────────────────────────────
        Schema::table('rfid_validated_times', function (Blueprint $table) {
            if (!$this->hasIndex('rfid_validated_times', 'idx_participant_cp')) {
                $table->index(
                    ['participant_id', 'rfid_checkpoint_id'],
                    'idx_participant_cp'
                );
            }

            if (!$this->hasIndex('rfid_validated_times', 'idx_checkpoint_order')) {
                $table->index(
                    ['rfid_checkpoint_id'],
                    'idx_checkpoint_order'
                );
            }
        });

        // ── participant_rfid_mappings ──────────────────────────────────────────
        Schema::table('participant_rfid_mappings', function (Blueprint $table) {
            if (!$this->hasIndex('participant_rfid_mappings', 'idx_rfid_tag_active')) {
                $table->index(
                    ['rfid_tag', 'is_active'],
                    'idx_rfid_tag_active'
                );
            }
        });

        // ── participants ───────────────────────────────────────────────────────
        Schema::table('participants', function (Blueprint $table) {
            if (!$this->hasIndex('participants', 'idx_event_elapsed')) {
                $table->index(
                    ['event_id', 'elapsed_time'],
                    'idx_event_elapsed'
                );
            }

            if (!$this->hasIndex('participants', 'idx_category_elapsed')) {
                $table->index(
                    ['event_category_id', 'elapsed_time'],
                    'idx_category_elapsed'
                );
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rfid_raw_logs', function (Blueprint $table) {
            if ($this->hasIndex('rfid_raw_logs', 'idx_rapid_dup')) {
                $table->dropIndex('idx_rapid_dup');
            }
        });

        Schema::table('rfid_validated_times', function (Blueprint $table) {
            if ($this->hasIndex('rfid_validated_times', 'idx_participant_cp')) {
                $table->dropIndex('idx_participant_cp');
            }

            if ($this->hasIndex('rfid_validated_times', 'idx_checkpoint_order')) {
                $table->dropIndex('idx_checkpoint_order');
            }
        });

        Schema::table('participant_rfid_mappings', function (Blueprint $table) {
            if ($this->hasIndex('participant_rfid_mappings', 'idx_rfid_tag_active')) {
                $table->dropIndex('idx_rfid_tag_active');
            }
        });

        Schema::table('participants', function (Blueprint $table) {
            if ($this->hasIndex('participants', 'idx_event_elapsed')) {
                $table->dropIndex('idx_event_elapsed');
            }

            if ($this->hasIndex('participants', 'idx_category_elapsed')) {
                $table->dropIndex('idx_category_elapsed');
            }
        });
    }

    /**
     * Cek apakah index sudah ada di tabel — mencegah Duplicate key error.
     * Bekerja untuk MySQL/MariaDB & PostgreSQL.
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver     = $connection->getDriverName();

        if ($driver === 'pgsql') {
            return (bool) $connection->selectOne(
                "SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?",
                [$table, $indexName]
            );
        }

        // MySQL / MariaDB
        $indexes = $connection->select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );

        return count($indexes) > 0;
    }
};
