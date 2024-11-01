<?php

use App\Models\User;
use App\Models\Shift;
use App\Models\Attendance;
use Database\Seeders\ShiftSeeder;
use Illuminate\Support\Facades\DB;


pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\LazilyRefreshDatabase::class)
    ->beforeEach(function () {
        DB::beginTransaction();
        $this->seed(ShiftSeeder::class);
        $this->morningShift = Shift::where('slug', 'morning-shift')->first();
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
