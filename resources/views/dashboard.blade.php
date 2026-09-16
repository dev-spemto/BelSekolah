<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bel Sekolah Otomatis - SPEMTO</title>

    <!-- FAVICON BROWSER -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col justify-between transition-colors duration-300">

    <div>
        <!-- HEADER -->
        <header class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-700 px-6 py-4 sticky top-0 z-40 shadow-sm transition-colors duration-300">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="bg-gradient-to-tr from-blue-600 to-indigo-600 text-white p-3 rounded-2xl shadow-lg shadow-blue-500/30 text-xl font-black">
                        🔔
                    </div>
                    <div>
                        <h1 class="font-extrabold text-xl text-slate-900 dark:text-white leading-tight flex items-center gap-2">
                            Bel Sekolah Otomatis
                            <span class="text-[10px] bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 px-2 py-0.5 rounded-full font-bold">SPEMTO v1.0</span>
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">SMP Muhammadiyah Tonjong • Local Engine</p>
                    </div>
                </div>

                <!-- CLOCK & DATE (BAHASA INDONESIA) -->
                <div class="text-center">
                    <div id="realtime-clock" class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight font-mono">
                        00:00:00 <span class="text-sm font-bold text-slate-400 dark:text-slate-500">WIB</span>
                    </div>
                    <div class="text-xs font-semibold text-slate-400 dark:text-slate-400 uppercase tracking-widest mt-0.5">
                        {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </div>
                </div>

                <!-- TOP CONTROLS -->
                <div class="flex items-center gap-3">
                    <!-- DARK / LIGHT MODE SWITCH BUTTON -->
                    <button onclick="toggleTheme()" title="Ubah Mode Tampilan (Gelap/Terang)"
                        class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700 text-slate-700 dark:text-amber-300 hover:bg-slate-100 dark:hover:bg-slate-600 transition-all shadow-sm">
                        <span id="theme-toggle-icon" class="text-lg">🌙</span>
                    </button>

                    <!-- SYSTEM STATUS BUTTON -->
                    <button onclick="toggleSystemStatus()" 
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-xs border transition-all shadow-sm {{ $systemStatus === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800' }}">
                        <span class="w-2.5 h-2.5 rounded-full {{ $systemStatus === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                        <span>{{ $systemStatus === 'active' ? 'Sistem Aktif' : 'Sistem Muted' }}</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- MAIN CONTAINER -->
        <main class="max-w-7xl mx-auto px-6 py-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- LEFT & CENTER COLUMN -->
            <div class="lg:col-span-2 space-y-6">

                <!-- WIDGET PEMILIH MODE BEL OPERASIONAL -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 shadow-sm transition-colors duration-300">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl p-2 bg-blue-50 dark:bg-slate-700 rounded-xl">📅</span>
                            <div>
                                <h3 class="font-bold text-sm text-slate-900 dark:text-white">Mode Bel Operasional Hari Ini</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pilih mode khusus jika sedang bulan Puasa atau Ujian</p>
                            </div>
                        </div>

                        <select id="select-active-mode" onchange="changeActiveMode(this.value)" 
                            class="px-3.5 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 font-bold text-xs rounded-xl text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                            <option value="regular" {{ $activeMode === 'regular' ? 'selected' : '' }}>🟢 Mode Reguler (KBM Harian)</option>
                            <option value="puasa" {{ $activeMode === 'puasa' ? 'selected' : '' }}>🌙 Mode Bulan Puasa (Ramadhan)</option>
                            <option value="asts" {{ $activeMode === 'asts' ? 'selected' : '' }}>📝 Mode ASTS (UTS)</option>
                            <option value="asas" {{ $activeMode === 'asas' ? 'selected' : '' }}>📑 Mode ASAS (UAS)</option>
                            <option value="ujian_sekolah" {{ $activeMode === 'ujian_sekolah' ? 'selected' : '' }}>🎓 Mode Ujian Sekolah</option>
                        </select>
                    </div>
                </div>

                <!-- WIDGET JUMAT -->
                @if($activeMode === 'regular')
                <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/30 dark:to-orange-950/20 border border-amber-200 dark:border-amber-900/40 rounded-2xl p-5 shadow-sm transition-colors duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🕌</span>
                            <h2 class="font-bold text-amber-900 dark:text-amber-200 text-sm">KONTROL JADWAL HARI JUM'AT</h2>
                        </div>
                        @if($today === 'friday')
                            <span class="bg-amber-200 text-amber-800 dark:bg-amber-900 dark:text-amber-200 text-xs font-bold px-2.5 py-1 rounded-lg">Hari Ini Jumat</span>
                        @else
                            <span class="bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300 text-xs font-medium px-2.5 py-1 rounded-lg">Pratinjau Mode</span>
                        @endif
                    </div>

                    <p class="text-xs text-amber-800 dark:text-amber-300 mb-4">Apakah hari ini Shalat Jum'at Berjamaah di Sekolah?</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label onclick="setJumatMode('jamaah')" class="cursor-pointer flex items-start gap-3 p-3.5 rounded-xl border transition-all {{ $jumatMode === 'jamaah' ? 'bg-white dark:bg-slate-800 border-amber-500 dark:border-amber-500 ring-2 ring-amber-400/20 shadow-sm' : 'bg-amber-50/50 dark:bg-slate-900/30 border-amber-200 dark:border-amber-900/30 opacity-70' }}">
                            <input type="radio" name="jumat_option" value="jamaah" {{ $jumatMode === 'jamaah' ? 'checked' : '' }} class="mt-1 text-amber-600 focus:ring-amber-500">
                            <div>
                                <span class="font-bold text-sm text-slate-900 dark:text-white block">YA (Default)</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">Jadwal Reguler & Jum'atan. <strong class="text-slate-700 dark:text-slate-200">Pulang Jam 13:00 WIB</strong></span>
                            </div>
                        </label>

                        <label onclick="setJumatMode('ringkas')" class="cursor-pointer flex items-start gap-3 p-3.5 rounded-xl border transition-all {{ $jumatMode === 'ringkas' ? 'bg-white dark:bg-slate-800 border-amber-500 dark:border-amber-500 ring-2 ring-amber-400/20 shadow-sm' : 'bg-amber-50/50 dark:bg-slate-900/30 border-amber-200 dark:border-amber-900/30 opacity-70' }}">
                            <input type="radio" name="jumat_option" value="ringkas" {{ $jumatMode === 'ringkas' ? 'checked' : '' }} class="mt-1 text-amber-600 focus:ring-amber-500">
                            <div>
                                <span class="font-bold text-sm text-slate-900 dark:text-white block">TIDAK (Air Minim / Darurat)</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">Siswa Pulang Awal. <strong class="text-slate-700 dark:text-slate-200">Pulang Jam 11:00 WIB</strong></span>
                            </div>
                        </label>
                    </div>
                </div>
                @endif

                <!-- HERO CARD: BEL BERIKUTNYA -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm text-center relative overflow-hidden transition-colors duration-300">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>

                    <span class="text-xs font-extrabold uppercase tracking-widest text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 px-3 py-1 rounded-full inline-block mb-3">Bel Berikutnya</span>

                    <h3 id="next-event-title" class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white mb-1">Memuat Jadwal...</h3>

                    <div class="my-5">
                        <div id="countdown-timer" class="text-4xl md:text-6xl font-black text-slate-900 dark:text-white font-mono tracking-tight drop-shadow-sm">00 : 00 : 00</div>
                        <p class="text-xs font-semibold text-slate-400 dark:text-slate-400 uppercase tracking-widest mt-2">Sisa Waktu Menuju Bel</p>
                    </div>

                    <div class="inline-flex items-center gap-2 bg-slate-100 dark:bg-slate-700/60 px-4 py-1.5 rounded-full text-xs font-medium text-slate-600 dark:text-slate-300 mb-6 border border-slate-200 dark:border-slate-600">
                        <span>🎵 Audio:</span>
                        <strong id="next-event-audio" class="text-slate-800 dark:text-white font-mono">-</strong>
                    </div>

                    <div class="flex justify-center gap-3">
                        <button onclick="fetchNextSchedule()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold text-sm rounded-xl transition-all">🔄 Refresh</button>
                        <button onclick="testAudio()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-blue-500/20 transition-all">🔊 Tes Audio Bel</button>
                    </div>
                </div>

                <!-- MANUAL CONTROL PANEL -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm transition-colors duration-300">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2"><span>⚡</span> PANEL KONTROL MANUAL & PENGUMUMAN</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                        <button onclick="playAudioDirect('bel_masuk.mp3', 'Bel Masuk Sekolah')" class="p-4 rounded-xl border border-blue-200 dark:border-blue-900/50 bg-blue-50/50 dark:bg-blue-950/30 hover:bg-blue-100/80 dark:hover:bg-blue-900/50 transition-all text-left group shadow-sm active:scale-95">
                            <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase block mb-1">🔔 Utama</span>
                            <span class="font-bold text-sm text-blue-950 dark:text-blue-100 block">Bel Masuk Sekolah</span>
                        </button>

                        <button onclick="playAudioDirect('bel_istirahat.mp3', 'Bel Istirahat')" class="p-4 rounded-xl border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/50 dark:bg-emerald-950/30 hover:bg-emerald-100/80 dark:hover:bg-emerald-900/50 transition-all text-left group shadow-sm active:scale-95">
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase block mb-1">☕ Utama</span>
                            <span class="font-bold text-sm text-emerald-950 dark:text-emerald-100 block">Bel Istirahat</span>
                        </button>

                        <button onclick="playAudioDirect('bel_pulang.mp3', 'Bel Pulang Sekolah')" class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all text-left group shadow-sm active:scale-95">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase block mb-1">🏠 Utama</span>
                            <span class="font-bold text-sm text-slate-900 dark:text-slate-100 block">Bel Pulang Sekolah</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                        <button onclick="playAudioDirect('chime_pembuka.mp3', 'Chime Pembuka Pengumuman')" class="p-3.5 rounded-xl border border-amber-200 dark:border-amber-900/40 bg-amber-50 dark:bg-amber-950/20 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-all flex items-center gap-3 active:scale-95">
                            <span class="text-2xl">📢</span>
                            <div class="text-left">
                                <span class="font-bold text-xs text-amber-900 dark:text-amber-200 block">Chime Pembuka</span>
                                <span class="text-[10px] text-amber-700 dark:text-amber-400">Putar sebelum bicara Mic</span>
                            </div>
                        </button>

                        <button onclick="playAudioDirect('chime_penutup.mp3', 'Chime Penutup Pengumuman')" class="p-3.5 rounded-xl border border-amber-200 dark:border-amber-900/40 bg-amber-50 dark:bg-amber-950/20 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-all flex items-center gap-3 active:scale-95">
                            <span class="text-2xl">🔕</span>
                            <div class="text-left">
                                <span class="font-bold text-xs text-amber-900 dark:text-amber-200 block">Chime Penutup</span>
                                <span class="text-[10px] text-amber-700 dark:text-amber-400">Putar setelah pengumuman</span>
                            </div>
                        </button>

                        <button onclick="playAudioDirect('indonesia_raya.mp3', 'Lagu Indonesia Raya')" class="p-3.5 rounded-xl border border-red-200 dark:border-red-900/40 bg-red-50 dark:bg-red-950/20 hover:bg-red-100 dark:hover:bg-red-900/40 transition-all flex items-center gap-3 active:scale-95">
                            <span class="text-2xl">🇮🇩</span>
                            <div class="text-left">
                                <span class="font-bold text-xs text-red-900 dark:text-red-200 block">Indonesia Raya</span>
                                <span class="text-[10px] text-red-700 dark:text-red-400">Putar Manual (Auto 10:00)</span>
                            </div>
                        </button>
                    </div>
                </div>

            </div>

            <!-- RIGHT SIDEBAR -->
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm transition-colors duration-300">
                    <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-700 pb-3">
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">Jadwal Hari Ini</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold capitalize" id="sidebar-day-name">
                                {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd') }}
                            </p>
                        </div>
                        <span class="text-[11px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold px-2.5 py-1 rounded-lg uppercase">
                            Mode: {{ $activeMode }}
                        </span>
                    </div>

                    <div class="space-y-2.5 max-h-[420px] overflow-y-auto pr-1" id="schedule-list-container">
                        @forelse($schedules as $sched)
                            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-900/40">
                                <div>
                                    <span class="font-mono font-bold text-sm text-slate-900 dark:text-white block">{{ \Carbon\Carbon::parse($sched->time)->format('H:i') }} WIB</span>
                                    <span class="text-xs text-slate-600 dark:text-slate-400 font-medium">{{ $sched->event_name }}</span>
                                </div>
                                <span class="text-[11px] font-mono bg-white dark:bg-slate-800 px-2 py-1 rounded border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400">{{ $sched->audio_file }}</span>
                            </div>
                        @empty
                            <div class="text-center py-8 text-slate-400 dark:text-slate-500 text-sm">Tidak ada jadwal bel untuk mode ini.</div>
                        @endforelse
                    </div>

                    <button onclick="openModal()" class="w-full mt-4 py-3 bg-slate-900 hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-all flex items-center justify-center gap-2 shadow-sm">
                        ⚙️ Kelola Semua Jadwal
                    </button>
                </div>
            </div>

        </main>
    </div>

    <!-- FOOTER WITH COPYRIGHT & CONTACT INFO -->
    <footer class="bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 mt-12 py-6 px-6 transition-colors duration-300">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400">
            <div>
                <p class="font-bold text-slate-800 dark:text-slate-200">&copy; {{ date('Y') }} Tim IT SMP Muhammadiyah Tonjong.</p>
                <p class="mt-0.5">Sistem Bel Sekolah Otomatis SPEMTO — Brebes, Jawa Tengah.</p>
            </div>

            <!-- CONTACT PERSON & LINKS -->
            <div class="flex flex-wrap items-center gap-4 font-semibold">
                <a href="mailto:smpmuhitonjong@gmail.com" class="hover:text-blue-600 dark:hover:text-blue-400 flex items-center gap-1">
                    ✉️ smpmuhitonjong@gmail.com
                </a>
                <a href="https://wa.me/6285185033377" target="_blank" class="hover:text-emerald-600 dark:hover:text-emerald-400 flex items-center gap-1">
                    📞 085185033377
                </a>
                <a href="https://smpmuhtonjong.sch.id" target="_blank" class="hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-1">
                    🌐 smpmuhtonjong.sch.id
                </a>
            </div>
        </div>
    </footer>

    <!-- MODAL POPUP SETTING JADWAL MANUAL -->
    <div id="modal-schedule" onclick="closeModalOnBackdrop(event)" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col shadow-2xl border border-slate-200 dark:border-slate-700 transition-colors duration-300">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                <h3 class="font-bold text-lg text-slate-900 dark:text-white">⚙️ Pengaturan Jadwal Bel Sekolah</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold text-xl" title="Tutup (Esc)">&times;</button>
            </div>

            <div class="p-6 overflow-y-auto space-y-6">

                <!-- PANEL HAPUS BATCH (KOSONGKAN JADWAL) -->
                <div class="bg-rose-50/60 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/40 rounded-xl p-4 space-y-3">
                    <h4 class="font-bold text-xs text-rose-900 dark:text-rose-300 uppercase tracking-wider flex items-center gap-1">
                        <span>🗑️</span> Opsi Hapus Batch / Kosongkan Jadwal
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <!-- Hapus Berdasarkan Hari -->
                        <form action="{{ route('schedules.destroyBatch') }}" method="POST" onsubmit="return confirm('Hapus seluruh jadwal pada hari yang dipilih?')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="delete_type" value="by_day">
                            <div class="flex gap-2">
                                <select name="day" class="text-xs p-2 rounded-lg border border-rose-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 w-full">
                                    <option value="monday">Hari Senin</option>
                                    <option value="tuesday">Hari Selasa</option>
                                    <option value="wednesday">Hari Rabu</option>
                                    <option value="thursday">Hari Kamis</option>
                                    <option value="friday">Hari Jumat</option>
                                    <option value="saturday">Hari Sabtu</option>
                                </select>
                                <button type="submit" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-lg whitespace-nowrap">Hapus Hari</button>
                            </div>
                        </form>

                        <!-- Hapus Berdasarkan Varian -->
                        <form action="{{ route('schedules.destroyBatch') }}" method="POST" onsubmit="return confirm('Hapus seluruh jadwal pada varian yang dipilih?')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="delete_type" value="by_variant">
                            <div class="flex gap-2">
                                <select name="variant" class="text-xs p-2 rounded-lg border border-rose-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 w-full">
                                    <option value="default">Reguler Harian</option>
                                    <option value="puasa">Mode Puasa</option>
                                    <option value="jumat_jamaah">Jumat Jamaah</option>
                                    <option value="jumat_ringkas">Jumat Ringkas</option>
                                    <option value="asts">Mode ASTS</option>
                                    <option value="asas">Mode ASAS</option>
                                    <option value="ujian_sekolah">Mode Ujian Sekolah</option>
                                </select>
                                <button type="submit" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-lg whitespace-nowrap">Hapus Varian</button>
                            </div>
                        </form>

                        <!-- Hapus SEMUA Jadwal -->
                        <form action="{{ route('schedules.destroyBatch') }}" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS SEMUA JADWAL yang ada di database?')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="delete_type" value="all">
                            <button type="submit" class="w-full py-2 bg-rose-800 hover:bg-rose-900 text-white font-bold text-xs rounded-lg shadow-sm">
                                🚨 Hapus SEMUA Jadwal
                            </button>
                        </form>
                    </div>
                </div>

                <!-- FORM TAMBAH JADWAL BARU -->
                <form action="{{ route('schedules.store') }}" method="POST" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl p-4 space-y-4">
                    @csrf
                    <h4 class="font-bold text-sm text-slate-800 dark:text-white">➕ Tambah Jadwal Bel Baru</h4>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 block mb-1">Hari</label>
                            <select name="day" class="w-full text-xs p-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                <option value="monday">Senin</option>
                                <option value="tuesday">Selasa</option>
                                <option value="wednesday">Rabu</option>
                                <option value="thursday">Kamis</option>
                                <option value="friday">Jumat</option>
                                <option value="saturday">Sabtu</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 block mb-1">Varian Presets / Mode Bel</label>
                            <select name="variant" class="w-full text-xs p-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                <option value="default">Reguler Harian</option>
                                <option value="puasa">🌙 Mode Bulan Puasa (Ramadhan)</option>
                                <option value="jumat_jamaah">Jumat (Mode Jamaah)</option>
                                <option value="jumat_ringkas">Jumat (Mode Ringkas/Air Minim)</option>
                                <option value="asts">Mode ASTS (UTS)</option>
                                <option value="asas">Mode ASAS (UAS)</option>
                                <option value="ujian_sekolah">Mode Ujian Sekolah</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 block mb-1">Jam Bel (HH:MM:SS)</label>
                            <input type="time" name="time" step="1" required class="w-full text-xs p-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 block mb-1">Nama Agenda / Kegiatan</label>
                            <input type="text" name="event_name" placeholder="Misal: Bel Masuk Jam ke-1" required class="w-full text-xs p-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 block mb-1">File Audio Sound</label>
                            <select name="audio_file" class="w-full text-xs p-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                @foreach($availableAudioFiles as $file)
                                    <option value="{{ $file }}">{{ $file }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                        Simpan Jadwal Baru
                    </button>
                </form>

                <!-- TABEL DAFTAR JADWAL DENGAN FILTER & SORTING -->
                <div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-3">
                        <h4 class="font-bold text-sm text-slate-800 dark:text-white flex items-center gap-1.5">
                            📋 Daftar Seluruh Jadwal Bel
                        </h4>

                        <!-- FILTER DROPDOWN -->
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <span class="text-xs text-slate-500 font-medium">Filter:</span>
                            <select id="filter-schedule-day" onchange="filterScheduleTable()" 
                                class="text-xs p-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 outline-none">
                                <option value="all">Semua Hari</option>
                                <option value="monday">Senin</option>
                                <option value="tuesday">Selasa</option>
                                <option value="wednesday">Rabu</option>
                                <option value="thursday">Kamis</option>
                                <option value="friday">Jumat</option>
                                <option value="saturday">Sabtu</option>
                            </select>

                            <select id="filter-schedule-variant" onchange="filterScheduleTable()" 
                                class="text-xs p-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 outline-none">
                                <option value="all">Semua Varian</option>
                                <option value="default">Reguler Harian</option>
                                <option value="puasa">Mode Puasa</option>
                                <option value="jumat_jamaah">Jumat Jamaah</option>
                                <option value="jumat_ringkas">Jumat Ringkas</option>
                                <option value="asts">Mode ASTS</option>
                                <option value="asas">Mode ASAS</option>
                                <option value="ujian_sekolah">Mode Ujian Sekolah</option>
                            </select>
                        </div>
                    </div>

                    <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-sm">
                        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300" id="table-all-schedules">
                            <thead class="bg-slate-100 dark:bg-slate-900/60 text-slate-700 dark:text-slate-300 font-bold uppercase border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="p-3">Hari & Varian</th>
                                    <th class="p-3">Jam</th>
                                    <th class="p-3">Kegiatan</th>
                                    <th class="p-3">File Audio</th>
                                    <th class="p-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @php
                                    $daysIndo = [
                                        'monday' => 'Senin',
                                        'tuesday' => 'Selasa',
                                        'wednesday' => 'Rabu',
                                        'thursday' => 'Kamis',
                                        'friday' => 'Jumat',
                                        'saturday' => 'Sabtu',
                                        'sunday' => 'Minggu'
                                    ];
                                @endphp
                                @foreach($allSchedules as $item)
                                    <tr class="schedule-row hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors" 
                                        data-day="{{ strtolower($item->day) }}" 
                                        data-variant="{{ strtolower($item->variant) }}">
                                        <td class="p-3 capitalize">
                                            <span class="font-bold text-slate-900 dark:text-white block">
                                                {{ $daysIndo[strtolower($item->day)] ?? $item->day }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 uppercase font-semibold">{{ $item->variant }}</span>
                                        </td>
                                        <td class="p-3 font-mono font-bold text-slate-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($item->time)->format('H:i') }}
                                        </td>
                                        <td class="p-3 font-medium text-slate-800 dark:text-slate-200">{{ $item->event_name }}</td>
                                        <td class="p-3 font-mono text-blue-600 dark:text-blue-400">{{ $item->audio_file }}</td>
                                        <td class="p-3 text-center">
                                            <form action="{{ route('schedules.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 dark:text-rose-400 hover:underline font-bold">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <audio id="bel-audio-player" class="hidden"></audio>

    <script>
        // MAPPER HARI INDONESIA JS
        function getIndonesianDay(dayName) {
            const daysMap = {
                'monday': 'Senin',
                'tuesday': 'Selasa',
                'wednesday': 'Rabu',
                'thursday': 'Kamis',
                'friday': 'Jumat',
                'saturday': 'Sabtu',
                'sunday': 'Minggu'
            };
            return daysMap[dayName.toLowerCase()] || dayName;
        }

        // DARK / LIGHT MODE SWITCHER ENGINE
        function initTheme() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
                document.getElementById('theme-toggle-icon').innerText = '☀️';
            } else {
                document.documentElement.classList.remove('dark');
                document.getElementById('theme-toggle-icon').innerText = '🌙';
            }
        }

        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                document.getElementById('theme-toggle-icon').innerText = '🌙';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                document.getElementById('theme-toggle-icon').innerText = '☀️';
            }
        }
        initTheme();

        // CORE ENGINE BEL & TIMERS
        let nextScheduleTime = null;
        let nextAudioFile = null;
        let hasPlayedCurrentBel = false;

        let is5sCurrentlyPlaying = false;
        let current5sAudioFile = null;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // KEEP-ALIVE SYSTEM
        let wakeLock = null;
        async function initKeepAlive() {
            try {
                if ('wakeLock' in navigator) {
                    wakeLock = await navigator.wakeLock.request('screen');
                }
            } catch (err) {}

            setInterval(() => {
                try {
                    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    if (audioCtx.state === 'suspended') audioCtx.resume();
                    const buffer = audioCtx.createBuffer(1, 1, 22050);
                    const source = audioCtx.createBufferSource();
                    source.buffer = buffer;
                    source.connect(audioCtx.destination);
                    source.start(0);
                } catch (e) {}
            }, 30000);
        }

        document.addEventListener('DOMContentLoaded', initKeepAlive);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible' && wakeLock === null) initKeepAlive();
        });

        function openModal() { document.getElementById('modal-schedule').classList.remove('hidden'); }
        function closeModal() { document.getElementById('modal-schedule').classList.add('hidden'); }

        function closeModalOnBackdrop(event) {
            if (event.target.id === 'modal-schedule') closeModal();
        }

        window.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' || event.key === 'Esc') closeModal();
        });

        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('realtime-clock').innerHTML = `${hours}:${minutes}:${seconds} <span class="text-sm font-bold text-slate-400 dark:text-slate-500">WIB</span>`;
            checkAndTriggerBel(`${hours}:${minutes}:${seconds}`);
        }
        setInterval(updateClock, 1000);
        updateClock();

        async function fetchNextSchedule() {
            try {
                const response = await fetch('/api/next-schedule');
                const data = await response.json();

                // --- PENANGANAN ROTASI AUDIO 5S PAGI (06.15 - 06.45 WIB) ---
                if (data.audio_5s && data.audio_5s.active) {
                    current5sAudioFile = data.audio_5s.file;
                    if (!is5sCurrentlyPlaying && current5sAudioFile) {
                        is5sCurrentlyPlaying = true;
                        playAudio5S(current5sAudioFile);
                    }
                } else {
                    if (is5sCurrentlyPlaying) {
                        stopAudio5S();
                        is5sCurrentlyPlaying = false;
                    }
                }

                // --- PENANGANAN JADWAL REGULER ---
                if (data.next_schedule) {
                    document.getElementById('next-event-title').innerText = data.next_schedule.event_name;
                    document.getElementById('next-event-audio').innerText = data.next_schedule.audio_file;
                    nextScheduleTime = data.next_schedule.time;
                    nextAudioFile = data.next_schedule.audio_file;
                    hasPlayedCurrentBel = false;
                } else {
                    document.getElementById('next-event-title').innerText = 'Tidak Ada Bel Lagi Hari Ini';
                    document.getElementById('next-event-audio').innerText = '-';
                    document.getElementById('countdown-timer').innerText = '00 : 00 : 00';
                    nextScheduleTime = null;
                }
                renderSidebarSchedules(data.today_schedules, data.current_time);
            } catch (error) { console.error(error); }
        }
        setInterval(fetchNextSchedule, 10000);
        fetchNextSchedule();

        setInterval(() => {
            if (!nextScheduleTime) return;
            const now = new Date();
            const [targetH, targetM, targetS] = nextScheduleTime.split(':').map(Number);
            const targetDate = new Date();
            targetDate.setHours(targetH, targetM, targetS, 0);
            const diff = targetDate - now;

            if (diff > 0) {
                const hours = String(Math.floor((diff / (1000 * 60 * 60)) % 24)).padStart(2, '0');
                const minutes = String(Math.floor((diff / (1000 * 60)) % 60)).padStart(2, '0');
                const seconds = String(Math.floor((diff / 1000) % 60)).padStart(2, '0');
                document.getElementById('countdown-timer').innerText = `${hours} : ${minutes} : ${seconds}`;
            } else {
                document.getElementById('countdown-timer').innerText = '00 : 00 : 00';
            }
        }, 1000);

        function checkAndTriggerBel(currentTimeStr) {
            if (nextScheduleTime && currentTimeStr === nextScheduleTime && !hasPlayedCurrentBel) {
                hasPlayedCurrentBel = true;
                playAudio(nextAudioFile);
                setTimeout(fetchNextSchedule, 3000);
            }
        }

        function playAudio(filename) {
            const player = document.getElementById('bel-audio-player');
            player.loop = false;
            player.src = `/storage/sounds/${filename}`;
            player.play().catch(error => {
                alert(`Waktunya Bel (${filename})! Klik layar untuk mengizinkan suara.`);
            });
        }

        function playAudio5S(filename) {
            const player = document.getElementById('bel-audio-player');
            player.src = `/storage/sounds/${filename}`;
            player.loop = true;
            player.play().catch(err => {
                console.warn('Playback 5S butuh interaksi pengguna:', err);
            });
        }

        function stopAudio5S() {
            const player = document.getElementById('bel-audio-player');
            player.pause();
            player.loop = false;
        }

        function playAudioDirect(filename, title) { playAudio(filename); }
        function testAudio() { if (nextAudioFile && nextAudioFile !== '-') playAudio(nextAudioFile); }

        async function changeActiveMode(mode) {
            await fetch('/api/active-mode', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ mode: mode })
            });
            location.reload();
        }

        async function setJumatMode(mode) {
            await fetch('/api/jumat-mode', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ mode: mode })
            });
            location.reload();
        }

        async function toggleSystemStatus() {
            await fetch('/api/system-status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            location.reload();
        }

        // FUNGSI FILTER TABLE IN MODAL
        function filterScheduleTable() {
            const selectedDay = document.getElementById('filter-schedule-day').value;
            const selectedVariant = document.getElementById('filter-schedule-variant').value;
            const rows = document.querySelectorAll('#table-all-schedules .schedule-row');

            rows.forEach(row => {
                const rowDay = row.getAttribute('data-day');
                const rowVariant = row.getAttribute('data-variant');

                const matchDay = (selectedDay === 'all' || rowDay === selectedDay);
                const matchVariant = (selectedVariant === 'all' || rowVariant === selectedVariant);

                if (matchDay && matchVariant) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function renderSidebarSchedules(schedules, currentTime) {
            const container = document.getElementById('schedule-list-container');
            if (!schedules || schedules.length === 0) {
                container.innerHTML = `<div class="text-center py-8 text-slate-400 dark:text-slate-500 text-sm">Tidak ada jadwal bel.</div>`;
                return;
            }

            container.innerHTML = schedules.map(sched => {
                const isPassed = sched.time <= currentTime;
                const isNext = sched.time === nextScheduleTime;
                let bgClass = 'bg-slate-50/50 dark:bg-slate-900/40 border-slate-100 dark:border-slate-700/60';
                let textClass = 'text-slate-900 dark:text-white';
                let statusBadge = '';

                if (isPassed) {
                    bgClass = 'bg-slate-100/60 dark:bg-slate-900/20 border-slate-100 dark:border-slate-800 opacity-60';
                    textClass = 'text-slate-500 dark:text-slate-500 line-through';
                    statusBadge = '<span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">✓ Selesai</span>';
                } else if (isNext) {
                    bgClass = 'bg-blue-50/80 dark:bg-blue-950/40 border-blue-200 dark:border-blue-800 ring-2 ring-blue-400/20 shadow-sm';
                    textClass = 'text-blue-900 dark:text-blue-200 font-bold';
                    statusBadge = '<span class="text-[10px] bg-blue-600 text-white px-1.5 py-0.5 rounded font-bold animate-pulse">Berikutnya</span>';
                }

                return `
                    <div class="flex items-center justify-between p-3 rounded-xl border ${bgClass}">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-sm ${textClass}">${sched.time.substring(0, 5)} WIB</span>
                                ${statusBadge}
                            </div>
                            <span class="text-xs text-slate-600 dark:text-slate-300 font-medium block">${sched.event_name}</span>
                        </div>
                        <span class="text-[11px] font-mono bg-white dark:bg-slate-800 px-2 py-1 rounded border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400">${sched.audio_file}</span>
                    </div>
                `;
            }).join('');
        }
    </script>
</body>
</html>