<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;
use Carbon\Traits\Date;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ClockInNotification;
use App\Notifications\ClockOutNotification;
use App\Notifications\AdminAttendanceNotification;
use DateTime;
use DateTimeInterface;

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
                $adminmessage= $request->user()->name. " has clocked in at ". $employee->clock_in->format('h:i A');
                $admin=User::where('userType',1)->first();
                Notification::send($request->user(), new ClockInNotification($message));
                Notification::send($admin, new AdminAttendanceNotification($adminmessage));
                return [
                    'message'=>$message,
                    'data'=>$employee
                ];
            }
            else{
                $message="U've successfully clocked in but You late!";
                $adminmessage= $request->user()->name. " has clocked in at ". $employee->clock_in->format('h:i A');
                $admin=User::where('userType',1)->first();
                Notification::send($request->user(), new ClockInNotification($message));
                Notification::send($admin, new AdminAttendanceNotification($adminmessage));
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
                Notification::send($request->user(), new ClockOutNotification($message));
                Notification::send($admin, new AdminAttendanceNotification($adminmessage));
                return [
                    'message'=>$message,
                ];
        }
        return ['message'=>'already clocked out!! please wait next day!!'];
    }

    public function attendance(){
        $attendances=Attendance::all();

        // foreach($attendances as $attendance){
        //     $data=[
        //         "date"=>$attendance->date,
        //         "clock_in"=>$attendance->clock_in,
        //         "clock_out"=>$attendance->clock_out,
        //         "user_id"=>$attendance->user->name
        //     ];
        // }
        return [
            "data"=>$attendances
        ];

    }


}
