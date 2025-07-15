<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            // ProfileSeeder::class,
            SkillSeeder::class,
            ApplicationSeeder::class,
            ContactSeeder::class,
            NoteSeeder::class,
            TaskSeeder::class,
        ]);
    }
}
