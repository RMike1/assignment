<?php

use App\Models\User;
use App\Models\Shift;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Database\Seeders\ShiftSeeder;
use function Pest\Laravel\actingAs;
use Illuminate\Support\Facades\DB;
// use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

// uses(LazilyRefreshDatabase::class);


beforeEach(function () {
    DB::beginTransaction();
    $this->seed(ShiftSeeder::class);
    $this->morningShift = Shift::where('slug', 'morning-shift')->first();
    $this->admin = User::factory()->admin()->create(['shift_id' => $this->morningShift->id]);
    $this->user = User::factory()->create(['shift_id' => $this->morningShift->id]);
});


it('can login with correct credentials', function () {
    $response = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => 1234, 
    ]);

    $response->assertStatus(200); 
    $response->assertJsonStructure([
        'user' => [
            'id', 'name', 'email', 'userType', 'shift_id'
        ],
        'token'
    ]);
});

it('cannot login with wrong credentials', function () {

    $response = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => 12345,
    ]);

    $response->assertStatus(401);
    $response->assertJson(['message' => 'Unauthorized']);
});