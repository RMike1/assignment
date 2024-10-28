<?php

namespace App\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use App\Models\User;
use DateTimeInterface;
use Carbon\Traits\Date;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Notifications\ClockOutNotification;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Mail\AttendanceClockInNotification;
use App\Mail\AttendanceClockOutNotification;
use App\Mail\AdminAttendanceNotification;
use Pdf;

class HomeController extends Controller
{

    // public static function middleware()
    // {
    //     return new Middleware('auth:sanctum',except:['clockIn','clockOut']);
    // }

    public function clockIn(Request $request){
        $today=now()->today();
        $tomorrow=now()->tomorrow();
        $exist=$request->user()->attendances()->whereBetween('clock_in',[$today,$tomorrow])->first();
        if(!$exist)
        {
            $employee=$request->user()->attendances()->create([
                'clock_in'=>now(),
                'date'=>now()->today(),
            ]);
            
            $user_shift=$request->user()->shift()->first();
            if($employee->clock_in > $user_shift->time_in){
                $message="U've successfully clocked in but You late!";
                $username= $request->user()->name;
                $adminmessage= $request->user()->name. "updated has clocked in at ". $employee->clock_in->format('h:i A');
                $admin=User::where('userType',1)->first();
                Mail::to($request->user())->queue(new AttendanceClockInNotification($message,$username));
                Mail::to($admin)->queue(new AdminAttendanceNotification($adminmessage));
                return [
                    'message'=>$message,
                    'data'=>$employee
                ];
            }
            else{
                $message="U've successfully clocked in on time!";
                $adminmessage=$request->user()->name. " has clocked in at ". $employee->clock_in->format('h:i A');
                $username= $request->user()->name;
                $admin=User::where('userType',1)->first();
                Mail::to($request->user())->queue(new AttendanceClockInNotification($message,$username));
                Mail::to($admin)->queue(new AdminAttendanceNotification($adminmessage));
                return [
                    'message'=>$message,
                    'data'=>$employee
                ];

            }
        }
        return ['message'=>'already clocked in!! please wait next day!!'];
    }
    public function clockOut(Request $request){
        $today=now()->today();
        $tomorrow=now()->tomorrow();
        $isAlreadyClockedIn=$request->user()->attendances()->whereBetween('clock_out',[$today,$tomorrow])->first();
        if(!$isAlreadyClockedIn){
                $isTodayAttendancetExist=$request->user()->attendances()->whereBetween('clock_in',[$today,$tomorrow])->first();
                if(!$isTodayAttendancetExist){
                    return [
                        'message'=>"You've not clocked yet, please first clock in!!",
                    ];
                }
                $request->user()->attendances()->where('id',$isTodayAttendancetExist->id)->update([
                    'clock_out'=>now(),
                ]);
                $employee=$request->user()->attendances()->latest()->first();
                $message="You've successfully clocked out!!";
                $adminmessage= $request->user()->name. " has clocked out at ". $employee->clock_out->format('h:i A');
                $admin=User::where('userType',1)->first();
                $username= $request->user()->name;
                Mail::to($request->user())->send(new AttendanceClockOutNotification($message,$username));
                Mail::to($admin)->send(new AdminAttendanceNotification($adminmessage));
                return [
                    'message'=>$message,
                ];
        }
        return ['message'=>'already clocked out!! please wait next day!!'];
    }

    public function attendance(){
        $attendances=Attendance::all();
        return [
            "data"=>$attendances
        ];

    }

    public function generateReport(Request $request){

        $todayDate=Carbon::now()->today();
        $attendances = Attendance::whereDate('date',$todayDate )->get();
        $date = $request->query($todayDate, now()->toDateString()); 

        $pdf = Pdf::loadView('pdf.attendance_report', ['attendances' => $attendances, 'date' => $date]);

        return $pdf->download("attendance_report_{$date}.pdf");
    }

}
