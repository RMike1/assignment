<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::apiResource('employee',HomeController::class);



Route::post('register',[AuthController::class,'register']);

Route::post('login',[AuthController::class,'login']);

Route::post('logout',[AuthController::class,'logout'])->middleware('auth:sanctum');

Route::post('/add-employee',[AuthController::class,'addEmployee'])->middleware('auth:sanctum');

Route::apiResource('user',AuthController::class);