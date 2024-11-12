<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UploadController;


 // Route::get('/attendance-report', [HomeController::class, 'generateReport']);

 // Route::get('generate-reportxlsx', [HomeController::class, 'generateReportExcel'])->name('generate.reportExcel');


Route::post('upload-profile-image-on-google', [UploadController::class, 'uploadOnGoogle'])->name('upload.profile.google');

// Route::post('upload-profile-on-google', [UploadController::class, 'uploadOnGoogle'])->name('upload.profile.google');

Route::get('upload-profile-on-google', [UploadController::class, 'getUploadOnGoogle'])->name('upload.google');
