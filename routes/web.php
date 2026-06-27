<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Employee\AssetController;
use App\Http\Controllers\Employee\AttendanceController;
use App\Http\Controllers\Employee\HolidayController;
use App\Http\Controllers\Employee\InboxController;
use App\Http\Controllers\Employee\LeaveController;
use App\Http\Controllers\Employee\MeetingController;
use App\Http\Controllers\Employee\NoticeController;
use App\Http\Controllers\Employee\PayrollController;
use App\Http\Controllers\Employee\ProfileController;
use App\Http\Controllers\Employee\ProjectController;
use App\Http\Controllers\Employee\ResignationController;
use App\Http\Controllers\Employee\TadaController;
use App\Http\Controllers\Employee\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::get('/auth/login', fn () => redirect()->route('login'))->name('auth.index');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('auth.login');
    Route::post('/login/store', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('auth.login.legacy');

    Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
        ->middleware('throttle:3,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'resetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'updatePassword'])
        ->middleware('throttle:5,1')
        ->name('password.update');
});

Route::middleware('auth')->group(function (): void {
     Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/status', [AttendanceController::class, 'status'])->name('attendance.status');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');

    Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::post('/leave/request', [LeaveController::class, 'store'])->name('leave.store');
    Route::post('/leave/time-leave', [LeaveController::class, 'storeTimeLeave'])->name('leave.time-leave.store');

    Route::get('/tada', [TadaController::class, 'index'])->name('tada.index');
    Route::post('/tada', [TadaController::class, 'store'])->name('tada.store');

    Route::get('/team-sheet', [TeamController::class, 'index'])->name('team.index');
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
    Route::get('/notices', [NoticeController::class, 'index'])->name('notices.index');
    Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/settings', [ProfileController::class, 'show'])->name('settings.index');

    Route::get('/resignation', [ResignationController::class, 'index'])->name('resignation.index');
    Route::post('/resignation', [ResignationController::class, 'store'])->name('resignation.store');

    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});



