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
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();

            $table->string('bib')->nullable()->unique();
            $table->string('name');
            $table->enum('gender', ['M', 'F']);
            $table->string('category')->nullable();
            $table->string('city')->nullable();

            $table->time('elapsed_time')->nullable();
            $table->integer('general_position')->nullable();
            $table->integer('category_position')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
