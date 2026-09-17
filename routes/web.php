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

// Route Save & Load Config
Route::get('/schedules/export', [MainController::class, 'exportConfig'])->name('schedules.export');
Route::post('/schedules/import', [MainController::class, 'importConfig'])->name('schedules.import');

// Route Edit Jadwal
Route::put('/schedules/{id}', [MainController::class, 'updateSchedule'])->name('schedules.update');
Route::post('/schedules/{id}/duplicate', [MainController::class, 'duplicateSchedule'])->name('schedules.duplicate');
Route::post('/schedules/copy-day', [MainController::class, 'copyDaySchedule'])->name('schedules.copyDay');

// Route Print
Route::get('/schedules/print', function (\Illuminate\Http\Request $request) {
    $activeMode = $request->query('mode', session('active_mode', 'regular'));
    
    $modeLabels = [
        'regular' => 'Jadwal Reguler',
        'puasa'   => 'Jadwal Bulan Puasa (Ramadhan)',
        'asts'    => 'Jadwal ASTS (UTS)',
        'asas'    => 'Jadwal ASAS (UAS)',
        'ujian_sekolah' => 'Jadwal Ujian Sekolah',
    ];

    $modeTitle = $modeLabels[$activeMode] ?? 'Jadwal Reguler';
    $allSchedules = \App\Models\Schedule::orderBy('time', 'asc')->get();

    return view('print', compact('allSchedules', 'activeMode', 'modeTitle'));
})->name('schedules.print');