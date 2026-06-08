<?php

use App\Http\Controllers\ReportExternalTeamController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Fortify;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Fortify::requestPasswordResetLinkView(fn() => view('auth.forgot-password'));
Fortify::resetPasswordView(fn($request) => view('auth.reset-password', ['request' => $request]));

// Home
Route::get('/', function () {
    return auth()->check() ? redirect()->route('report.create') : redirect('/login');
})->name('home');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');
});

// Verify Email
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('report.create')
        ->with('success', 'Email successfully to verify!');
})->middleware(['signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
})->middleware(['throttle:6,1'])->name('verification.send');

// Guard Route
Route::middleware('auth')->group(function () {
    // Report
    Route::get('/', [ReportController::class, 'create'])->name('report.create');
    Route::get('/reports', [ReportController::class, 'index'])->name('report.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('report.export');
    Route::get('/reports/export-count', [ReportController::class,'exportCount'])->name('report.exportCount');
    Route::get('/reports/chart', [ReportController::class, 'chart'])->name('report.chart');
    Route::post('/report', [ReportController::class, 'store'])->name('report.store');
    Route::get('/reportShow/{uuid}', [ReportController::class, 'show'])->name('report.show');
    Route::get('/reports/{uuid}', [ReportController::class, 'edit'])->name('report.edit');
    Route::patch('/reports/{uuid}', [ReportController::class, 'update'])->name('report.update');
    Route::post('/reports/{uuid}/restore', [ReportController::class, 'markRestored'])->name('report.markRestored');
    Route::post('/reports/{uuid}/toggle-handled', [ReportController::class, 'toggleHandled'])->name('report.toggleHandled');
    Route::post('/report-external-teams', [ReportExternalTeamController::class,'store'])->name('report.external.store');
    Route::put('/report-external-teams/{id}', [ReportExternalTeamController::class,'update'])->name('report.external.update');
    Route::delete('/report-external-teams/{id}', [ReportExternalTeamController::class,'destroy'])->name('report.external.destrou');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

// Logout
Route::post('/logout', function () {
    auth()->logout();
    return redirect('/login');
})->name('logout');