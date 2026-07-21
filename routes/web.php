<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\backend\aol\webhook;
use App\Http\Controllers\backend\user\HseReportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::post('login-page', [AuthController::class, 'customLogin'])->name('login.custom');
Route::get('login', [AuthController::class, 'index'])->name('login')->middleware("throttle:8,2");
Route::get('login/admin', [AuthController::class, 'AdminLogin'])->name('loginAdmin')->middleware("throttle:8,2");
Route::get('logout', [AuthController::class, 'logout'])->name('logout');

// HSE PROGRAM PEDULI 
Route::post('/login-user', [AuthController::class, 'userLogin'])->name('login.user');
Route::middleware(['cek.login.user', 'ssouser'])->group(function () {
    Route::get('/formreport', [HseReportController::class, 'index']);
    Route::post('/submit-hse-report', [HseReportController::class, 'store']);
    Route::get('/riwayat-pelaporan', [HseReportController::class, 'history']);
});