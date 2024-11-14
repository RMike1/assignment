<?php

namespace Database\Factories;

use App\Models\Shift;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'userType' => fake()->boolean(),
            'email' => fake()->unique()->safeEmail(),
            'shift_id' => Shift::inRandomOrder()->first()->id ?? 1,
            'profile_image' =>fake()->imageUrl(),
            'password' => static::$password ??= Hash::make('1234'),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin()
    {
        return $this->state([
            'userType' => 1,
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
