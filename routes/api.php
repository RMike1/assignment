<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthController;

Route::get('all-employess',[AuthController::class,'all_employess'])->middleware(['auth:sanctum']);

Route::post('login',[AuthController::class,'login'])->name('login');

Route::post('logout',[AuthController::class,'logout'])->middleware('auth:sanctum');

Route::post('add-employee',[AuthController::class,'add_employee'])->middleware(['auth:sanctum']);

Route::put('update-employee/{user}',[AuthController::class,'update_employee'])->middleware(['auth:sanctum']);

Route::delete('delete-employee/{user}',[AuthController::class,'delete_employee'])->middleware(['auth:sanctum']);

Route::post('clock-in',[HomeController::class,'clockIn'])->middleware(['auth:sanctum']);

Route::post('clock-out',[HomeController::class,'clockOut'])->middleware(['auth:sanctum']);

Route::get('attendance',[HomeController::class,'attendance'])->middleware(['auth:sanctum']);



