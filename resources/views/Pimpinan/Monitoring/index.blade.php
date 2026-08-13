{{-- resources/views/Pimpinan/Monitoring/index.blade.php --}}
@extends('Pimpinan.sidebar')
@section('title', 'Monitoring Kehadiran')

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Monitoring Kehadiran</h1>
            <p class="text-sm text-slate-500 mt-0.5 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse inline-block"></span>
                Real-time Â· {{ $today->translatedFormat('l, d F Y') }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('pimpinan.monitoring.statistik') }}"
               class="flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold px-4 py-2 rounded-xl transition">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i> Statistik
            </a>
            <a href="{{ route('pimpinan.monitoring.per-mitra') }}"
               class="flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold px-4 py-2 rounded-xl transition">
                <i data-lucide="building-2" class="w-4 h-4"></i> Per Mitra
            </a>
        </div>
    </div>

    {{-- Kartu Statistik --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:-translate-y-1 transition-all duration-300">
            <p class="text-[10px] font-black text-slate-400 uppercase">Total Karyawan</p>
            <p class="text-3xl font-black text-slate-800 mt-1">{{ $totalKaryawan }}</p>
        </div>
        <div class="bg-emerald-50 rounded-2xl border border-emerald-100 shadow-sm p-4 hover:-translate-y-1 transition-all duration-300">
            <p class="text-[10px] font-black text-emerald-500 uppercase">Hadir</p>
            <p class="text-3xl font-black text-emerald-700 mt-1">{{ $hadirCount }}</p>
            <p class="text-[9px] font-bold text-emerald-500/80 mt-1">{{ $persenHadir }}% dari total</p>
        </div>
        <div class="bg-amber-50 rounded-2xl border border-amber-100 shadow-sm p-4 hover:-translate-y-1 transition-all duration-300">
            <p class="text-[10px] font-black text-amber-500 uppercase">Telat</p>
            <p class="text-3xl font-black text-amber-700 mt-1">{{ $telatCount }}</p>
        </div>
        <div class="bg-blue-50 rounded-2xl border border-blue-100 shadow-sm p-4 hover:-translate-y-1 transition-all duration-300">
            <p class="text-[10px] font-black text-blue-500 uppercase">Izin / Sakit / Cuti</p>
            <p class="text-3xl font-black text-blue-700 mt-1">{{ $izinCount }}</p>
        </div>
        <div class="bg-red-50 rounded-2xl border border-red-100 shadow-sm p-4 hover:-translate-y-1 transition-all duration-300">
            <p class="text-[10px] font-black text-red-500 uppercase">Alfa / Belum Hadir</p>
            <p class="text-3xl font-black text-red-700 mt-1">{{ $belumHadir }}</p>
        </div>
    </div>

    {{-- Pie Chart + Tabel --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">

        {{-- Pie Chart --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col items-center">
            <h2 class="font-black text-slate-600 text-xs tracking-wider mb-4">Kehadiran Hari Ini</h2>
            <div class="relative">
                <canvas id="pieChart" width="180" height="180"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span id="pie-persen" class="text-3xl font-black text-slate-800">{{ $persenHadir }}%</span>
                    <span class="text-xs text-slate-400">Hadir</span>
                </div>
            </div>
            <div class="mt-5 space-y-2 w-full" id="pie-legend">
                @foreach([
                    ['Tepat Waktu',     'bg-emerald-500', $pieData['tepat_waktu']],
                    ['Telat',           'bg-amber-400',   $pieData['telat']],
                    ['Izin/Sakit/Cuti', 'bg-blue-400',    $pieData['izin']],
                    ['Alfa',            'bg-red-500',      $pieData['alfa']],
                    ['Belum Absen',     'bg-slate-300',    $pieData['belum']],
                ] as [$label, $dot, $val])
                <div class="flex items-center justify-between text-xs">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full {{ $dot }} inline-block shrink-0"></span>
                        {{ $label }}
                    </span>
                    <span class="font-bold text-slate-700">{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Area Monitoring --}}
        <div class="lg:col-span-3 space-y-6">
            {{-- Tab Card (Filter Terintegrasi) --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

                {{-- Header: Tab + Filter --}}
                <form method="GET" action="{{ route('pimpinan.monitoring.index') }}">
                    <div class="px-5 pt-3 pb-3 border-b border-slate-100 bg-slate-50 flex flex-wrap items-center gap-2">
                        <button id="btn-tab-tetap" type="button" onclick="switchTabAbsen('tetap')"
                            class="tab-btn-absen active-tab-absen flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all">
                            <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                            Karyawan Tetap
                            <span class="ml-1 px-2 py-0.5 rounded-full text-[9px] font-black bg-teal-100 text-teal-700">
                                {{ $absensiTetap->count() }}
                            </span>
                        </button>
                        <button id="btn-tab-kontrak" type="button" onclick="switchTabAbsen('kontrak')"
                            class="tab-btn-absen flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            Karyawan Kontrak
                            <span class="ml-1 px-2 py-0.5 rounded-full text-[9px] font-black bg-amber-100 text-amber-700">
                                {{ $absensiKontrak->count() }}
                            </span>
                        </button>

                        <div class="ml-auto flex flex-wrap items-center gap-2">
                            <select name="status"
                                class="bg-slate-100 border-none rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-400 transition-all">
                                <option value="">Semua Status</option>
                                <option value="hadir"      @selected($statusFilter=='hadir')>Hadir</option>
                                <option value="telat"      @selected($statusFilter=='telat')>Telat</option>
                                <option value="alfa"       @selected($statusFilter=='alfa')>Alfa</option>
                                <option value="izin"       @selected($statusFilter=='izin')>Izin</option>
                                <option value="sakit"      @selected($statusFilter=='sakit')>Sakit</option>
                                <option value="cuti"       @selected($statusFilter=='cuti')>Cuti</option>
                                <option value="dinas_luar" @selected($statusFilter=='dinas_luar')>Dinas Luar</option>
                                <option value="belum_absen" @selected($statusFilter=='belum_absen')>Belum Absen</option>
                            </select>

                            <select id="filter-mitra-absen" name="mitra_id"
                                class="bg-slate-100 border-none rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-400 transition-all hidden">
                                <option value="">Semua Mitra</option>
                                @foreach($semuaMitra as $m)
                                    <option value="{{ $m->id }}" @selected($m->id == $mitraId)>
                                        @if($m->is_cabang)
                                            ↳ {{ $m->nama_mitra }} (Cabang)
                                        @else
                                            {{ $m->nama_mitra }} (Mitra Utama)
                                        @endif
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit"
                                class="bg-[#1E3A5F] hover:bg-blue-900 text-white text-xs font-black px-4 py-2 rounded-xl transition shadow-md shadow-blue-100 flex items-center gap-1.5 active:scale-95">
                                <i data-lucide="filter" class="w-3 h-3"></i>
                                Filter
                            </button>
                            @if($statusFilter || $mitraId)
                            <a href="{{ route('pimpinan.monitoring.index') }}"
                               class="text-xs font-bold text-slate-400 hover:text-slate-600 px-3 py-2 rounded-xl hover:bg-slate-100 transition">
                                Reset
                            </a>
                            @endif
                        </div>
                    </div>
                </form>

                {{-- Panel: Karyawan Tetap --}}
                <div id="panel-absen-tetap" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-700 text-white text-[10px] font-black uppercase tracking-wider">
                                <th class="px-5 py-3 text-left">Nama Karyawan</th>
                                <th class="px-5 py-3 text-left">Jabatan</th>
                                <th class="px-5 py-3 text-center">Jam Masuk</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($absensiTetap as $abs)
                            @php
                                $sl = match($abs->status) {
                                    'hadir'      => $abs->is_telat ? 'Telat' : 'Tepat Waktu',
                                    'alfa'       => 'Alfa', 'izin' => 'Izin', 'sakit' => 'Sakit',
                                    'cuti'       => 'Cuti', 'dinas_luar' => 'Dinas Luar',
                                    'belum_absen'=> 'Belum Absen',
                                    default      => ucfirst($abs->status),
                                };
                                $sc = match($abs->status) {
                                    'hadir'      => $abs->is_telat ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700',
                                    'alfa'       => 'bg-red-100 text-red-700',
                                    'izin'       => 'bg-purple-100 text-purple-700',
                                    'sakit'      => 'bg-blue-100 text-blue-700',
                                    'cuti'       => 'bg-sky-100 text-sky-700',
                                    'dinas_luar' => 'bg-indigo-100 text-indigo-700',
                                    'belum_absen'=> 'bg-slate-100 text-slate-600',
                                    default      => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <tr class="hover:bg-blue-50/40 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-800">{{ $abs->karyawan?->nama ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-slate-500 text-xs font-semibold">{{ $abs->karyawan?->jabatan ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-center font-mono text-xs font-bold text-slate-700">{{ $abs->waktu_masuk?->format('H:i') ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $sc }}">{{ $sl }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <a href="{{ route('pimpinan.monitoring.detail', $abs->user_id) }}"
                                       class="text-blue-500 hover:text-blue-700 transition" title="Lihat Detail">
                                        <i data-lucide="eye" class="w-4 h-4 inline"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center text-slate-400 font-semibold text-xs">Tidak ada data absensi karyawan tetap hari ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Panel: Karyawan Kontrak --}}
                <div id="panel-absen-kontrak" class="overflow-x-auto hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-700 text-white text-[10px] font-black uppercase tracking-wider">
                                <th class="px-5 py-3 text-left">Nama Karyawan</th>
                                <th class="px-5 py-3 text-left">Jabatan &amp; Mitra</th>
                                <th class="px-5 py-3 text-center">Jam Masuk</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($absensiKontrak as $abs)
                            @php
                                $sl = match($abs->status) {
                                    'hadir'      => $abs->is_telat ? 'Telat' : 'Tepat Waktu',
                                    'alfa'       => 'Alfa', 'izin' => 'Izin', 'sakit' => 'Sakit',
                                    'cuti'       => 'Cuti', 'dinas_luar' => 'Dinas Luar',
                                    'belum_absen'=> 'Belum Absen',
                                    default      => ucfirst($abs->status),
                                };
                                $sc = match($abs->status) {
                                    'hadir'      => $abs->is_telat ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700',
                                    'alfa'       => 'bg-red-100 text-red-700',
                                    'izin'       => 'bg-purple-100 text-purple-700',
                                    'sakit'      => 'bg-blue-100 text-blue-700',
                                    'cuti'       => 'bg-sky-100 text-sky-700',
                                    'dinas_luar' => 'bg-indigo-100 text-indigo-700',
                                    'belum_absen'=> 'bg-slate-100 text-slate-600',
                                    default      => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <tr class="hover:bg-blue-50/40 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-800">{{ $abs->karyawan?->nama ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-xs font-semibold">
                                    <p class="font-bold text-slate-700">{{ $abs->karyawan?->jabatan ?? '-' }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase mt-0.5">{{ $abs->mitra?->nama_mitra ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-center font-mono text-xs font-bold text-slate-700">{{ $abs->waktu_masuk?->format('H:i') ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $sc }}">{{ $sl }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <a href="{{ route('pimpinan.monitoring.detail', $abs->user_id) }}"
                                       class="text-blue-500 hover:text-blue-700 transition" title="Lihat Detail">
                                        <i data-lucide="eye" class="w-4 h-4 inline"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center text-slate-400 font-semibold text-xs">Tidak ada data absensi karyawan kontrak hari ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>{{-- /Tab Card --}}

        </div>
    </div>
</div>


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // â”€â”€ Tab Toggle Absensi â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function switchTabAbsen(tab) {
        const panels = { tetap: 'panel-absen-tetap', kontrak: 'panel-absen-kontrak' };
        const btns   = { tetap: 'btn-tab-tetap',     kontrak: 'btn-tab-kontrak' };
        Object.keys(panels).forEach(key => {
            document.getElementById(panels[key]).classList.toggle('hidden', key !== tab);
            document.getElementById(btns[key]).classList.toggle('active-tab-absen', key === tab);
        });
        // Tampilkan filter Mitra hanya di tab Kontrak
        const mitraFilter = document.getElementById('filter-mitra-absen');
        if (mitraFilter) {
            if (tab === 'kontrak') {
                mitraFilter.classList.remove('hidden');
            } else {
                mitraFilter.classList.add('hidden');
            }
        }
    }

    // Auto-switch ke tab Kontrak jika filter mitra aktif saat halaman dibuka
    document.addEventListener('DOMContentLoaded', function() {
        @if($mitraId)
        switchTabAbsen('kontrak');
        @endif
    });

    // â”€â”€ Pie Chart â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    const pieChart = new Chart(document.getElementById('pieChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Tepat Waktu','Telat','Izin/Sakit/Cuti','Alfa','Belum Absen'],
            datasets: [{
                data: [{{ $pieData['tepat_waktu'] }},{{ $pieData['telat'] }},{{ $pieData['izin'] }},{{ $pieData['alfa'] }},{{ $pieData['belum'] }}],
                backgroundColor: ['#10b981','#f59e0b','#60a5fa','#ef4444','#cbd5e1'],
                borderWidth: 3,
                borderColor: '#fff',
            }]
        },
        options: {
            cutout: '72%',
            plugins: { legend: { display: false }, tooltip: { callbacks: {
                label: ctx => ` ${ctx.label}: ${ctx.raw} karyawan`
            }}},
        }
    });



    // Auto refresh setiap 60 detik
    setTimeout(() => window.location.reload(), 60000);
</script>
<style>
.tab-btn-absen { color: #64748b; background: transparent; }
.tab-btn-absen.active-tab-absen { background: #1E3A5F; color: #fff; }
</style>
@endpush
@endsection
