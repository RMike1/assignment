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
use DateTime;
use DateTimeInterface;

class HomeController extends Controller
{

    // public static function middleware()
    // {
    //     return new Middleware('auth:sanctum');
    // }

    public function index()
    {
        return User::all();
    }

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
            return [
                'message'=>"You've successfully clocked in",
                'data'=>$employee,
            ];
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
                return [
                    'message'=>"You've successfully clocked out!!",
                ];
        }
        return ['message'=>'already clocked out!! please wait next day!!'];
    }

    public function store(Request $request)
    {
        $validated=Validator::make($request->all(),[
        // $validated=$request->validate([
            'name'=>'required',
            'email'=>'required',
            'password'=>'required',
        ]);
        if($validated->fails()){
            $message=$validated->messages();
            return response()->json([
                'message'=>$message
            ]);
        }

        User::create($validated);
        return response()->json([
           $validated
        ]);
    }
}
