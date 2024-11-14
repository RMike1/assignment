<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Mail\AttendanceReportMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withSchedule(function(Schedule $schedule){
        $schedule->call(function () {
            $todayDate = Carbon::today();
            $pdfTodayReport=$todayDate->format('Y-m-d');
            $attendances = Attendance::whereDate('date', $todayDate)->get();
            $pdf = PDF::loadView('report.attendance_report', compact('attendances', 'todayDate'));

            $fileName = 'attendance_report_' . $todayDate->format('Y_m_d_H_i_s') . '.pdf';
            $pdfFilePath = storage_path('app/public/reports/' . $fileName);

            $fileUrlPdf = url('storage/reports/' . $fileName);
            $pdf->save($pdfFilePath);


            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Employee Name')
                  ->setCellValue('B1', 'Clock In Time')
                  ->setCellValue('C1', 'Clock Out Time')
                  ->setCellValue('D1', 'Status');
        
            $row = 2;
            foreach ($attendances as $attendance) {
                $sheet->setCellValue("A$row", $attendance->user->name);
                $sheet->setCellValue("B$row", Carbon::parse($attendance->clock_in)->format('h:i A'));
                $sheet->setCellValue("C$row", Carbon::parse($attendance->clock_out)->format('h:i A'));
                $sheet->setCellValue("D$row", $attendance->user->shift->time_in < $attendance->clock_in ? 'Late' : 'On time');
                $row++;
            }
        
            $excelFileName = 'attendance_report_' . $todayDate->format('Y_m_d_H_i_s') . '.xlsx';
            $excelFilePath = storage_path('app/public/reports/' . $excelFileName);
            $writer = new Xlsx($spreadsheet);
            $writer->save($excelFilePath);

            $fileUrlExcel = url('storage/reports/' . $excelFileName);

            $admin = User::where('userType', 1)->first(); 
            Mail::to($admin->email)->send(new AttendanceReportMail($fileUrlPdf, $pdfTodayReport, $fileUrlExcel));  
        
        })->dailyAt('23:59');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
