<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Shift;
use Illuminate\Database\Seeder;
use Database\Seeders\ShiftSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ShiftSeeder::class,
        ]);
        // User::factory()->create();
        // Shift::factory(1)->create();

        User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'userType' => 1,
        ]);
    }
}
