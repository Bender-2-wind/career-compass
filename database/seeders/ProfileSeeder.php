<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::all()->each(function ($user) {
            if (!$user->profile) {
                Profile::factory()->create([
                    'user_id' => $user->id,
                ]);
            }
        });
    }
}
