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
    $this->latest = User::where('userType',1)->first();
    $this->user = User::factory()->create(['shift_id' => $this->morningShift->id]);
});

afterEach(function () {
    DB::rollBack();
});

it('allows admin to add an employee', function () {
    $this->actingAs($this->admin, 'sanctum');
    $employeeData = [
        'name' => 'user2',
        'email' => 'user2@gmail.com',
        'password' => 1234,
        'password_confirmation' => 1234,
        'shift_id' => $this->morningShift->id,
    ];

    $response = $this->postJson('/api/add-employee', $employeeData);

    $response->assertStatus(200);
    $this->assertDatabaseHas('users', ['email' => 'user2@gmail.com']);
});


it('restrict non-admin to add an employee', function () {
    $this->actingAs($this->user, 'sanctum');
    $employeeData = [
        'name' => 'user2',
        'email' => 'user2@gmail.com',
        'password' => 1234,
        'password_confirmation' => 1234,
        'shift_id' => $this->morningShift->id,
    ];
    $response = $this->postJson('/api/add-employee', $employeeData);
    $response->assertStatus(403);
});

