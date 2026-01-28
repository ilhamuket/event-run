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
        Schema::table('participants', function (Blueprint $table) {

            $table->string('bib_name')->nullable()->after('bib');
            $table->integer('age')->nullable()->after('gender');
            $table->string('email')->nullable()->after('age');
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->string('country')->default('Indonesia')->after('address');
            $table->string('province')->nullable()->after('country');
            $table->string('regency')->nullable()->after('province'); // kabupaten
            $table->string('jersey_size')->nullable()->after('city');
            $table->string('community')->nullable()->after('jersey_size');
            $table->string('emergency_contact_name')->nullable()->after('community');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->boolean('has_comorbid')->default(false)->after('emergency_contact_phone');
            $table->text('comorbid_details')->nullable()->after('has_comorbid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn([
                'bib_name',
                'age',
                'email',
                'phone',
                'address',
                'country',
                'province',
                'regency',
                'jersey_size',
                'community',
                'emergency_contact_name',
                'emergency_contact_phone',
                'has_comorbid',
                'comorbid_details',
            ]);
        });
    }
};
