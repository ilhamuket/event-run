<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin2@scoutrun.id'],
            [
                'name' => 'Admin',
                'password' => Hash::make('nimda123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
