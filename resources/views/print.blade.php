<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Bel Pelajaran - SMP Muhammadiyah Tonjong</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 4mm 6mm;
        }

        *, ::before, ::after {
            box-sizing: border-box;
        }

        html, body {
            font-family: 'Arial', sans-serif;
            color: #0f172a;
            margin: 0;
            padding: 0;
            background: #fff;
            height: 100vh;
            overflow: hidden;
        }

        .print-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
            justify-content: space-between;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 3px;
            margin-bottom: 4px;
            flex-shrink: 0;
        }

        .header h1 {
            margin: 0;
            font-size: 11pt;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.5px;
            line-height: 1.1;
        }

        .header p {
            margin: 1px 0 0 0;
            font-size: 7.5pt;
            font-weight: bold;
            color: #334155;
        }

        .table-container {
            flex-grow: 1;
            display: flex;
        }

        table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 1px 2px;
            font-size: 6.5pt;
            line-height: 1.1;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            vertical-align: middle;
        }

        th {
            background-color: #0f172a !important;
            color: #ffffff !important;
            text-transform: uppercase;
            font-size: 7.5pt;
            height: 18px;
            padding: 2px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        tr {
            height: 1%;
        }

        .time-col {
            width: 45px;
            text-align: center;
            font-family: monospace;
            font-weight: bold;
            font-size: 6.5pt;
            background-color: #f1f5f9 !important;
            border-right: 2px solid #0f172a;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .card {
            padding: 1.5px 3px;
            border-radius: 3px;
            margin: 0;
            border-width: 1px;
            border-style: solid;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* WARNA KARTU CETAK (PILIHAN) */
        /* KUNING AMBER: Event (Upacara, Literasi, Senam) */
        .card-amber {
            background-color: #fef3c7 !important;
            border-color: #fcd34d !important;
            color: #78350f !important;
        }

        /* HIJAU EMERALD: Dhuha / Tadris BTQ */
        .card-emerald {
            background-color: #d1fae5 !important;
            border-color: #6ee7b7 !important;
            color: #064e3b !important;
        }

        /* BIRU BLUE: Masuk / KBM */
        .card-blue {
            background-color: #dbeafe !important;
            border-color: #93c5fd !important;
            color: #1e3a8a !important;
        }

        /* MERAH ROSE: Pulang & Istirahat */
        .card-rose {
            background-color: #ffe4e6 !important;
            border-color: #fca5a5 !important;
            color: #881337 !important;
        }

        .event-title {
            font-weight: bold;
            font-size: 6.5pt;
            line-height: 1.1;
        }

        .event-variant {
            font-size: 5pt;
            font-weight: bold;
            background: #0f172a;
            color: #fff;
            padding: 0 2px;
            border-radius: 1px;
            display: inline-block;
            float: right;
            line-height: 1;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-data {
            text-align: center;
            color: #cbd5e1;
            font-size: 6pt;
        }

        @media print {
            .no-print { display: none !important; }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body onload="window.print()">

    @php
        $modeLabels = [
            'regular' => 'Jadwal Reguler',
            'puasa'   => 'Jadwal Bulan Puasa (Ramadhan)',
            'asts'    => 'Jadwal ASTS (UTS)',
            'asas'    => 'Jadwal ASAS (UAS)',
            'ujian_sekolah' => 'Jadwal Ujian Sekolah',
        ];

        $currentMode = $activeMode ?? session('active_mode', 'regular');
        $displayModeTitle = $modeTitle ?? ($modeLabels[$currentMode] ?? 'Jadwal Reguler');
    @endphp

    <div class="print-wrapper">
        <div class="header">
            <h1>JADWAL BEL PELAJARAN - SMP MUHAMMADIYAH TONJONG</h1>
            <p>Pemetaan : {{ $displayModeTitle }}</p>
        </div>

        @php
            $matrixDays = [
                'monday' => 'Senin',
                'tuesday' => 'Selasa',
                'wednesday' => 'Rabu',
                'thursday' => 'Kamis',
                'friday' => 'Jumat',
                'saturday' => 'Sabtu'
            ];

            $uniqueTimes = $allSchedules->pluck('time')
                ->map(fn($t) => \Carbon\Carbon::parse($t)->format('H:i'))
                ->unique()
                ->sort()
                ->values();
        @endphp

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 45px;">⏰ JAM</th>
                        @foreach($matrixDays as $dayKey => $dayLabel)
                            <th>{{ $dayLabel }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($uniqueTimes as $timeStr)
                        <tr>
                            <td class="time-col">{{ $timeStr }}</td>
                            @foreach($matrixDays as $dayKey => $dayLabel)
                                @php
                                    $matchingEvents = $allSchedules->filter(function($item) use ($dayKey, $timeStr) {
                                        return strtolower($item->day) === $dayKey && \Carbon\Carbon::parse($item->time)->format('H:i') === $timeStr;
                                    });
                                @endphp
                                <td>
                                    @if($matchingEvents->count() > 0)
                                        @foreach($matchingEvents as $ev)
                                            @php
                                                $nameLower = strtolower($ev->event_name);

                                                // DEFAULT: Biru (Masuk / KBM)
                                                $cardClass = "card-blue";

                                                // KUNING AMBER: Event
                                                if (stristr($nameLower, 'upacara') || stristr($nameLower, 'literasi') || stristr($nameLower, 'senam')) {
                                                    $cardClass = "card-amber";
                                                } 
                                                // HIJAU EMERALD: Dhuha / BTQ
                                                elseif (stristr($nameLower, 'dhuha') || stristr($nameLower, 'btq') || stristr($nameLower, 'tadris')) {
                                                    $cardClass = "card-emerald";
                                                } 
                                                // MERAH ROSE: Pulang / Istirahat
                                                elseif (stristr($nameLower, 'pulang') || stristr($nameLower, 'istirahat')) {
                                                    $cardClass = "card-rose";
                                                }
                                            @endphp

                                            <div class="card {{ $cardClass }}">
                                                @if($ev->variant !== 'default')
                                                    <span class="event-variant">{{ strtoupper($ev->variant) }}</span>
                                                @endif
                                                <div class="event-title">{{ $ev->event_name }}</div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="no-data">–</div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center;">Belum ada data jadwal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>