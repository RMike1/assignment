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


it('can login with correct credentials', function () {
    $response = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => 1234, 
    ]);

    $response->assertStatus(200); 
    $response->assertJsonStructure([
        'user' => ['name', 'email'],
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


it('allows only admin to view all employees', function () {
    $response = $this->actingAs($this->admin)->getJson('/api/all-employess');
    $response->assertStatus(200);
});

it('restricts non-admins from accessing employee list', function () {
    $response = $this->actingAs($this->user)->getJson('/api/all-employess');
    $response->assertStatus(403); 
    $response->assertJson([
        'message' => "U have not access to employee list", 
    ]);
});
