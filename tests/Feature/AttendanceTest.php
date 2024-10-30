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

it('allows admin to update an employee', function () {

    $userB = User::factory()->create([
        'name' => 'user4',
        'email' => 'user4@gmail.com',
        'password' => 1234,
        'shift_id' => $this->morningShift->id,
    ]);

    $this->actingAs($this->admin, 'sanctum');
    $employeeData = [
        'name' => 'user2',
        'email' => 'user2@gmail.com',
        'password' => 1234,
        'shift_id' => $this->morningShift->id,
    ];

    $response = $this->putJson("/api/update-employee/{$userB->id}", $employeeData);

    $response->assertStatus(200);
    $this->assertDatabaseHas('users', ['email' => 'user2@gmail.com']);
});

it('restrict non admin to update employee', function () {

    $userB = User::factory()->create([
        'name' => 'user4',
        'email' => 'user4@gmail.com',
        'password' => 1234,
        'shift_id' => $this->morningShift->id,
    ]);

    $this->actingAs($this->user, 'sanctum');
    $employeeData = [
        'name' => 'user2',
        'email' => 'user2@gmail.com',
        'password' => 1234,
        'shift_id' => $this->morningShift->id,
    ];

    $response = $this->putJson("/api/update-employee/{$userB->id}", $employeeData);

    $response->assertStatus(403);
});


it('allows admin to delete an employee', function () {

    $userC = User::factory()->create([
        'name' => 'user5',
        'email' => 'user5@gmail.com',
        'password' => 1234,
        'shift_id' => $this->morningShift->id,
    ]);

    $this->actingAs($this->admin, 'sanctum');
    $response = $this->deleteJson("/api/delete-employee/{$userC->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('users', ['name' => $userC->name]);
    $response->assertJson([
        "message" => "employee deleted successfully!!"
    ]);
});
it('restrict normal user to delete an employee', function () {

    $userC = User::factory()->create([
        'name' => 'user5',
        'email' => 'user5@gmail.com',
        'password' => 1234,
        'shift_id' => $this->morningShift->id,
    ]);

    $this->actingAs($this->user, 'sanctum');
    $response = $this->deleteJson("/api/delete-employee/{$userC->id}");

    $response->assertStatus(403);
    $response->assertJson([
        'message' => "U have not access to employee list",
    ]);
});
it('allows authenticated user to clock in', function () {
    $this->actingAs($this->user, 'sanctum');
    $response = $this->postJson('/api/clock-in');
    $response->assertStatus(200);
    $response->assertJsonStructure(['message', 'data']);
    $this->assertDatabaseHas('attendances', [
        'user_id' => $this->user->id,
        'date' => now()->today()->format('Y-m-d'),
    ]);
});
it('restrict non authenticated user to clock in', function () {
    $response = $this->postJson('/api/clock-in');
    $response->assertStatus(401);
});

it('allows only one clock in per day', function () {
    $this->actingAs($this->user, 'sanctum');
    $this->postJson('/api/clock-in');
    $response = $this->postJson('/api/clock-in');
    $response->assertStatus(200);
    $response->assertJson(['message' => 'already clocked in!! please wait next day!!']);
});

it('informs user when they clock in late', function () {
    $this->actingAs($this->user, 'sanctum');
    $this->travelTo(now()->setTime(9, 0));
    $response = $this->postJson('/api/clock-in');
    $response->assertStatus(200);
    $this->assertStringContainsString(
        "U've successfully clocked in but You late!",
        $response->json('message')
    );
});

it('informs user when they clock in on time', function () {
    $this->actingAs($this->user, 'sanctum');
    $this->travelTo(now()->setTime(8, 0));
    $response = $this->postJson('/api/clock-in');
    $response->assertStatus(200);
    $this->assertStringContainsString(
        "U've successfully clocked in on time!",
        $response->json('message')
    );
});
