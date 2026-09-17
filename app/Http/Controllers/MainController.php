<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Setting;
use App\Models\AudioFile;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class MainController extends Controller
{
    public function index()
    {
        $today = strtolower(Carbon::now()->format('l'));
        $activeMode = Setting::get('active_mode', 'regular');
        $jumatMode = Setting::get('jumat_mode', 'jamaah');
        $systemStatus = Setting::get('system_status', 'active');

        if ($activeMode !== 'regular') {
            $variant = $activeMode;
        } else {
            if ($today === 'friday') {
                $variant = ($jumatMode === 'jamaah') ? 'jumat_jamaah' : 'jumat_ringkas';
            } else {
                $variant = 'default';
            }
        }

        $schedules = Schedule::where('day', $today)
            ->where('variant', $variant)
            ->where('is_active', true)
            ->orderBy('time', 'asc')
            ->get();

        // URUTAN HARI LOGIS: Senin (1) -> Selasa (2) -> Rabu (3) -> Kamis (4) -> Jumat (5) -> Sabtu (6) -> Minggu (7)
        $dayOrder = "CASE day 
            WHEN 'monday' THEN 1 
            WHEN 'tuesday' THEN 2 
            WHEN 'wednesday' THEN 3 
            WHEN 'thursday' THEN 4 
            WHEN 'friday' THEN 5 
            WHEN 'saturday' THEN 6 
            WHEN 'sunday' THEN 7 
            ELSE 8 END";

        $allSchedules = Schedule::orderByRaw($dayOrder)
            ->orderBy('time', 'asc')
            ->orderBy('variant', 'asc')
            ->get();

        $availableAudioFiles = collect(Storage::disk('public')->files('sounds'))
            ->filter(fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'mp3')
            ->map(fn($file) => basename($file))
            ->values();

        $manualAudioList = AudioFile::all();

        return view('dashboard', compact(
            'today',
            'activeMode',
            'jumatMode',
            'systemStatus',
            'schedules',
            'allSchedules',
            'availableAudioFiles',
            'manualAudioList'
        ));
    }

    public function getNextSchedule()
    {
        $now = Carbon::now();
        $today = strtolower($now->format('l'));
        $currentTime = $now->format('H:i:s');
        $todayDate = $now->format('Y-m-d');
        $activeMode = Setting::get('active_mode', 'regular');
        $jumatMode = Setting::get('jumat_mode', 'jamaah');

        // --- 1. LOGIKA ROTASI AUDIO 5S PAGI (06:15 - 06:45 WIB) ---
        $audio5sToday = null;
        $is5sActive = false;

        if (in_array($today, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'])) {
            $savedDate = Setting::get('5s_assigned_date');
            
            if ($savedDate !== $todayDate) {
                $assignedAudio = $this->pickRandom5sAudio();
                Setting::set('5s_today_audio', $assignedAudio);
                Setting::set('5s_assigned_date', $todayDate);
            }

            $audio5sToday = Setting::get('5s_today_audio');

            if ($currentTime >= '06:15:00' && $currentTime < '06:45:00') {
                $is5sActive = true;
            }
        }

        // --- 2. LOGIKA JADWAL BEL REGULER & INDONESIA RAYA ---
        $query = Schedule::where('day', $today)->where('is_active', true);

        if ($activeMode !== 'regular') {
            $query->where('variant', $activeMode);
        } else {
            if ($today === 'friday') {
                $query->where('variant', ($jumatMode === 'jamaah') ? 'jumat_jamaah' : 'jumat_ringkas');
            } else {
                $query->where('variant', 'default');
            }
        }

        $todaySchedules = $query->orderBy('time', 'asc')->get();

        // Injeksi jadwal Lagu Indonesia Raya (Jam 10:00 WIB) HANYA untuk Mode REGULER
        $hasIndonesiaRaya = $todaySchedules->contains(fn($item) => Carbon::parse($item->time)->format('H:i:s') === '10:00:00');
        if (!$hasIndonesiaRaya && $activeMode === 'regular' && in_array($today, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'])) {
            $autoIndonesiaRaya = new Schedule([
                'id' => 9999,
                'day' => $today,
                'variant' => 'auto',
                'time' => '10:00:00',
                'event_name' => '🇮🇩 Menyanyikan Lagu Indonesia Raya',
                'audio_file' => 'indonesia_raya.mp3',
                'is_active' => true
            ]);
            $todaySchedules->push($autoIndonesiaRaya);
            $todaySchedules = $todaySchedules->sortBy(fn($item) => Carbon::parse($item->time)->format('H:i:s'))->values();
        }

        // --- 3. ROTASI AUTOMATIS AUDIO UNTUK BEL ISTIRAHAT DAN PULANG ---
        foreach ($todaySchedules as $schedule) {
            $audioLower = strtolower($schedule->audio_file);
            
            if ($audioLower === 'bel_istirahat.mp3' || stristr($schedule->event_name, 'istirahat')) {
                $schedule->audio_file = $this->pickRandomNationalAudio('istirahat');
            }
            elseif ($audioLower === 'bel_pulang.mp3' || stristr($schedule->event_name, 'pulang')) {
                $schedule->audio_file = $this->pickRandomNationalAudio('pulang');
            }
        }

        // --- 4. PENENTUAN JADWAL BERIKUTNYA SECARA PRESISI ---
        $nextSchedule = $todaySchedules->first(function ($item) use ($currentTime) {
            $itemTime = Carbon::parse($item->time)->format('H:i:s');
            return $itemTime > $currentTime;
        });

        return response()->json([
            'current_time' => $currentTime,
            'next_schedule' => $nextSchedule,
            'today_schedules' => $todaySchedules,
            'active_mode' => $activeMode,
            'jumat_mode' => $jumatMode,
            'system_status' => Setting::get('system_status', 'active'),
            'audio_5s' => [
                'active' => $is5sActive,
                'file' => $audio5sToday,
                'start_time' => '06:15:00',
                'end_time' => '06:45:00'
            ]
        ]);
    }

    private function pickRandom5sAudio()
    {
        $allPlaylist = [
            'arrahman.mp3',
            'alwaqiah.mp3',
            'asmaul_husna.mp3',
            'alkahfi.mp3',
            'asy_syams.mp3',
            'al_mulk.mp3'
        ];

        $playedHistory = json_decode(Setting::get('5s_played_history', '[]'), true);
        $remaining = array_values(array_diff($allPlaylist, $playedHistory));

        if (empty($remaining)) {
            $playedHistory = [];
            $remaining = $allPlaylist;
        }

        $selected = $remaining[array_rand($remaining)];
        $playedHistory[] = $selected;
        Setting::set('5s_played_history', json_encode($playedHistory));

        return $selected;
    }

    private function pickRandomNationalAudio($type = 'istirahat')
    {
        $allPlaylist = [
            'garuda_pancasila.mp3',
            'gundul_pacul.mp3',
            'halo_halo_bandung.mp3',
            'suwe_ora_jamu.mp3',
            'berkibarlah_benderaku.mp3',
            'maju_tak_gentar.mp3',
            'ampar_ampar_pisang.mp3',
            'manuk_dadali.mp3',
            'rayuan_pulau_kelapa.mp3',
            'kicir_kicir.mp3'
        ];

        $settingKeyDate = "national_{$type}_assigned_date";
        $settingKeyAudio = "national_{$type}_today_audio";
        $settingKeyHistory = "national_{$type}_played_history";

        $todayDate = Carbon::now()->format('Y-m-d');
        $savedDate = Setting::get($settingKeyDate);

        if ($savedDate !== $todayDate || !Setting::get($settingKeyAudio)) {
            $playedHistory = json_decode(Setting::get($settingKeyHistory, '[]'), true);
            $remaining = array_values(array_diff($allPlaylist, $playedHistory));

            if (empty($remaining)) {
                $playedHistory = [];
                $remaining = $allPlaylist;
            }

            $selected = $remaining[array_rand($remaining)];
            $playedHistory[] = $selected;

            Setting::set($settingKeyAudio, $selected);
            Setting::set($settingKeyDate, $todayDate);
            Setting::set($settingKeyHistory, json_encode($playedHistory));
        }

        return Setting::get($settingKeyAudio);
    }

    // === METHOD SAVE / EXPORT CONFIGURATION (DOWNLOAD JSON) ===
    public function exportConfig()
    {
        $schedules = Schedule::all(['day', 'variant', 'time', 'event_name', 'audio_file', 'is_active']);
        $settings = [
            'active_mode' => Setting::get('active_mode', 'regular'),
            'jumat_mode' => Setting::get('jumat_mode', 'jamaah'),
        ];

        $exportData = [
            'app' => 'Bel Sekolah Otomatis SPEMTO',
            'version' => '1.0',
            'exported_at' => Carbon::now()->toDateTimeString(),
            'settings' => $settings,
            'schedules' => $schedules
        ];

        $fileName = 'backup_jadwal_spemto_' . Carbon::now()->format('Y-m-d_His') . '.json';

        return response()->streamDownload(function () use ($exportData) {
            echo json_encode($exportData, JSON_PRETTY_PRINT);
        }, $fileName, ['Content-Type' => 'application/json']);
    }

    // === METHOD LOAD / IMPORT CONFIGURATION (UPLOAD JSON) ===
    public function importConfig(Request $request)
    {
        $request->validate([
            'config_file' => 'required|file|mimes:json,txt|max:2048'
        ]);

        $content = file_get_contents($request->file('config_file')->getRealPath());
        $data = json_decode($content, true);

        if (!$data || !isset($data['schedules'])) {
            return redirect()->back()->with('error', 'Format file backup JSON tidak valid!');
        }

        if (isset($data['settings']['active_mode'])) {
            Setting::set('active_mode', $data['settings']['active_mode']);
        }
        if (isset($data['settings']['jumat_mode'])) {
            Setting::set('jumat_mode', $data['settings']['jumat_mode']);
        }

        Schedule::truncate();

        foreach ($data['schedules'] as $item) {
            Schedule::create([
                'day' => $item['day'],
                'variant' => $item['variant'],
                'time' => $item['time'],
                'event_name' => $item['event_name'],
                'audio_file' => $item['audio_file'],
                'is_active' => $item['is_active'] ?? true,
            ]);
        }

        return redirect()->back()->with('success', 'Berhasil memuat konfigurasi! Seluruh jadwal bel telah dipulihkan.');
    }

    public function toggleActiveMode(Request $request)
    {
        $request->validate(['mode' => 'required|in:regular,puasa,asts,asas,ujian_sekolah']);
        Setting::set('active_mode', $request->mode);
        return response()->json(['status' => 'success', 'mode' => $request->mode]);
    }

    public function toggleJumatMode(Request $request)
    {
        $request->validate(['mode' => 'required|in:jamaah,ringkas']);
        Setting::set('jumat_mode', $request->mode);
        return response()->json(['status' => 'success', 'mode' => $request->mode]);
    }

    public function toggleSystemStatus(Request $request)
    {
        $current = Setting::get('system_status', 'active');
        $newStatus = ($current === 'active') ? 'muted' : 'active';
        Setting::set('system_status', $newStatus);
        return response()->json(['status' => 'success', 'system_status' => $newStatus]);
    }

    public function storeSchedule(Request $request)
    {
        $request->validate([
            'day' => 'required',
            'variant' => 'required',
            'time' => 'required',
            'event_name' => 'required',
            'audio_file' => 'required',
        ]);

        Schedule::create([
            'day' => $request->day,
            'variant' => $request->variant,
            'time' => $request->time,
            'event_name' => $request->event_name,
            'audio_file' => $request->audio_file,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Jadwal bel berhasil ditambahkan!');
    }

    public function updateSchedule(Request $request, $id)
    {
        $request->validate([
            'day' => 'required',
            'variant' => 'required',
            'time' => 'required',
            'event_name' => 'required',
            'audio_file' => 'required',
        ]);

        $schedule = Schedule::findOrFail($id);
        $schedule->update([
            'day' => $request->day,
            'variant' => $request->variant,
            'time' => $request->time,
            'event_name' => $request->event_name,
            'audio_file' => $request->audio_file,
        ]);

        return redirect()->back()->with('success', 'Jadwal bel berhasil diperbarui!');
    }

    // === METHOD DUPLIKASI SINGLE JADWAL ===
    public function duplicateSchedule($id)
    {
        $schedule = Schedule::findOrFail($id);
        $newSchedule = $schedule->replicate();
        $newSchedule->save();

        return redirect()->back()->with('success', 'Jadwal bel berhasil disalin!');
    }

    // === METHOD SALIN JADWAL DENGAN PEMISAHAN VARIAN ASAL DAN TUJUAN ===
    public function copyDaySchedule(Request $request)
    {
        $request->validate([
            'from_day'     => 'required',
            'from_variant' => 'required',
            'to_day'       => 'required',
            'to_variant'   => 'required',
        ]);

        $fromDay     = $request->from_day;
        $fromVariant = $request->from_variant;
        $toDay       = $request->to_day;
        $toVariant   = $request->to_variant;

        if ($fromDay === $toDay && $fromVariant === $toVariant) {
            return redirect()->back()->with('error', 'Hari dan varian asal tidak boleh sama persis dengan tujuan.');
        }

        // 1. Ambil data dari lokasi asal
        $query = Schedule::where('day', $fromDay);
        if ($fromVariant !== 'all') {
            $query->where('variant', $fromVariant);
        }
        $sourceSchedules = $query->get();

        if ($sourceSchedules->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada jadwal pada hari dan varian asal yang dapat disalin.');
        }

        // 2. Bersihkan jadwal pada lokasi tujuan sebelum ditimpa
        $targetQuery = Schedule::where('day', $toDay);
        if ($toVariant !== 'all') {
            $targetQuery->where('variant', $toVariant);
        }
        $targetQuery->delete();

        // 3. Masukkan data hasil salinan ke lokasi tujuan
        foreach ($sourceSchedules as $item) {
            Schedule::create([
                'day'        => $toDay,
                'variant'    => ($toVariant === 'all') ? $item->variant : $toVariant,
                'time'       => $item->time,
                'event_name' => $item->event_name,
                'audio_file' => $item->audio_file,
                'is_active'  => $item->is_active,
            ]);
        }

        return redirect()->back()->with('success', 'Berhasil menyalin jadwal ke hari dan varian tujuan!');
    }

    public function destroySchedule($id)
    {
        Schedule::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Jadwal bel telah dihapus!');
    }

    public function destroyBatch(Request $request)
    {
        $type = $request->input('delete_type');

        if ($type === 'all') {
            Schedule::truncate();
            $msg = 'Seluruh jadwal bel telah berhasil dikosongkan!';
        } elseif ($type === 'by_day') {
            $day = $request->input('day');
            Schedule::where('day', $day)->delete();
            $msg = "Seluruh jadwal untuk hari {$day} berhasil dihapus!";
        } elseif ($type === 'by_variant') {
            $variant = $request->input('variant');
            Schedule::where('variant', $variant)->delete();
            $msg = "Seluruh jadwal untuk varian/mode {$variant} berhasil dihapus!";
        } else {
            return redirect()->back()->with('error', 'Pilihan hapus tidak valid!');
        }

        return redirect()->back()->with('success', $msg);
    }
}