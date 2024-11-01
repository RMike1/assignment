<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthController;


Route::post('login',[AuthController::class,'login'])->name('login');


Route::middleware(['auth:sanctum'])->group(function(){
    
    Route::post('logout',[AuthController::class,'logout']);
    
    Route::get('all-employess',[AuthController::class,'all_employess']);
    
    Route::post('add-employee',[AuthController::class,'add_employee']);
    
    Route::put('update-employee/{userId}',[AuthController::class,'update_employee']);
    
    Route::delete('delete-employee/{userId}',[AuthController::class,'delete_employee']);
    
    Route::post('clock-in',[HomeController::class,'clockIn'])->name('clock-in');
    
    Route::post('clock-out',[HomeController::class,'clockOut']);
    
    Route::get('attendance',[HomeController::class,'attendance'])->name('attendance');
    
    Route::post('/forgot-password', [NewPasswordController::class, 'forgotPassword'])->name('forgot.password')->middleware('auth:sanctum');
    
    Route::post('attendance-report', [HomeController::class, 'generateReport'])->name('generate.reportPdf');
    
});



Route::post('reset-password', [NewPasswordController::class, 'reset'])->name('reset.password');



