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
            
            // Mengacak audio baru jika memasuki hari yang berbeda
            if ($savedDate !== $todayDate) {
                $assignedAudio = $this->pickRandom5sAudio();
                Setting::set('5s_today_audio', $assignedAudio);
                Setting::set('5s_assigned_date', $todayDate);
            }

            $audio5sToday = Setting::get('5s_today_audio');

            // Cek jendela waktu aktif 5S Pagi
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

        // Injeksi jadwal Lagu Indonesia Raya secara sistematis jam 10:00 WIB (Senin-Sabtu)
        $hasIndonesiaRaya = $todaySchedules->contains(fn($item) => $item->time === '10:00:00');
        if (!$hasIndonesiaRaya && in_array($today, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'])) {
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
            $todaySchedules = $todaySchedules->sortBy('time')->values();
        }

        // Ambil bel berikutnya dari waktu saat ini
        $nextSchedule = $todaySchedules->first(fn($item) => $item->time > $currentTime);

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

    /**
     * Memilih audio 5S acak tanpa pengulangan hingga semua file dalam daftar pernah diputar.
     */
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

        // Reset riwayat jika seluruh playlist sudah pernah diputar
        if (empty($remaining)) {
            $playedHistory = [];
            $remaining = $allPlaylist;
        }

        $selected = $remaining[array_rand($remaining)];
        $playedHistory[] = $selected;
        Setting::set('5s_played_history', json_encode($playedHistory));

        return $selected;
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