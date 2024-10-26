<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('shifts')->insert([
            [
                'name' => 'Morning Shift',
                'time_in' => '08:00:00', 
                'time_out' => '16:00:00', 
                'slug' => 'morning-shift' 
            ],
            [
                'name' => 'Afternoon Shift',
                'time_in' => '14:00:00',
                'time_out' => '22:00:00',
                'slug' => 'afternoon-shift'
            ],
            [
                'name' => 'Night Shift',
                'time_in' => '22:00:00',
                'time_out' => '06:00:00',
                'slug' => 'night-shift',
            ],
            
        ]);
    }
}
