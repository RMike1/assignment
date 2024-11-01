<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date'=>now(),
            'clock_in'=>fake()->time(),
            'clock_out'=>fake()->time(),
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory()->create()->id,
            // 'user_id' => rand(1, 5),
        ];
    }
}
