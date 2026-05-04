{{-- resources/views/Pimpinan/Monitoring/index.blade.php --}}
@extends('Pimpinan.sidebar')
@section('title', 'Monitoring Kehadiran')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Monitoring Kehadiran</h1>
            <p class="text-sm text-slate-500 mt-0.5 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse inline-block"></span>
                Real-time · {{ $today->translatedFormat('l, d F Y') }}
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

    {{-- Tab Toggle Tetap / Kontrak / Semua --}}
    <div class="flex gap-2 bg-slate-100 rounded-2xl p-1.5 w-fit">
        <a href="{{ route('pimpinan.monitoring.index', ['tab' => 'tetap']) }}"
        class="px-5 py-2 rounded-xl text-sm font-bold transition
            {{ $tab === 'tetap' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Karyawan Tetap
        </a>
        <a href="{{ route('pimpinan.monitoring.index', ['tab' => 'kontrak']) }}"
        class="px-5 py-2 rounded-xl text-sm font-bold transition
            {{ $tab === 'kontrak' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Karyawan Kontrak
        </a>
        <a href="{{ route('pimpinan.monitoring.index', ['tab' => 'semua']) }}"
        class="px-5 py-2 rounded-xl text-sm font-bold transition
            {{ $tab === 'semua' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Semua
        </a>
    </div>
<form method="GET" action="{{ route('pimpinan.monitoring.index') }}" class="flex flex-wrap gap-2 items-center">
    <input type="hidden" name="tab" value="{{ $tab }}">
</form>

    {{-- Kartu Statistik — Tetap --}}
    <div id="stats-tetap" class="stats-panel grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <p class="text-xs font-bold text-slate-400 tracking-wider">Total Tetap</p>
            <p class="text-3xl font-black text-slate-800 mt-1">{{ $stats['tetap']['total'] }}</p>
        </div>
        <div class="bg-emerald-50 rounded-2xl border border-emerald-100 shadow-sm p-4">
            <p class="text-xs font-bold text-emerald-500 tracking-wider">Hadir</p>
            <p class="text-3xl font-black text-emerald-700 mt-1">{{ $stats['tetap']['hadir'] }}</p>
            <p class="text-xs text-emerald-500 mt-1">{{ $stats['tetap']['persen_hadir'] }}% dari total</p>
        </div>
        <div class="bg-amber-50 rounded-2xl border border-amber-100 shadow-sm p-4">
            <p class="text-xs font-bold text-amber-500 tracking-wider">Telat</p>
            <p class="text-3xl font-black text-amber-700 mt-1">{{ $stats['tetap']['telat'] }}</p>
        </div>
        <div class="bg-blue-50 rounded-2xl border border-blue-100 shadow-sm p-4">
            <p class="text-xs font-bold text-blue-500 tracking-wider">Izin / Sakit / Cuti</p>
            <p class="text-3xl font-black text-blue-700 mt-1">{{ $stats['tetap']['izin'] }}</p>
        </div>
        <div class="bg-red-50 rounded-2xl border border-red-100 shadow-sm p-4">
            <p class="text-xs font-bold text-red-500 tracking-wider">Alfa / Belum Hadir</p>
            <p class="text-3xl font-black text-red-700 mt-1">{{ $stats['tetap']['belum'] }}</p>
        </div>
    </div>

    {{-- Kartu Statistik — Kontrak --}}
    <div id="stats-kontrak" class="stats-panel hidden grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <p class="text-xs font-bold text-slate-400 tracking-wider">Total Kontrak</p>
            <p class="text-3xl font-black text-slate-800 mt-1">{{ $stats['kontrak']['total'] }}</p>
        </div>
        <div class="bg-emerald-50 rounded-2xl border border-emerald-100 shadow-sm p-4">
            <p class="text-xs font-bold text-emerald-500 tracking-wider">Hadir</p>
            <p class="text-3xl font-black text-emerald-700 mt-1">{{ $stats['kontrak']['hadir'] }}</p>
            <p class="text-xs text-emerald-500 mt-1">{{ $stats['kontrak']['persen_hadir'] }}% dari total</p>
        </div>
        <div class="bg-amber-50 rounded-2xl border border-amber-100 shadow-sm p-4">
            <p class="text-xs font-bold text-amber-500 tracking-wider">Telat</p>
            <p class="text-3xl font-black text-amber-700 mt-1">{{ $stats['kontrak']['telat'] }}</p>
        </div>
        <div class="bg-blue-50 rounded-2xl border border-blue-100 shadow-sm p-4">
            <p class="text-xs font-bold text-blue-500 tracking-wider">Izin / Sakit / Cuti</p>
            <p class="text-3xl font-black text-blue-700 mt-1">{{ $stats['kontrak']['izin'] }}</p>
        </div>
        <div class="bg-red-50 rounded-2xl border border-red-100 shadow-sm p-4">
            <p class="text-xs font-bold text-red-500 tracking-wider">Alfa / Belum Hadir</p>
            <p class="text-3xl font-black text-red-700 mt-1">{{ $stats['kontrak']['belum'] }}</p>
        </div>
    </div>

    {{-- Kartu Statistik — Semua --}}
    <div id="stats-semua" class="stats-panel hidden grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <p class="text-xs font-bold text-slate-400 tracking-wider">Total Karyawan</p>
            <p class="text-3xl font-black text-slate-800 mt-1">{{ $totalKaryawan }}</p>
        </div>
        <div class="bg-emerald-50 rounded-2xl border border-emerald-100 shadow-sm p-4">
            <p class="text-xs font-bold text-emerald-500 tracking-wider">Hadir</p>
            <p class="text-3xl font-black text-emerald-700 mt-1">{{ $hadirCount }}</p>
            <p class="text-xs text-emerald-500 mt-1">{{ $persenHadir }}% dari total</p>
        </div>
        <div class="bg-amber-50 rounded-2xl border border-amber-100 shadow-sm p-4">
            <p class="text-xs font-bold text-amber-500 tracking-wider">Telat</p>
            <p class="text-3xl font-black text-amber-700 mt-1">{{ $telatCount }}</p>
        </div>
        <div class="bg-blue-50 rounded-2xl border border-blue-100 shadow-sm p-4">
            <p class="text-xs font-bold text-blue-500 tracking-wider">Izin / Sakit / Cuti</p>
            <p class="text-3xl font-black text-blue-700 mt-1">{{ $izinCount }}</p>
        </div>
        <div class="bg-red-50 rounded-2xl border border-red-100 shadow-sm p-4">
            <p class="text-xs font-bold text-red-500 tracking-wider">Alfa / Belum Hadir</p>
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

        {{-- Tabel Karyawan --}}
        <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">

            {{-- Filter --}}
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
                <form method="GET" action="{{ route('pimpinan.monitoring.index') }}"
                      class="flex flex-wrap gap-2 items-center">
                    {{-- Kirim tab aktif agar filter & halaman konsisten --}}
                    <input type="hidden" name="tab" id="hidden-tab" value="{{ request('tab', 'tetap') }}">
                    <select name="mitra_id"
                        class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">Semua Mitra</option>
                        @foreach($semuaMitra as $m)
                            <option value="{{ $m->id }}" @selected($m->id == $mitraId)>{{ $m->nama_mitra }}</option>
                        @endforeach
                    </select>
                    <select name="status"
                        class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">Semua Status</option>
                        <option value="hadir"      @selected($statusFilter=='hadir')>Hadir</option>
                        <option value="telat"      @selected($statusFilter=='telat')>Telat</option>
                        <option value="alfa"       @selected($statusFilter=='alfa')>Alfa</option>
                        <option value="izin"       @selected($statusFilter=='izin')>Izin</option>
                        <option value="sakit"      @selected($statusFilter=='sakit')>Sakit</option>
                        <option value="cuti"       @selected($statusFilter=='cuti')>Cuti</option>
                        <option value="dinas_luar" @selected($statusFilter=='dinas_luar')>Dinas Luar</option>
                    </select>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-1.5 rounded-xl transition">
                        Filter
                    </button>
                    <a href="{{ route('pimpinan.monitoring.index') }}"
                       class="bg-slate-200 hover:bg-slate-300 text-slate-600 text-xs font-bold px-3 py-1.5 rounded-xl transition">
                        Reset
                    </a>
                </form>
            </div>

            {{-- Tabel --}}
            <div class="overflow-x-auto overflow-y-auto flex-1" style="max-height:420px">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-slate-700 text-white text-xs font-bold tracking-wider">
                            <th class="px-4 py-3 text-left">Nama Karyawan</th>
                            <th class="px-4 py-3 text-left">Jabatan</th>
                            <th class="px-4 py-3 text-center">Jenis</th>
                            <th class="px-4 py-3 text-left">Mitra / Cabang</th>
                            <th class="px-4 py-3 text-center">Jam Masuk</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($absensiHariIni as $i => $abs)
                        @php
                            $sl = match($abs->status) {
                                'hadir'      => $abs->is_telat ? 'Telat' : 'Tepat Waktu',
                                'alfa'       => 'Alfa',
                                'izin'       => 'Izin',
                                'sakit'      => 'Sakit',
                                'cuti'       => 'Cuti',
                                'dinas_luar' => 'Dinas Luar',
                                default      => ucfirst($abs->status),
                            };
                            $sc = match($abs->status) {
                                'hadir'      => $abs->is_telat ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700',
                                'alfa'       => 'bg-red-100 text-red-700',
                                'izin'       => 'bg-purple-100 text-purple-700',
                                'sakit'      => 'bg-blue-100 text-blue-700',
                                'cuti'       => 'bg-sky-100 text-sky-700',
                                'dinas_luar' => 'bg-indigo-100 text-indigo-700',
                                default      => 'bg-slate-100 text-slate-600',
                            };
                            $isTetap = $abs->karyawan?->jenis_karyawan === 'tetap';
                        @endphp
                        <tr class="{{ $i%2===0 ? 'bg-white':'bg-slate-50' }} hover:bg-blue-50 transition">
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $abs->karyawan?->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $abs->karyawan?->jabatan ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold
                                    {{ $isTetap ? 'bg-teal-100 text-teal-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $isTetap ? 'Tetap' : 'Kontrak' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-500 text-xs">
                                {{ $abs->mitra?->nama_mitra ?? ($isTetap ? 'Kantor CBN' : '-') }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono text-slate-700">
                                {{ $abs->waktu_masuk?->format('H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="... {{ $isTetap ? 'bg-teal-100 text-teal-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $isTetap ? 'Tetap' : 'Kontrak' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('pimpinan.monitoring.detail', $abs->karyawan_id) }}"
                                   class="text-blue-500 hover:text-blue-700 transition" title="Lihat Detail">
                                    <i data-lucide="eye" class="w-4 h-4 inline"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                Belum ada data absensi hari ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ── Pie Chart ──────────────────────────────────────────────
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

    // ── Pie data per tab (dari PHP) ────────────────────────────
    const pieDataPerTab = {
        tetap:   { tepat_waktu: {{ $pieDataTetap['tepat_waktu'] }}, telat: {{ $pieDataTetap['telat'] }}, izin: {{ $pieDataTetap['izin'] }}, alfa: {{ $pieDataTetap['alfa'] }}, belum: {{ $pieDataTetap['belum'] }}, persen: {{ $stats['tetap']['persen_hadir'] }} },
        kontrak: { tepat_waktu: {{ $pieDataKontrak['tepat_waktu'] }}, telat: {{ $pieDataKontrak['telat'] }}, izin: {{ $pieDataKontrak['izin'] }}, alfa: {{ $pieDataKontrak['alfa'] }}, belum: {{ $pieDataKontrak['belum'] }}, persen: {{ $stats['kontrak']['persen_hadir'] }} },
        semua:   { tepat_waktu: {{ $pieData['tepat_waktu'] }}, telat: {{ $pieData['telat'] }}, izin: {{ $pieData['izin'] }}, alfa: {{ $pieData['alfa'] }}, belum: {{ $pieData['belum'] }}, persen: {{ $persenHadir }} },
    };

    // ── Tab switching ──────────────────────────────────────────
    function switchTab(tab) {
        // Tombol aktif
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('bg-white','text-slate-800','shadow-sm');
            b.classList.add('text-slate-500');
        });
        const activeBtn = document.getElementById('tab-' + tab);
        activeBtn.classList.add('bg-white','text-slate-800','shadow-sm');
        activeBtn.classList.remove('text-slate-500');

        // Stats panel
        document.querySelectorAll('.stats-panel').forEach(p => p.classList.add('hidden'));
        document.getElementById('stats-' + tab).classList.remove('hidden');

        // Simpan tab ke hidden input agar filter form tahu
        document.getElementById('hidden-tab').value = tab;

        // Update pie chart
        const d = pieDataPerTab[tab];
        pieChart.data.datasets[0].data = [d.tepat_waktu, d.telat, d.izin, d.alfa, d.belum];
        pieChart.update();
        document.getElementById('pie-persen').textContent = d.persen + '%';
    }

    // Init tab sesuai request
    switchTab('{{ request('tab', 'tetap') }}');

    // Auto refresh setiap 60 detik
    setTimeout(() => window.location.reload(), 60000);
</script>
@endpush
@endsection