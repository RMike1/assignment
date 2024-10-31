<?php

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminAttendanceNotification;
use App\Mail\AttendanceClockInNotification;
use App\Mail\AttendanceClockOutNotification;


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

it('allows only admin to view attendance data', function () {
    $response = $this->actingAs($this->admin)->getJson(route('attendance'));
    $response->assertStatus(200);
});
it('restrict non admin to view attendance data', function () {
    $response = $this->actingAs($this->user)->getJson(route('attendance'));
    $response->assertStatus(403);
    $response->assertJson(["message" => "U have not access to check attendance list"]);
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



it('sends email notifications to the user and admin upon clocking in', function () {
    Mail::fake();
    $this->actingAs($this->user, 'sanctum');
    $response = $this->postJson('/api/clock-in');
    $response->assertStatus(200);
    Mail::assertQueued(AttendanceClockInNotification::class, function ($mail) {
        return $mail->hasTo($this->user->email);
    });
    $admin = User::where('userType', 1)->first();
    Mail::assertQueued(AdminAttendanceNotification::class, function ($mail) use ($admin) {
        return $mail->hasTo($admin->email);
    });
});

it('allows authenticated employee to clock out after clocking in', function () {
    $this->actingAs($this->user, 'sanctum');
    $this->user->attendances()->create([
        'clock_in' => now(),
        'date' => now()->today(),
    ]);
    $response = $this->postJson('/api/clock-out');
    $response->assertStatus(200);
    $response->assertJson(['message' => "You've successfully clocked out!!"]);
    $this->assertDatabaseHas('attendances', [
        'user_id' => $this->user->id,
        'date' => now()->today()->format('Y-m-d'),
    ]);
});

it('restricts non-authenticated employee from clocking out', function () {
    $response = $this->postJson('/api/clock-out');
    $response->assertStatus(401);
});

it('prevents multiple clock-outs on the same day', function () {
    $this->actingAs($this->user, 'sanctum');
    $this->user->attendances()->create([
        'clock_in' => now(),
        'clock_out' => now(),
        'date' => now()->today(),
    ]);
    $response = $this->postJson('/api/clock-out');
    $response->assertStatus(200);
    $response->assertJson(['message' => 'already clocked out!! please wait next day!!']);
});

it('returns error message if user tries to clock out without clocking in', function () {
    $this->actingAs($this->user, 'sanctum');
    $this->assertDatabaseMissing('attendances', [
        'user_id' => $this->user->id,
        'date' => now()->today()->format('Y-m-d'),
    ]);
    $response = $this->postJson('/api/clock-out');
    $response->assertStatus(200);
    $response->assertJson(['message' => "You've not clocked yet, please first clock in!!"]);
});

it('sends email notifications to employee and admin upon clocking out', function () {
    Mail::fake();
    $this->actingAs($this->user, 'sanctum');
    $this->postJson('/api/clock-in');
    $response = $this->postJson('/api/clock-out');
    $response->assertStatus(200);
    Mail::assertQueued(AttendanceClockOutNotification::class, function ($mail) {
        return $mail->hasTo($this->user->email);
    });
    $admin = User::where('userType', 1)->first();
    Mail::assertQueued(AdminAttendanceNotification::class, function ($mail) use ($admin) {
        return $mail->hasTo($admin->email);
    });
});

it('sends late clock-in notification if clock-in is after shift start', function () {
    Mail::fake();
    $this->actingAs($this->user, 'sanctum');
    $this->user->shift()->update(['time_in' => now()->subHours(1)]);
    $response = $this->postJson('/api/clock-in');
    $response->assertStatus(200);
    $response->assertJson(['message' => "U've successfully clocked in but You late!"]);
    Mail::assertQueued(AttendanceClockInNotification::class);
    Mail::assertQueued(AdminAttendanceNotification::class);
});
