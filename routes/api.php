<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\TimeSheetController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\MasterDataHistoryController;
use App\Http\Controllers\SendNotifController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Semua route wajib menyertakan header X-API-Key
Route::middleware('api.key')->group(function () {

    // LOGIN
    Route::post('loginless', [AuthController::class, 'loginless'])
        ->middleware('throttle:login')
        ->name('loginless');

    // PROTECTED ROUTES - wajib Bearer Token
    // Route::middleware('auth:sanctum')->group(function () {
    //     // tambahkan route yang butuh login di sini
    // });

});





