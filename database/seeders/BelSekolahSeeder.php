<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BelSekolahSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Data Default Settings
        DB::table('settings')->insertOrIgnore([
            [
                'key' => 'jumat_mode',
                'value' => 'jamaah', // Pilihan default: 'jamaah' atau 'ringkas'
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'system_status',
                'value' => 'active', // 'active' atau 'muted'
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ]);

        // 2. Data Master Audio Files (Menyesuaikan file di folder storage/app/public/sounds)
        DB::table('audio_files')->truncate();
        DB::table('audio_files')->insert([
            ['title' => 'Bel Masuk Sekolah', 'filename' => 'bel_masuk.mp3', 'category' => 'utama', 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Bel Istirahat', 'filename' => 'bel_istirahat.mp3', 'category' => 'utama', 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Bel Pulang Sekolah', 'filename' => 'bel_pulang.mp3', 'category' => 'utama', 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Chime Pembuka Pengumuman', 'filename' => 'chime_pembuka.mp3', 'category' => 'pengumuman', 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Chime Penutup Pengumuman', 'filename' => 'chime_penutup.mp3', 'category' => 'pengumuman', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 3. Data Jadwal Bel (Schedules)
        $schedules = [];
        $daysReguler = ['monday', 'tuesday', 'wednesday', 'thursday', 'saturday'];

        // Jadwal Senin - Kamis & Sabtu (Reguler)
        foreach ($daysReguler as $day) {
            $schedules[] = ['day' => $day, 'variant' => 'default', 'time' => '07:00:00', 'event_name' => 'Bel Masuk Sekolah', 'audio_file' => 'bel_masuk.mp3', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
            $schedules[] = ['day' => $day, 'variant' => 'default', 'time' => '10:00:00', 'event_name' => 'Bel Istirahat Pertama', 'audio_file' => 'bel_istirahat.mp3', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
            $schedules[] = ['day' => $day, 'variant' => 'default', 'time' => '10:15:00', 'event_name' => 'Bel Masuk Kelas', 'audio_file' => 'bel_masuk.mp3', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
            $schedules[] = ['day' => $day, 'variant' => 'default', 'time' => '12:00:00', 'event_name' => 'Bel Istirahat Kedua', 'audio_file' => 'bel_istirahat.mp3', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
            $schedules[] = ['day' => $day, 'variant' => 'default', 'time' => '12:30:00', 'event_name' => 'Bel Masuk Kelas', 'audio_file' => 'bel_masuk.mp3', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
            $schedules[] = ['day' => $day, 'variant' => 'default', 'time' => '14:30:00', 'event_name' => 'Bel Pulang Sekolah', 'audio_file' => 'bel_pulang.mp3', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
        }

        // --- PRESET HARI JUMAT ---

        // VARIAN 1: Jumat Jamaah di Sekolah (Pulang Jam 13:00)
        $schedules[] = ['day' => 'friday', 'variant' => 'jumat_jamaah', 'time' => '07:00:00', 'event_name' => 'Bel Masuk Sekolah', 'audio_file' => 'bel_masuk.mp3', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
        $schedules[] = ['day' => 'friday', 'variant' => 'jumat_jamaah', 'time' => '09:30:00', 'event_name' => 'Bel Istirahat', 'audio_file' => 'bel_istirahat.mp3', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
        $schedules[] = ['day' => 'friday', 'variant' => 'jumat_jamaah', 'time' => '11:30:00', 'event_name' => 'Bel Persiapan Shalat Jumat', 'audio_file' => 'bel_jumat_jamaah.mp3', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
        $schedules[] = ['day' => 'friday', 'variant' => 'jumat_jamaah', 'time' => '13:00:00', 'event_name' => 'Bel Pulang Sekolah (Pasca Jamaah)', 'audio_file' => 'bel_pulang.mp3', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];

        // VARIAN 2: Jumat Ringkas / Air Minim (Pulang Jam 11:00)
        $schedules[] = ['day' => 'friday', 'variant' => 'jumat_ringkas', 'time' => '07:00:00', 'event_name' => 'Bel Masuk Sekolah', 'audio_file' => 'bel_masuk.mp3', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
        $schedules[] = ['day' => 'friday', 'variant' => 'jumat_ringkas', 'time' => '09:30:00', 'event_name' => 'Bel Istirahat Ringkas', 'audio_file' => 'bel_istirahat.mp3', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
        $schedules[] = ['day' => 'friday', 'variant' => 'jumat_ringkas', 'time' => '11:00:00', 'event_name' => 'Bel Pulang Sekolah (Air Minim)', 'audio_file' => 'bel_pulang_ringkas.mp3', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];

        DB::table('schedules')->insert($schedules);
    }
}