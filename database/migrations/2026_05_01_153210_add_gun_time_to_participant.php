<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Gun time disimpan di event_categories karena:
        // - Satu gun time berlaku untuk seluruh peserta dalam satu kategori
        // - Bisa berbeda antar kategori (misal: 10K gun time 06:00, 5K gun time 07:30)
        Schema::table('event_categories', function (Blueprint $table) {
            $table->dateTime('gun_time')->nullable()->after('name')
                ->comment('Waktu tembakan start resmi untuk kategori ini');
        });

        // gun_elapsed_time di participants, analog dengan elapsed_time (chip time)
        Schema::table('participants', function (Blueprint $table) {
            $table->string('gun_elapsed_time', 8)->nullable()->after('elapsed_time')
                ->comment('Elapsed time dihitung dari gun time (HH:MM:SS)');
        });
    }

    public function down(): void
    {
        Schema::table('event_categories', function (Blueprint $table) {
            $table->dropColumn('gun_time');
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('gun_elapsed_time');
        });
    }
};
