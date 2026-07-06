<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\backend\aol\webhook;
use Illuminate\Support\Facades\Route;

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
    return view('login');
});

Route::post('login-page', [AuthController::class, 'customLogin'])->name('login.custom');
// Route::post('webhook-receiving-aol', [webhook::class, 'webhookHandler']);
Route::get('login', [AuthController::class, 'index'])->name('login')->middleware("throttle:8,2");
Route::get('login/admin', [AuthController::class, 'AdminLogin'])->name('loginAdmin')->middleware("throttle:8,2");
Route::get('logout', [AuthController::class, 'logout'])->name('logout');