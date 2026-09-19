<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use Illuminate\Support\Facades\File;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path ke file JSON default
        $jsonPath = database_path('seeders/default_schedules.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("File default_schedules.json tidak ditemukan!");
            return;
        }

        $jsonContent = File::get($jsonPath);
        $data = json_decode($jsonContent, true);

        if (isset($data['schedules']) && is_array($data['schedules'])) {
            // Kosongkan tabel jadwal terlebih dahulu
            Schedule::truncate();

            // Insert semua data jadwal dari JSON
            foreach ($data['schedules'] as $item) {
                Schedule::create([
                    'day'        => strtolower($item['day']),
                    'variant'    => strtolower($item['variant']),
                    'time'       => $item['time'],
                    'event_name' => $item['event_name'],
                    'audio_file' => $item['audio_file'],
                    'is_active'  => $item['is_active'] ?? true,
                ]);
            }

            $this->command->info('Jadwal default SPEMTO berhasil dimuat dari JSON!');
        }
    }
}