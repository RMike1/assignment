<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::apiResource('employee',HomeController::class);


Route::post('login',[AuthController::class,'login']);

Route::get('login',[AuthController::class,'login'])->name('login');

Route::post('logout',[AuthController::class,'logout'])->middleware('auth:sanctum');

Route::apiResource('employee',AuthController::class);

Route::apiResource('user',AuthController::class);

Route::post('clock-in',[HomeController::class,'clockIn'])->middleware(['auth:sanctum']);

Route::post('clock-out',[HomeController::class,'clockOut'])->middleware(['auth:sanctum']);

Route::post('add-employee',[AuthController::class,'add_employee'])->middleware(['auth:sanctum']);

Route::post('update-employee/{user}',[AuthController::class,'update_employee'])->middleware(['auth:sanctum']);