<?php

use App\Models\User;
use App\Models\Shift;
use App\Models\Attendance;
use Database\Seeders\ShiftSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use App\Services\GoogleDriveService;
use App\Services\DropboxService;


pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\LazilyRefreshDatabase::class)
    ->beforeEach(function () {
        DB::beginTransaction();
        $this->seed(ShiftSeeder::class);
        $this->morningShift = Shift::where('slug', 'morning-shift')->first();
        $this->afternoonShift = Shift::where('slug', 'afternoon-shift')->first();
        $this->admin = User::factory()->admin()->create(['shift_id' => $this->morningShift->id]);
        $this->latest = User::where('userType', 1)->first();
        $this->user = User::factory()->create(['shift_id' => $this->morningShift->id]);
        $this->userAttendance = User::factory()->create(['shift_id' => $this->morningShift->id]);
        $this->userDate=now()->toDateString();
        Attendance::factory()->count(5)->create([
            'date' => $this->userDate,
            'clock_in' => '09:00:00',
            'clock_out' => '16:00:00',
            'user_id' =>$this->userAttendance->id,
        ]);

        //=============Googledrive==============

        $this->google_drive_file=$google_drive_file = UploadedFile::fake()->image('profile.jpg');
        $googleDriveService = mock(GoogleDriveService::class)->shouldReceive('upload')
            ->with($google_drive_file, 'user7')
            ->andReturn(['success' => true, 'file_id' => 'google_file_id', 'file_name' => 'employee-name-profile-image.jpg'])
            ->getMock();
    
        app()->instance(GoogleDriveService::class, $googleDriveService);

        //=============DropBox==============

        $this->dropbox_file=$dropbox_file = UploadedFile::fake()->image('profile.jpg');
        $dropboxService = mock(DropboxService::class)->shouldReceive('upload')
            ->with($dropbox_file, 'user3')
            ->andReturn(['success' => true, 'file_id' => 'profile_images/employee-name-profile-image.jpg', 'file_name' => 'employee-name-profile-image.jpg'])
            ->getMock();

        app()->instance(DropboxService::class, $dropboxService);


    });

afterEach(function () {
    DB::rollBack();
})

    ->in('Feature');


expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

function something()
{
    // ..
}
