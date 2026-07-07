<?php

use App\Http\Controllers\backend\master\GroupMenuModuleController;
use App\Http\Controllers\backend\master\MenuController;
use App\Http\Controllers\backend\master\ModuleController;
use App\Http\Controllers\backend\master\NotificationController;
use App\Http\Controllers\backend\master\RoleController;
use App\Http\Controllers\backend\master\TaskController;
use App\Http\Controllers\backend\master\UserController;
use App\Http\Controllers\UtilityController;
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
//Utility
Route::post('unreadNotification', [UtilityController::class, 'getNotifUnReadCount'])->name('utility.unreadNotification');
Route::get('selectNotification/{id}', [UtilityController::class, 'selectNotification'])->name('utility.selectNotification');

// USER
Route::post('users/datatables', [UserController::class, 'datatables'])->name('users.datatables');
Route::put('users/change-status', [UserController::class, 'changestatus'])->name('users.change-status');
Route::post('users/add-role', [UserController::class, 'addrole'])->name('users.add-role');
Route::post('users/storeToken', [UserController::class, 'storeToken'])->name('storeToken');
Route::resource('users', UserController::class);

// MODULE
Route::resource('modules', ModuleController::class);

//MENU
Route::post('menus/getmoduleactionsinmoduleid', [MenuController::class, 'getmoduleactionsinmoduleid'])->name('menus.getmoduleactionsinmoduleid');
Route::post('menus/getmenudata', [MenuController::class, 'getmenudata'])->name('menus.getmenudata');
Route::post('menus/savemenu', [MenuController::class, 'savemenu'])->name('menus.savemenu');
Route::get('menus', [MenuController::class, 'index'])->name('menus.index');

//GROUPMENU MODULE
Route::resource('groupmenumodules', GroupMenuModuleController::class);

//ROLE
Route::resource('roles', RoleController::class);

//TASK
Route::post('task/taskdatatables', [TaskController::class, 'taskdatatables'])->name('task.taskdatatables');
Route::resource('task', TaskController::class);

//NOTIFICATION
Route::post('notification/readdatatables', [NotificationController::class, 'readdatatables'])->name('notification.readdatatables');
Route::post('notification/unreaddatatables', [NotificationController::class, 'unreaddatatables'])->name('notification.unreaddatatables');
Route::resource('notification', NotificationController::class);


