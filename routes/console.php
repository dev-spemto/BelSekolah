<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Schedule as ScheduleModel;
use Carbon\Carbon;

// Command Bawaan Laravel
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// 1. DAFTARKAN COMMAND AUTO SHUTDOWN
Artisan::command('server:auto-shutdown', function () {
    $now = Carbon::now('Asia/Jakarta');
    $today = strtolower($now->format('l')); // Contoh: 'monday', 'tuesday', 'friday'

    // Cari jadwal bel paling terakhir untuk hari ini
    $lastSchedule = ScheduleModel::where('day', $today)
        ->orderBy('time', 'desc')
        ->first();

    if (!$lastSchedule) {
        return;
    }

    // Hitung waktu toleransi (Waktu Bel Terakhir + 5 Menit)
    $lastScheduleTime = Carbon::createFromFormat('H:i:s', $lastSchedule->time, 'Asia/Jakarta');
    $shutdownTime = $lastScheduleTime->copy()->addMinutes(5);

    // Jika waktu sekarang sudah melewati atau sama dengan waktu shutdown
    if ($now->greaterThanOrEqualTo($shutdownTime)) {
        $this->info('Bel terakhir hari ini telah selesai. Mematikan sistem...');

        // Close aplikasi & background runner
        exec('taskkill /F /IM wscript.exe /T');
        exec('taskkill /F /IM php.exe /T');

        // Shutdown Windows (dengan hitungan mundur 10 detik)
        exec('shutdown /s /f /t 10 /c "Bel Sekolah Otomatis SPEMTO: KBM Hari Ini Selesai. Komputer dimatikan otomatis."');
    }
})->purpose('Mengecek dan mematikan laptop otomatis 5 menit setelah bel terakhir.');


// 2. JALANKAN CHECKER INI SETIAP MENIT
Schedule::command('server:auto-shutdown')->everyMinute();