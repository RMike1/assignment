<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Response;
use App\Services\DropboxService;
use Illuminate\Http\UploadedFile;
use App\Mail\AttendanceReportMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\AdminAttendanceNotification;
use App\Mail\AttendanceClockInNotification;
use App\Mail\AttendanceClockOutNotification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


it('allows admin to add an employee with Google drive to store profile image', function () {
    $this->actingAs($this->admin, 'sanctum');

    $employeeData = [
        'name' => 'user7',
        'email' => 'user7@gmail.com',
        'password' => 1234,
        'password_confirmation' => 1234,
        'shift_id' => $this->morningShift->id,
        'profile_image' => $this->google_drive_file,
        'upload_type' => 'google',
    ];

    $response = $this->postJson('/api/add-employee', $employeeData);

    $response->assertStatus(200)
        ->assertJsonPath('user.name', 'user7')
        ->assertJsonPath('employee_profile_image', 'employee-name-profile-image.jpg');
    $this->assertDatabaseHas('users', ['email' => 'user7@gmail.com']);
});


it('allows admin to add an employee with DropBox to store profile image', function () {

    $this->actingAs($this->admin, 'sanctum');
    $employeeData = [
        'name' => 'user3',
        'email' => 'user3@gmail.com',
        'password' => 1234,
        'password_confirmation' => 1234,
        'shift_id' => $this->afternoonShift->id,
        'profile_image' => $this->dropbox_file,
        'upload_type' => 'dropbox',
    ];

    $response = $this->postJson('/api/add-employee', $employeeData);

    $response->assertStatus(200)
        ->assertJsonPath('user.name', 'user3')
        ->assertJsonPath('employee_profile_image', 'employee-name-profile-image.jpg');
    $this->assertDatabaseHas('users', ['email' => 'user3@gmail.com']);
});



it('restrict non-admin to add an employee', function () {
    $this->actingAs($this->user, 'sanctum');
    $employeeData = [
        'name' => 'user2',
        'email' => 'user2@gmail.com',
        'password' => 1234,
        'password_confirmation' => 1234,
        'shift_id' => $this->morningShift->id,
        'profile_image' => UploadedFile::fake()->image('user23.jpg'),
        'upload_type' => 'google',
    ];
    $response = $this->postJson('/api/add-employee', $employeeData);
    $response->assertStatus(403);
});

it('fails if upload type is missing', function () {

    $this->actingAs($this->admin, 'sanctum');
    $response = $this->postJson('/api/add-employee', [
        'name' => 'user7',
        'email' => 'user7@gmail.com',
        'password' => 1234,
        'password_confirmation' => 1234,
        'shift_id' => $this->morningShift->id,
        'profile_image' => UploadedFile::fake()->image('user.jpg'),
    ]);
    $response->assertStatus(422);
});

it('fails if name or email already exists', function () {


    $this->actingAs($this->admin, 'sanctum');

    User::factory()->create([
        'name' => 'user12',
        'email' => 'user12@gmail.com',
        'password' => 1234,
        'shift_id' => $this->morningShift->id,
        'profile_image' => $this->google_drive_file,
    ]);

    $response = $this->postJson('/api/add-employee', [
        'name' => 'user12',
        'email' => 'user12@gmail.com',
        'password' => 1234,
        'password_confirmation' => 1234,
        'shift_id' => $this->morningShift->id,
        'profile_image' => UploadedFile::fake()->image('user12.jpg'),
        'upload_type' => 'google',
    ]);
    $response->assertStatus(422);
});


it('allows admin to update an employee with an profile image via GoogleDrive on store', function () {

    $userB = User::factory()->create([
        'name' => 'user4',
        'email' => 'user4@gmail.com',
        'password' => 1234,
        'shift_id' => $this->morningShift->id,
        'profile_image' => $this->google_drive_file,
    ]);

    $this->actingAs($this->admin, 'sanctum');
    $employeeData = [
        'name' => 'user7',
        'email' => 'user7@gmail.com',
        'password' => 1234,
        'password_confirmation' => 1234,
        'shift_id' => $this->morningShift->id,
        'profile_image' => $this->google_drive_file,
        'upload_type' => 'google',
    ];

    $response = $this->putJson("/api/update-employee/{$userB->id}", $employeeData);

    $response->assertStatus(200)
        ->assertJsonPath('user.name', 'user7')
        ->assertJsonPath('employee_profile_image', 'employee-name-profile-image.jpg');
    $this->assertDatabaseHas('users', ['email' => 'user7@gmail.com']);
});


it('allows admin to update an employee with an profile image via Dropbox on store', function () {

    $userB = User::factory()->create([
        'name' => 'user4',
        'email' => 'user4@gmail.com',
        'password' => 1234,
        'shift_id' => $this->morningShift->id,
        'profile_image' => $this->google_drive_file,
    ]);

    $this->actingAs($this->admin, 'sanctum');
    $employeeData = [
        'name' => 'user3',
        'email' => 'user3@gmail.com',
        'password' => 1234,
        'password_confirmation' => 1234,
        'shift_id' => $this->morningShift->id,
        'profile_image' => $this->dropbox_file,
        'upload_type' => 'dropbox',
    ];

    $response = $this->putJson("/api/update-employee/{$userB->id}", $employeeData);

    $response->assertStatus(200)
        ->assertJsonPath('user.name', 'user3')
        ->assertJsonPath('employee_profile_image', 'employee-name-profile-image.jpg');
    $this->assertDatabaseHas('users', ['email' => 'user3@gmail.com']);
});

