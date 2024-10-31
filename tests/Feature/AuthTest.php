<?php

use App\Models\User;
use App\Models\Shift;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Database\Seeders\ShiftSeeder;
use Illuminate\Support\Facades\DB;
use function Pest\Laravel\actingAs;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
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

it('sends a reset link to the user with a valid email', function () {
    $this->actingAs($this->user, 'sanctum');
    $response = $this->postJson('/api/forgot-password', [
        'email' => $this->user->email,
    ]);
    $response->assertStatus(200);
    $response->assertJson(['status' => __('passwords.sent')]);
});


it('fails to send reset link if email is not provided', function () {
    // Authenticate the user
    $this->actingAs($this->user, 'sanctum');
    $response = $this->postJson(route('forgot.password'), [
        'email' => '',
    ]);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

it('fails to send reset link if email is invalid', function () {
    $this->actingAs($this->user, 'sanctum');
    $response = $this->postJson(route('forgot.password'), [
        'email' => 'invalid-email',
    ]);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});
it('returns error if the user is not found', function () {
    $this->actingAs($this->user, 'sanctum');
    Password::shouldReceive('sendResetLink')
        ->once()
        ->with(['email' => 'nonexistent@example.com'])
        ->andReturn(Password::INVALID_USER);
    $response = $this->postJson(route('forgot.password'), [
        'email' => 'nonexistent@example.com',
    ]);
    $response->assertStatus(422);
    $response->assertJson([
        'message' => "We can't find a user with that email address.",
        'errors' => [
            'email' => [
                "We can't find a user with that email address."
            ],
        ],
    ]);
});


it('successfully resets the password with a valid token', function () {
    $token = Password::createToken($this->user);
    $response = $this->postJson(route('reset.password'), [
        'email' => $this->user->email,
        'password' => 12345,
        'password_confirmation' => 12345,
        'token' => $token,
    ]);
    $response->assertStatus(200);
    $response->assertJson(['message' => 'Password reset successfully']);
    $this->assertTrue(Hash::check(12345, $this->user->fresh()->password));
});

it('fails to reset the password if the token is invalid', function () {
    $response = $this->postJson(route('reset.password'), [
        'email' => $this->user->email,
        'password' => 12345,
        'password_confirmation' => 12345,
        'token' => 'invalid-token',
    ]);
    $response->assertStatus(500);
    $response->assertJson(['message' => trans(Password::INVALID_TOKEN)]);
});

it('fails to reset the password if the email is not provided', function () {
    $token = Password::createToken($this->user);
    $response = $this->postJson(route('reset.password'), [
        'token' => $token,
        'password' => 12345,
        'password_confirmation' => 12345,
    ]);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

it('fails to reset the password if the passwords do not match', function () {
    $token = Password::createToken($this->user);
    $response = $this->postJson(route('reset.password'), [
        'email' => $this->user->email,
        'password' => 12345,
        'password_confirmation' => 123456,
        'token' => $token,
    ]);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});


