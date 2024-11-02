<?php

namespace App\Http\Controllers;

use PDF;
use DateTime;
use Carbon\Carbon;
use App\Models\User;
use DateTimeInterface;
use Carbon\Traits\Date;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Barryvdh\Snappy\Facades\SnappyPdf;
use App\Mail\AdminAttendanceNotification;
use Illuminate\Support\Facades\Validator;
use App\Mail\AttendanceClockInNotification;
use App\Notifications\ClockOutNotification;
use App\Mail\AttendanceClockOutNotification;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{

    // public static function middleware()
    // {
    //     return new Middleware('auth:sanctum',except:['clockIn','clockOut']);
    // }

    public function clockIn(Request $request)
    {
        $today = now()->today();
        $tomorrow = now()->tomorrow();
        $exist = $request->user()->attendances()->whereBetween('clock_in', [$today, $tomorrow])->first();
        if (!$exist) {
            $employee = $request->user()->attendances()->create([
                'clock_in' => now(),
                'date' => now()->today(),
            ]);

            $user_shift = $request->user()->shift()->first();
            if ($employee->clock_in > $user_shift->time_in) {
                $message = "U've successfully clocked in but You late!";
                $username = $request->user()->name;
                $adminmessage = $request->user()->name . "updated has clocked in at " . $employee->clock_in->format('h:i A');
                $admin = User::where('userType', 1)->first();
                Mail::to($request->user())->queue(new AttendanceClockInNotification($message, $username));
                Mail::to($admin)->queue(new AdminAttendanceNotification($adminmessage));
                return response()->json([
                    'message' => $message,
                    'data' => $employee
                ]);
            } else {
                $message = "U've successfully clocked in on time!";
                $adminmessage = $request->user()->name . " has clocked in at " . $employee->clock_in->format('h:i A');
                $username = $request->user()->name;
                $admin = User::where('userType', 1)->first();
                Mail::to($request->user())->queue(new AttendanceClockInNotification($message, $username));
                Mail::to($admin)->queue(new AdminAttendanceNotification($adminmessage));
                return response()->json([
                    'message' => $message,
                    'data' => $employee
                ]);
            }
        }
        return ['message' => 'already clocked in!! please wait next day!!'];
    }
    public function clockOut(Request $request)
    {
        $today = now()->today();
        $tomorrow = now()->tomorrow();
        $isAlreadyClockedIn = $request->user()->attendances()->whereBetween('clock_out', [$today, $tomorrow])->first();
        if (!$isAlreadyClockedIn) {
            $isTodayAttendancetExist = $request->user()->attendances()->whereBetween('clock_in', [$today, $tomorrow])->first();
            if (!$isTodayAttendancetExist) {
                return response()->json([
                    'message' => "You've not clocked yet, please first clock in!!",
                ]);
            }
            $request->user()->attendances()->where('id', $isTodayAttendancetExist->id)->update([
                'clock_out' => now(),
            ]);
            $employee = $request->user()->attendances()->latest()->first();
            $message = "You've successfully clocked out!!";
            $adminmessage = $request->user()->name . " has clocked out at " . $employee->clock_out->format('h:i A');
            $admin = User::where('userType', 1)->first();
            $username = $request->user()->name;
            Mail::to($request->user())->send(new AttendanceClockOutNotification($message, $username));
            Mail::to($admin)->send(new AdminAttendanceNotification($adminmessage));
            return response()->json([
                'message' => $message,
            ]);
        }
        return ['message' => 'already clocked out!! please wait next day!!'];
    }

    public function attendance()
    {
        if (Gate::allows('accessAttendance', User::class)) {
            $attendances = Attendance::all();
            return response()->json([
                "data" => $attendances
            ]);
        }
        return response()->json([
            'message' => "U have not access to check attendance list"
        ], Response::HTTP_FORBIDDEN);
    }

    public function generateReport(Request $request)

    {
        if (Gate::allows('generateReport', User::class)) {
            $todayDate = Carbon::today();
            $attendances = Attendance::whereDate('date', $todayDate)->get();
            $pdf = PDF::loadView('report.attendance_report', compact('attendances', 'todayDate'));
            return $pdf->inline();
        }
        return response()->json([
            'message' => "U're not allowed to generate attendance report"
        ], Response::HTTP_FORBIDDEN);
    }

    //=================CSV====================

    // public function generateReportExcel()
    // {
    //     $todayDate = Carbon::today();
    //     $attendances = Attendance::whereDate('date', $todayDate)->get();
    //     $csvData = "Employee Name,Clock In Time,Clock Out Time,Rep\n";

    //     foreach ($attendances as $attendance) {
    //         $employeeName = $attendance->user->name;
    //         $clockInTime = Carbon::parse($attendance->clock_in)->format('h:i A');
    //         $clockOutTime = Carbon::parse($attendance->clock_out)->format('h:i A');
    //         $status = $attendance->user->shift->time_in < $attendance->clock_in ? 'Late' : 'On time';
    //         $csvData .= "$employeeName,$clockInTime,$clockOutTime,$status\n";
    //     }
    //     $fileName = "attendance_report_" . $todayDate->format('Y_m_d') . ".csv";
    //     return response($csvData, 200, [
    //         'Content-Type' => 'text/csv',
    //         'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
    //     ]);
    // }

    //=================XLSX====================
    public function generateReportExcel()
    {
        if (Gate::allows('generateReport', User::class)) {
            $todayDate = Carbon::today();
            $attendances = Attendance::whereDate('date', $todayDate)->get();
            if ($attendances->isEmpty()) {
                return response()->json(['message' => 'No attendance records found for today.'], Response::HTTP_NO_CONTENT);
            }
            $spreadsheet = new Spreadsheet();
            $activeWorksheet = $spreadsheet->getActiveSheet();
            $activeWorksheet->setCellValue('A1', 'Employee Name');
            $activeWorksheet->setCellValue('B1', 'Clock In Time');
            $activeWorksheet->setCellValue('C1', 'Clock Out Time');
            $activeWorksheet->setCellValue('D1', 'Status')->getDefaultColumnDimension()->setWidth(30);

            $row = 2;
            foreach ($attendances as $attendance) {
                $activeWorksheet->setCellValue("A$row", $attendance->user->name);
                $activeWorksheet->setCellValue("B$row", Carbon::parse($attendance->clock_in)->format('h:i A'));
                $activeWorksheet->setCellValue("C$row", Carbon::parse($attendance->clock_out)->format('h:i A'));
                $activeWorksheet->setCellValue("D$row", $attendance->user->shift->time_in < $attendance->clock_in ? 'Late' : 'On time');
                $row++;
            }
            $fileName = "attendance_report_" . $todayDate->format('Y_m_d') . ".xlsx";
            return response()->stream(function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'max-age=0',
            ]);
        }

        return response()->json(['message' => "U're not allowed to generate attendance report"], Response::HTTP_FORBIDDEN);
    }
}