it('restrict non admin to update employee', function () {

    $userB = User::factory()->create([
        'name' => 'user4',
        'email' => 'user4@gmail.com',
        'password' => 1234,
        'shift_id' => $this->morningShift->id,
        'profile_image' => UploadedFile::fake()->image('user23.jpg'),
    ]);

    $this->actingAs($this->user, 'sanctum');
    $employeeData = [
        'name' => 'user2',
        'email' => 'user2@gmail.com',
        'password' => 1234,
        'shift_id' => $this->morningShift->id,
        'profile_image' => UploadedFile::fake()->image('user23.jpg'),
        'upload_type' => 'google',
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
        'profile_image' => UploadedFile::fake()->image('user23.jpg'),
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
        'profile_image' => UploadedFile::fake()->image('user23.jpg'),
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
    $this->user->shift()->update(['time_in' => now()->subMinutes(10)]);
    $this->actingAs($this->user, 'sanctum');
    Carbon::setTestNow(now()->addMinutes(15));
    $response = $this->postJson('/api/clock-in');
    $response->assertStatus(200);
    $response->assertJson(['message' => "U've successfully clocked in but You late!"]);
    Mail::assertQueued(AttendanceClockInNotification::class);
    Mail::assertQueued(AdminAttendanceNotification::class);
});

it('can generate attendance excel report', function () {
    $this->actingAs($this->admin);
    Attendance::factory()->create(['date' => Carbon::today()]);
    $response = $this->post(route('generate.reportExcel'));
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->assertHeader('Content-Disposition', 'attachment; filename="attendance_report_' . now()->format('Y_m_d') . '.xlsx"');
});


it('admin can generate an PDF report', function () {
    $this->actingAs($this->admin, 'sanctum');
    Gate::shouldReceive('allows')
        ->with('generateReport', User::class)
        ->andReturn(true);
    $response = $this->postJson(route('generate.reportPdf'));
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
    $content = $response->getContent();
    $this->assertNotEmpty($content, 'PDF content is empty.');
});

it('denies access to generate PDF report for non admins', function () {
    $this->actingAs($this->user, 'sanctum');
    Gate::shouldReceive('allows')
        ->with('generateReport', User::class)
        ->andReturn(false);
    $response = $this->postJson(route('generate.reportPdf'));
    $response->assertStatus(Response::HTTP_FORBIDDEN);
    $response->assertJson(['message' => "U're not allowed to generate attendance report"]);
});


it('generate daily attendance report PDF n Excel via email at the end of day to admin', function () {
    $attendances = Attendance::factory()->count(5)->create(); 
    $todayDate = now()->toDateString();
    $pdf = PDF::loadView('email.daily-report', compact('attendances', 'todayDate'));
    $pdfContent = $pdf->output(); 
    expect($pdfContent)->not()->toBeEmpty();
    $fileNamePdf = 'attendance_report_' . $todayDate . '.pdf';
    $pdfFilePath = 'reports/' . $fileNamePdf;
    Storage::disk('public')->put($pdfFilePath, $pdfContent);
    expect(Storage::disk('public')->exists($pdfFilePath))->toBeTrue();
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Employee');
    $sheet->setCellValue('B1', 'Attendance Date');
    $sheet->setCellValue('C1', 'Status');
    $row = 2;
    foreach ($attendances as $attendance) {
        $sheet->setCellValue('A' . $row, $attendance->user->name);
        $sheet->setCellValue('B' . $row, $attendance->attendance_date);
        $sheet->setCellValue('C' . $row, $attendance->status);
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    ob_start();
    $writer->save('php://output');
    $excelContent = ob_get_clean();
    expect($excelContent)->not()->toBeEmpty();
    $fileNameExcel = 'attendance_report_' . $todayDate . '.xlsx';
    $excelFilePath = 'reports/' . $fileNameExcel;
    Storage::disk('public')->put($excelFilePath, $excelContent);
    expect(Storage::disk('public')->exists($excelFilePath))->toBeTrue();
    $fileUrlPdf = url('storage/' . $pdfFilePath);
    $fileUrlExcel = url('storage/' . $excelFilePath);
    Mail::fake();
    Mail::to($this->admin->email)->send(new AttendanceReportMail($fileUrlPdf, $todayDate, $fileUrlExcel));
    Mail::assertSent(AttendanceReportMail::class, function ($mail) use ($fileUrlPdf, $fileUrlExcel) {
        return $mail->fileUrlPdf === $fileUrlPdf && $mail->fileUrlExcel === $fileUrlExcel;
    });
    Storage::disk('public')->delete([$pdfFilePath, $excelFilePath]);
});
