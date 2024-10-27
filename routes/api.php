<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthController;


Route::post('login',[AuthController::class,'login'])->name('login');


Route::middleware(['auth:sanctum'])->group(function(){
    
    Route::post('logout',[AuthController::class,'logout']);
    
    Route::get('all-employess',[AuthController::class,'all_employess']);
    
    Route::post('add-employee',[AuthController::class,'add_employee']);
    
    Route::put('update-employee/{user}',[AuthController::class,'update_employee']);
    
    Route::delete('delete-employee/{user}',[AuthController::class,'delete_employee']);
    
    Route::post('clock-in',[HomeController::class,'clockIn']);
    
    Route::post('clock-out',[HomeController::class,'clockOut']);
    
    Route::get('attendance',[HomeController::class,'attendance']);

});






