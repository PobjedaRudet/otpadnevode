<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\InstrumentsController;
use App\Http\Controllers\RecordController;

Route::get('/', function () {
    return view('welcome');
});

// Guest (not authenticated) routes
Route::middleware('guest')->group(function () {
    // Javna registracija ukinuta – samo admin može kreirati korisnika.
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
// Admin panel routes (uključuje kreiranje novih korisnika)
Route::middleware(['auth', 'isadmin'])->group(function () {
    Route::get('/admin', [\App\Http\Controllers\AdminController::class, 'index'])->name('admin.panel');
    Route::post('/admin/user/{id}/role', [\App\Http\Controllers\AdminController::class, 'updateRole'])->name('admin.user.role');

    // Registracija dostupna samo adminu
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('haspermission:dashboard');
    Route::get('/companies', [CompaniesController::class, 'index'])->name('companies.index')->middleware('haspermission:companies');
    Route::get('/instruments', [InstrumentsController::class, 'index'])->name('instruments.index')->middleware('haspermission:instruments');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index')->middleware('haspermission:reports');
    Route::get('/reports/summary', [ReportController::class, 'summary'])->name('reports.summary')->middleware('haspermission:summary');
    Route::get('/reports/instrument-summary', [ReportController::class, 'instrumentSummary'])->name('reports.instrumentSummary')->middleware('haspermission:instrumentSummary');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/records-lazy', [RecordController::class, 'lazy'])->name('records.lazy');
});



