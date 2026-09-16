<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;

Route::get('/', [MainController::class, 'index'])->name('dashboard');
Route::get('/api/next-schedule', [MainController::class, 'getNextSchedule'])->name('api.next-schedule');

Route::post('/api/active-mode', [MainController::class, 'toggleActiveMode'])->name('api.active-mode');
Route::post('/api/jumat-mode', [MainController::class, 'toggleJumatMode'])->name('api.jumat-mode');
Route::post('/api/system-status', [MainController::class, 'toggleSystemStatus'])->name('api.system-status');

// Route CRUD & Batch Delete Jadwal
Route::post('/schedules', [MainController::class, 'storeSchedule'])->name('schedules.store');
Route::delete('/schedules/batch', [MainController::class, 'destroyBatch'])->name('schedules.destroyBatch');
Route::delete('/schedules/{id}', [MainController::class, 'destroySchedule'])->name('schedules.destroy');