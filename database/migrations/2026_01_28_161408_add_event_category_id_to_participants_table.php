<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {

            $table->foreignId('event_category_id')
                ->after('event_id')
                ->nullable()
                ->constrained('event_categories')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {

            $table->dropForeign(['event_category_id']);
            $table->dropColumn('event_category_id');
        });
    }
};
