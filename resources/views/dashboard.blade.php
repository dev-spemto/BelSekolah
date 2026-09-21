<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bel Sekolah Otomatis - SPEMTO</title>

    <!-- FAVICON BROWSER -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- TAILWIND JS LOKAL (OFFLINE-SAFE) -->
    <script src="{{ asset('js/tailwind.js') }}"></script>
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

    <!-- CSS KHUSUS PRINT & FONT SYSTEM (OFFLINE-SAFE) -->
    <style>
        body { 
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; 
        }

        /* CSS KHUSUS PRINT (A4 LANDSCAPE & FIT 1 HALAMAN) */
        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm;
            }

            body * {
                visibility: hidden;
            }

            #modal-view-schedule, 
            #modal-view-schedule * {
                visibility: visible;
            }

            #modal-view-schedule {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                height: auto !important;
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            #modal-view-schedule .bg-white,
            #modal-view-schedule .bg-slate-900 {
                background: white !important;
                color: black !important;
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                max-height: none !important;
                padding: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            table {
                width: 100% !important;
                table-layout: fixed !important;
                border-collapse: collapse !important;
            }

            th, td {
                padding: 3px 4px !important;
                font-size: 9px !important;
                border: 1px solid #cbd5e1 !important;
                word-wrap: break-word !important;
            }

            th {
                background-color: #0f172a !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-badge {
                border: 1px solid #94a3b8 !important;
                padding: 2px 3px !important;
                margin-bottom: 2px !important;
                border-radius: 4px !important;
                background-color: #f8fafc !important;
                color: #0f172a !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col justify-between transition-colors duration-300">

    <div>
        <!-- HEADER -->
        <header class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-700 px-6 py-4 sticky top-0 z-40 shadow-sm transition-colors duration-300">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    
                    <!-- LOGO SEKOLAH -->
                    <div class="p-1.5 bg-white dark:bg-slate-700 rounded-2xl shadow-md border border-slate-100 dark:border-slate-600 flex items-center justify-center">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo SPEMTO" class="h-10 w-auto object-contain">
                    </div>

                    <div>
                        <h1 class="font-extrabold text-xl text-slate-900 dark:text-white leading-tight flex items-center gap-2">
                            Bel Sekolah Otomatis
                            <span class="text-[10px] bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 px-2 py-0.5 rounded-full font-bold">SPEMTO v1.0</span>
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">SMP Muhammadiyah Tonjong • Local Engine</p>
                    </div>
                </div>

                <!-- CLOCK & DATE -->
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
                    <button onclick="toggleTheme()" title="Ubah Mode Tampilan (Gelap/Terang)"
                        class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700 text-slate-700 dark:text-amber-300 hover:bg-slate-100 dark:hover:bg-slate-600 transition-all shadow-sm">
                        <span id="theme-toggle-icon" class="text-lg">🌙</span>
                    </button>

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

                    <div class="space-y-2 mt-4">
                        <button onclick="openModal()" class="w-full py-3 bg-slate-900 hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-all flex items-center justify-center gap-2 shadow-sm">
                            ⚙️ Kelola Semua Jadwal
                        </button>
                        <button onclick="openViewModal()" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-2">
                            👁️ Lihat Semua Jadwal (Senin-Sabtu)
                        </button>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- FOOTER -->
    <footer class="bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 mt-12 py-6 px-6 transition-colors duration-300">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400">
            <div>
                <p class="font-bold text-slate-800 dark:text-slate-200">&copy; {{ date('Y') }} Tim IT SMP Muhammadiyah Tonjong.</p>
                <p class="mt-0.5">Sistem Bel Sekolah Otomatis SPEMTO — Brebes, Jawa Tengah.</p>
            </div>

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

    <!-- MODAL POPUP 1: SETTING JADWAL MANUAL & BACKUP -->
    <div id="modal-schedule" onclick="closeModalOnBackdrop(event)" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col shadow-2xl border border-slate-200 dark:border-slate-700 transition-colors duration-300">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                <h3 class="font-bold text-lg text-slate-900 dark:text-white">⚙️ Pengaturan Jadwal Bel Sekolah</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold text-xl" title="Tutup (Esc)">&times;</button>
            </div>

            <div class="p-6 overflow-y-auto space-y-6">

                <!-- PANEL SAVE & LOAD CONFIGURATION -->
                <div class="bg-blue-50/60 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/40 rounded-xl p-4 space-y-3">
                    <h4 class="font-bold text-xs text-blue-900 dark:text-blue-300 uppercase tracking-wider flex items-center gap-1">
                        <span>💾</span> Backup & Restore Konfigurasi Jadwal (Save / Load JSON)
                    </h4>
                    <p class="text-xs text-blue-700 dark:text-blue-400">
                        Simpan seluruh susunan jadwal ke file JSON atau muat file jadwal saat berpindah komputer.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                        <a href="{{ route('schedules.export') }}" 
                            class="flex items-center justify-center gap-2 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all text-center">
                            <span>📥</span> Download Backup Config (Save JSON)
                        </a>

                        <form action="{{ route('schedules.import') }}" method="POST" enctype="multipart/form-data" class="flex gap-2" onsubmit="return confirm('Memuat file ini akan menggantikan seluruh jadwal bel yang ada. Lanjutkan?')">
                            @csrf
                            <input type="file" name="config_file" accept=".json" required 
                                class="text-xs p-1.5 rounded-lg border border-blue-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 w-full file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-slate-800 dark:file:text-blue-300">
                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl whitespace-nowrap shadow-sm">
                                📤 Load JSON
                            </button>
                        </form>
                    </div>
                </div>

                <!-- PANEL SALIN JADWAL -->
                <div class="bg-indigo-50/60 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-900/40 rounded-xl p-4 space-y-3">
                    <h4 class="font-bold text-xs text-indigo-900 dark:text-indigo-300 uppercase tracking-wider flex items-center gap-1">
                        <span>📋</span> Salin / Duplikat Jadwal Bel (Hari & Varian)
                    </h4>
                    <p class="text-xs text-indigo-700 dark:text-indigo-400">
                        Salin jadwal dari Hari/Varian asal ke Hari/Varian tujuan (contoh: <strong>Jumat + Jumat Jamaah</strong> ➔ <strong>Jumat + Jumat Ringkas</strong>).
                    </p>

                    <form action="{{ route('schedules.copyDay') }}" method="POST" onsubmit="return confirm('Salin jadwal ke lokasi tujuan? Jadwal pada lokasi tujuan akan diperbarui.')">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-center">
                            <div>
                                <label class="text-[10px] font-bold text-indigo-900 dark:text-indigo-300 block mb-1 uppercase">Dari Hari (Asal)</label>
                                <select name="from_day" required class="w-full text-xs p-2 rounded-lg border border-indigo-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                                    <option value="monday">Senin</option>
                                    <option value="tuesday">Selasa</option>
                                    <option value="wednesday">Rabu</option>
                                    <option value="thursday">Kamis</option>
                                    <option value="friday" selected>Jumat</option>
                                    <option value="saturday">Sabtu</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-indigo-900 dark:text-indigo-300 block mb-1 uppercase">Dari Varian</label>
                                <select name="from_variant" required class="w-full text-xs p-2 rounded-lg border border-indigo-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                                    <option value="default">Reguler Harian</option>
                                    <option value="jumat_jamaah" selected>Jumat Jamaah</option>
                                    <option value="jumat_ringkas">Jumat Ringkas</option>
                                    <option value="puasa">Mode Puasa</option>
                                    <option value="asts">Mode ASTS</option>
                                    <option value="asas">Mode ASAS</option>
                                    <option value="ujian_sekolah">Mode Ujian Sekolah</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-indigo-900 dark:text-indigo-300 block mb-1 uppercase">Ke Hari (Tujuan)</label>
                                <select name="to_day" required class="w-full text-xs p-2 rounded-lg border border-indigo-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                                    <option value="monday">Senin</option>
                                    <option value="tuesday">Selasa</option>
                                    <option value="wednesday">Rabu</option>
                                    <option value="thursday">Kamis</option>
                                    <option value="friday" selected>Jumat</option>
                                    <option value="saturday">Sabtu</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-indigo-900 dark:text-indigo-300 block mb-1 uppercase">Ke Varian</label>
                                <select name="to_variant" required class="w-full text-xs p-2 rounded-lg border border-indigo-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                                    <option value="default">Reguler Harian</option>
                                    <option value="jumat_jamaah">Jumat Jamaah</option>
                                    <option value="jumat_ringkas" selected>Jumat Ringkas</option>
                                    <option value="puasa">Mode Puasa</option>
                                    <option value="asts">Mode ASTS</option>
                                    <option value="asas">Mode ASAS</option>
                                    <option value="ujian_sekolah">Mode Ujian Sekolah</option>
                                </select>
                            </div>

                            <div class="lg:pt-5">
                                <button type="submit" class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all whitespace-nowrap">
                                    🔄 Salin Jadwal
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

                <!-- PANEL HAPUS BATCH -->
                <div class="bg-rose-50/60 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/40 rounded-xl p-4 space-y-3">
                    <h4 class="font-bold text-xs text-rose-900 dark:text-rose-300 uppercase tracking-wider flex items-center gap-1">
                        <span>🗑️</span> Opsi Hapus Batch / Kosongkan Jadwal
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
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
                                            <div class="flex items-center justify-center gap-2">
                                                <button onclick="openEditModal({{ json_encode($item) }})" class="text-blue-600 dark:text-blue-400 hover:underline font-bold">Edit</button>
                                                <span class="text-slate-300 dark:text-slate-700">|</span>

                                                <form action="{{ route('schedules.duplicate', $item->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold" title="Duplikat jadwal ini">Salin</button>
                                                </form>
                                                <span class="text-slate-300 dark:text-slate-700">|</span>

                                                <form action="{{ route('schedules.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-600 dark:text-rose-400 hover:underline font-bold">Hapus</button>
                                                </form>
                                            </div>
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

    <!-- MODAL POPUP 2: READ-ONLY MATRIX LIHAT SEMUA JADWAL -->
    <div id="modal-view-schedule" onclick="closeViewModalOnBackdrop(event)" class="fixed inset-0 bg-slate-950/70 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-6xl w-full max-h-[92vh] overflow-hidden flex flex-col shadow-2xl border border-slate-200 dark:border-slate-800 transition-colors duration-300">
            
            <!-- HEADER MODAL -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-gradient-to-r from-blue-50 via-slate-50 to-indigo-50 dark:from-slate-900 dark:via-slate-850 dark:to-indigo-950/40">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-blue-600 text-white rounded-2xl shadow-md shadow-blue-500/20 text-lg no-print">
                        👁️
                    </div>
                    <div>
                        <h3 class="font-extrabold text-lg text-slate-900 dark:text-white flex items-center gap-2">
                            Matrix Pratinjau Jadwal Bel
                            <span class="text-[10px] bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider no-print">Read-Only</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Peta seluruh kegiatan bel sekolah dari hari Senin hingga Sabtu</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 no-print">
                    <div class="flex items-center gap-2 bg-white dark:bg-slate-800 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                        <label for="previewVariantFilter" class="text-xs font-bold text-slate-600 dark:text-slate-300">Filter Varian:</label>
                        <select id="previewVariantFilter" onchange="filterMatrixPreview(this.value)" class="text-xs font-bold rounded-lg border-none bg-transparent text-slate-800 dark:text-white focus:ring-0 cursor-pointer outline-none">
                            <option value="auto" selected>✨ Sesuai Mode System ({{ strtoupper($activeMode) }})</option>
                            <option value="all">🌐 Tampilkan Semua Varian</option>
                            <option value="regular">🟢 Reguler (Default & Jumat)</option>
                            <option value="asts">📝 Mode ASTS</option>
                            <option value="asas">📑 Mode ASAS</option>
                            <option value="puasa">🌙 Mode Bulan Puasa</option>
                            <option value="ujian_sekolah">🎓 Mode Ujian Sekolah</option>
                        </select>
                    </div>

                    <a href="{{ route('schedules.print', ['mode' => $activeMode]) }}" target="_blank" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition-all shadow-sm flex items-center gap-1.5 inline-flex">
                        🖨️ Cetak / Print
                    </a>
                    <button onclick="closeViewModal()" class="w-8 h-8 rounded-full bg-slate-200/60 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-300 font-bold flex items-center justify-center transition-all" title="Tutup (Esc)">&times;</button>
                </div>
            </div>

            <!-- BODY MODAL MATRIX -->
            <div class="p-6 overflow-y-auto">
                @php
                    $uniqueTimes = $allSchedules->pluck('time')->map(fn($t) => \Carbon\Carbon::parse($t)->format('H:i'))->unique()->sort()->values();
                    $matrixDays = ['monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu', 'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu'];
                @endphp

                <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-x-auto shadow-sm bg-white dark:bg-slate-900">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white font-bold uppercase text-[11px] tracking-wider border-b border-slate-800">
                                <th class="p-3.5 border-r border-slate-800 w-24 text-center bg-slate-950/80">
                                    <div class="flex items-center justify-center gap-1">
                                        <span>⏰</span> Jam
                                    </div>
                                </th>
                                @foreach($matrixDays as $dayKey => $dayLabel)
                                    <th class="p-3.5 border-r border-slate-800/60 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span>📅</span> {{ $dayLabel }}
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($uniqueTimes as $timeStr)
                                <tr class="hover:bg-blue-50/30 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="p-3 font-mono font-extrabold text-slate-800 dark:text-white border-r border-slate-200 dark:border-slate-800 text-center bg-slate-50/80 dark:bg-slate-900/60">
                                        <span class="inline-block bg-slate-200 dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-2.5 py-1 rounded-lg text-xs shadow-inner">
                                            {{ $timeStr }}
                                        </span>
                                    </td>

                                    @foreach($matrixDays as $dayKey => $dayLabel)
                                        @php
                                            $matchingEvents = $allSchedules->filter(function($item) use ($dayKey, $timeStr) {
                                                return strtolower($item->day) === $dayKey && \Carbon\Carbon::parse($item->time)->format('H:i') === $timeStr;
                                            });
                                        @endphp

                                        <td class="p-2 border-r border-slate-200/60 dark:border-slate-800/50 align-top">
                                            @if($matchingEvents->count() > 0)
                                                @foreach($matchingEvents as $ev)
                                                    @php
                                                        $nameLower = strtolower($ev->event_name);
                                                        $badgeStyle = "bg-blue-50 dark:bg-blue-950/40 border-blue-200 dark:border-blue-900/60 text-blue-950 dark:text-blue-200";
                                                        $accentDot = "bg-blue-500";

                                                        if (stristr($nameLower, 'upacara') || stristr($nameLower, 'literasi') || stristr($nameLower, 'senam')) {
                                                            $badgeStyle = "bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-900/60 text-amber-950 dark:text-amber-200";
                                                            $accentDot = "bg-amber-500";
                                                        } 
                                                        elseif (stristr($nameLower, 'dhuha') || stristr($nameLower, 'btq') || stristr($nameLower, 'tadris')) {
                                                            $badgeStyle = "bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-900/60 text-emerald-950 dark:text-emerald-200";
                                                            $accentDot = "bg-emerald-500";
                                                        } 
                                                        elseif (stristr($nameLower, 'pulang') || stristr($nameLower, 'istirahat')) {
                                                            $badgeStyle = "bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-900/60 text-rose-950 dark:text-rose-200";
                                                            $accentDot = "bg-rose-500";
                                                        }
                                                    @endphp

                                                    <div class="matrix-card-item print-badge p-2 mb-1 rounded-xl border shadow-sm transition-all hover:scale-[1.02] {{ $badgeStyle }}" data-variant="{{ strtolower($ev->variant) }}">
                                                        <div class="flex items-center gap-1.5 mb-0.5">
                                                            <span class="w-2 h-2 rounded-full {{ $accentDot }} no-print"></span>
                                                            <span class="font-bold text-xs leading-snug">{{ $ev->event_name }}</span>
                                                        </div>
                                                        <div class="flex items-center justify-between gap-1 pt-0.5 border-t border-black/5 dark:border-white/5">
                                                            <span class="text-[10px] font-mono opacity-75 truncate max-w-[90px]">🎵 {{ $ev->audio_file }}</span>
                                                            @if($ev->variant !== 'default')
                                                                <span class="text-[9px] font-extrabold uppercase bg-white/70 dark:bg-black/40 px-1 py-0.5 rounded text-slate-700 dark:text-slate-300">{{ $ev->variant }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="h-full flex items-center justify-center py-2">
                                                    <span class="text-slate-300 dark:text-slate-700 text-xs font-semibold">–</span>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-12 text-slate-400 dark:text-slate-500 font-medium">
                                        <div class="text-3xl mb-2">📭</div>
                                        Belum ada jadwal bel yang diinputkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- FOOTER MODAL PREVIEW -->
            <div class="px-6 py-3.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/80 flex justify-between items-center no-print">
                <div class="flex flex-wrap gap-4 text-[11px] font-medium text-slate-600 dark:text-slate-300">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Masuk / KBM</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Dhuha / Tadris BTQ</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Event (Upacara, Literasi, Senam)</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Pulang / Istirahat</span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('schedules.print', ['mode' => $activeMode]) }}" target="_blank" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition-all shadow-sm flex items-center gap-1.5 inline-flex">
                        🖨️ Cetak / Print
                    </a>
                    <button onclick="closeViewModal()" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition-all shadow-sm">
                        Tutup Pratinjau
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL POPUP 3: EDIT JADWAL BEL -->
    <div id="modal-edit-schedule" onclick="closeEditModalOnBackdrop(event)" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full flex flex-col shadow-2xl border border-slate-200 dark:border-slate-700 transition-colors duration-300 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                <h3 class="font-bold text-base text-slate-900 dark:text-white flex items-center gap-2">
                    ✏️ Edit Jadwal Bel Sekolah
                </h3>
                <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold text-xl" title="Tutup (Esc)">&times;</button>
            </div>

            <form id="form-edit-schedule" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 block mb-1">Hari</label>
                        <select id="edit-day" name="day" class="w-full text-xs p-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
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
                        <select id="edit-variant" name="variant" class="w-full text-xs p-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
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
                        <input type="time" id="edit-time" name="time" step="1" required class="w-full text-xs p-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 block mb-1">File Audio Sound</label>
                        <select id="edit-audio-file" name="audio_file" class="w-full text-xs p-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                            @foreach($availableAudioFiles as $file)
                                <option value="{{ $file }}">{{ $file }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 block mb-1">Nama Agenda / Kegiatan</label>
                        <input type="text" id="edit-event-name" name="event_name" required class="w-full text-xs p-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-xl transition-all">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                        Perbarui Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <audio id="bel-audio-player" class="hidden"></audio>

    <script>
        const systemActiveMode = "{{ $activeMode }}";

        function filterMatrixPreview(variant) {
            const cards = document.querySelectorAll('.matrix-card-item');
            
            let targetVariant = variant;
            if (variant === 'auto') {
                targetVariant = systemActiveMode;
            }

            cards.forEach(card => {
                const cardVariant = card.getAttribute('data-variant');
                
                if (targetVariant === 'all') {
                    card.style.display = 'block';
                } else if (targetVariant === 'regular') {
                    if (['default', 'jumat_jamaah', 'jumat_ringkas'].includes(cardVariant)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                } else {
                    if (cardVariant === targetVariant) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        }

        function openViewModal() { 
            document.getElementById('modal-view-schedule').classList.remove('hidden'); 
            const filterSelect = document.getElementById('previewVariantFilter');
            if (filterSelect) {
                filterMatrixPreview(filterSelect.value);
            }
        }

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

        let nextScheduleTime = null;
        let nextAudioFile = null;
        let hasPlayedCurrentBel = false;

        let is5sCurrentlyPlaying = false;
        let current5sAudioFile = null;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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

        // MODAL SETTING
        function openModal() { document.getElementById('modal-schedule').classList.remove('hidden'); }
        function closeModal() { document.getElementById('modal-schedule').classList.add('hidden'); }
        function closeModalOnBackdrop(event) { if (event.target.id === 'modal-schedule') closeModal(); }

        // MODAL PRATINJAU
        function closeViewModal() { document.getElementById('modal-view-schedule').classList.add('hidden'); }
        function closeViewModalOnBackdrop(event) { if (event.target.id === 'modal-view-schedule') closeViewModal(); }

        // MODAL EDIT
        function openEditModal(schedule) {
            const form = document.getElementById('form-edit-schedule');
            form.action = `/schedules/${schedule.id}`;

            document.getElementById('edit-day').value = schedule.day.toLowerCase();
            document.getElementById('edit-variant').value = schedule.variant.toLowerCase();
            document.getElementById('edit-time').value = schedule.time;
            document.getElementById('edit-event-name').value = schedule.event_name;
            document.getElementById('edit-audio-file').value = schedule.audio_file;

            document.getElementById('modal-edit-schedule').classList.remove('hidden');
        }
        function closeEditModal() { document.getElementById('modal-edit-schedule').classList.add('hidden'); }
        function closeEditModalOnBackdrop(event) { if (event.target.id === 'modal-edit-schedule') closeEditModal(); }

        window.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' || event.key === 'Esc') {
                closeModal();
                closeViewModal();
                closeEditModal();
            }
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