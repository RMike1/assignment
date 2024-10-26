<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\ShiftSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(4)->create();
        // $this->call([
        //     ShiftSeeder::class,
        // ]);

        // User::factory()->create([
        //         'name' => 'Admin',
        //         'email' => 'admin@gmail.com',
        //         'userType' => 1,
        // ]);
    }
}
