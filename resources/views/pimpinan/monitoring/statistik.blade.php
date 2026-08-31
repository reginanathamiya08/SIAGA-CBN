@extends('pimpinan.sidebar')
@section('title', 'Statistik Ketidakhadiran')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Statistik Ketidakhadiran</h1>
            <p class="text-sm text-slate-500 mt-0.5">
                {{ $dari->format('d/m/Y') }} — {{ $sampai->format('d/m/Y') }} · Total Hari Kerja: <strong>{{ $totalHariKerja }} hari</strong>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('pimpinan.monitoring.index') }}"
               class="flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold px-4 py-2 rounded-xl transition">
                <i data-lucide="radio" class="w-4 h-4"></i> Real-time
            </a>
            {{-- Export --}}
            <form method="GET" action="{{ route('pimpinan.monitoring.export') }}">
                <input type="hidden" name="dari"          value="{{ $dari->toDateString() }}">
                <input type="hidden" name="sampai"        value="{{ $sampai->toDateString() }}">
                <input type="hidden" name="mitra_id"      value="{{ $mitraId }}">
                <input type="hidden" name="divisi"        value="{{ $divisi }}">
                <input type="hidden" name="jenis_karyawan_id" value="{{ $jenisKaryawan }}">
                <input type="hidden" name="user_id"   value="{{ $karyawanId }}">
                <button type="submit"
                    class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition">
                    <i data-lucide="download" class="w-4 h-4"></i> Export Excel
                </button>
            </form>
        </div>
    </div>

    {{-- Tab Toggle Tetap / Kontrak / Semua --}}
    <div class="flex flex-wrap gap-3">
        {{-- Karyawan Tetap --}}
        <a href="{{ route('pimpinan.monitoring.statistik', array_merge(request()->query(), ['jenis_karyawan_id' => 'tetap', 'mitra_id' => ''])) }}"
           class="flex items-center gap-3 px-5 py-3 rounded-full font-black text-xs transition-all whitespace-nowrap shadow-sm border
                  {{ ($jenisKaryawan === 'tetap' || $jenisKaryawan === 'JNS-00001')
                       ? 'bg-[#1E3A5F] text-white shadow-xl shadow-blue-200/50 border-[#1E3A5F]'
                       : 'bg-white border-slate-200 text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
            <span class="w-2.5 h-2.5 rounded-full bg-[#34D399]"></span>
            Karyawan Tetap
            <span class="bg-emerald-100 text-emerald-800 w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black ml-1">
                {{ $countTetap }}
            </span>
        </a>

        {{-- Karyawan Kontrak --}}
        <a href="{{ route('pimpinan.monitoring.statistik', array_merge(request()->query(), ['jenis_karyawan_id' => 'kontrak'])) }}"
           class="flex items-center gap-3 px-5 py-3 rounded-full font-black text-xs transition-all whitespace-nowrap shadow-sm border
                  {{ ($jenisKaryawan === 'kontrak' || $jenisKaryawan === 'JNS-00002')
                       ? 'bg-[#1E3A5F] text-white shadow-xl shadow-blue-200/50 border-[#1E3A5F]'
                       : 'bg-white border-slate-200 text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
            Karyawan Kontrak
            <span class="bg-amber-100 text-amber-800 w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black ml-1">
                {{ $countKontrak }}
            </span>
        </a>

        {{-- Semua --}}
        <a href="{{ route('pimpinan.monitoring.statistik', array_merge(request()->query(), ['jenis_karyawan_id' => ''])) }}"
           class="flex items-center gap-3 px-5 py-3 rounded-full font-black text-xs transition-all whitespace-nowrap shadow-sm border
                  {{ empty($jenisKaryawan)
                       ? 'bg-[#1E3A5F] text-white shadow-xl shadow-blue-200/50 border-[#1E3A5F]'
                       : 'bg-white border-slate-200 text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
            Semua
            <span class="bg-blue-100 text-blue-800 w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black ml-1">
                {{ $countSemua }}
            </span>
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <form method="GET" action="{{ route('pimpinan.monitoring.statistik') }}"
              class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-{{ $jenisKaryawan === 'JNS-00001' ? '4' : '5' }} gap-3 items-end">
            <input type="hidden" name="jenis_karyawan_id" value="{{ $jenisKaryawan }}">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Dari</label>
                <input type="date" name="dari" value="{{ $dari->toDateString() }}"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Sampai</label>
                <input type="date" name="sampai" value="{{ $sampai->toDateString() }}"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            @if ($jenisKaryawan !== 'JNS-00001')
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Mitra</label>
                <select name="mitra_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Semua Mitra</option>
                    @foreach($semuaMitra as $m)
                        <option value="{{ $m->id }}" @selected($m->id == $mitraId)>{{ $m->nama_mitra }}{{ $m->is_pusat ? ' (Kantor Pusat / Utama)' : '' }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Divisi</label>
                <select name="divisi" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Semua Divisi</option>
                    <option value="hc"             @selected($divisi=='hc')>HC</option>
                    <option value="umum"           @selected($divisi=='umum')>Umum</option>
                    <option value="keuangan"       @selected($divisi=='keuangan')>Keuangan</option>
                    <option value="koordinator_cs" @selected($divisi=='koordinator_cs')>Koordinator CS</option>
                    <option value="adm_umum"       @selected($divisi=='adm_umum')>Adm & Umum</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition">Tampilkan</button>
                <a href="{{ route('pimpinan.monitoring.statistik') }}"
                   class="flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-2 rounded-xl transition">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- 2 Grafik --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Grafik Tren Harian --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <h2 class="font-black text-slate-600 text-xs  tracking-wider mb-4">Ketidakhadiran Per Hari</h2>
            <canvas id="trenChart" height="200"></canvas>
        </div>
        {{-- Top 10 --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <h2 class="font-black text-slate-600 text-xs  tracking-wider mb-4">Top 10 Karyawan — Telat + Alfa Terbanyak</h2>
            <canvas id="top10Chart" height="200"></canvas>
        </div>
    </div>

    {{-- Tabel Rekap Per Karyawan (Dikelompokkan Karyawan Tetap & Kontrak) --}}
    @php
        $rekapTetap   = $rekapTabel->where('is_tetap', true)->values();
        $rekapKontrak = $rekapTabel->where('is_tetap', false)->values();
    @endphp

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden" x-data="{ tabRekap: 'tetap' }">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <button type="button" @click="tabRekap = 'tetap'"
                        :class="tabRekap === 'tetap' ? 'bg-[#1E3A5F] text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black transition-all shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-400"></span>
                    Karyawan Tetap
                    <span :class="tabRekap === 'tetap' ? 'bg-teal-500 text-white' : 'bg-teal-100 text-teal-800'" class="px-2 py-0.5 rounded-full text-[10px] font-black">
                        {{ $rekapTetap->count() }}
                    </span>
                </button>
                <button type="button" @click="tabRekap = 'kontrak'"
                        :class="tabRekap === 'kontrak' ? 'bg-[#1E3A5F] text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black transition-all shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                    Karyawan Kontrak
                    <span :class="tabRekap === 'kontrak' ? 'bg-amber-500 text-white' : 'bg-amber-100 text-amber-800'" class="px-2 py-0.5 rounded-full text-[10px] font-black">
                        {{ $rekapKontrak->count() }}
                    </span>
                </button>
            </div>
            <span class="text-xs text-slate-400 font-medium">*Merah = kehadiran &lt; 80%</span>
        </div>

        {{-- Panel Karyawan Tetap --}}
        <div x-show="tabRekap === 'tetap'" class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#1E3A5F] text-white text-xs font-bold tracking-wider">
                        <th class="px-4 py-3 text-left">No.</th>
                        <th class="px-4 py-3 text-left">Nama Karyawan</th>
                        <th class="px-4 py-3 text-left">Jabatan</th>
                        <th class="px-4 py-3 text-left">Mitra / Cabang</th>
                        <th class="px-4 py-3 text-center">Hadir</th>
                        <th class="px-4 py-3 text-center">Telat</th>
                        <th class="px-4 py-3 text-center">Alfa</th>
                        <th class="px-4 py-3 text-center">Izin</th>
                        <th class="px-4 py-3 text-center">Sakit</th>
                        <th class="px-4 py-3 text-center">Cuti</th>
                        <th class="px-4 py-3 text-center">Dinas</th>
                        <th class="px-4 py-3 text-center">% Hadir</th>
                        <th class="px-4 py-3 text-center">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rekapTetap as $i => $r)
                    @php $merah = $r['persen'] < 80; @endphp
                    <tr class="{{ $i%2===0 ? 'bg-white':'bg-slate-50' }} {{ $merah ? 'hover:bg-red-50':'hover:bg-blue-50' }}">
                        <td class="px-4 py-3 text-slate-400">{{ $i+1 }}</td>
                        <td class="px-4 py-3 font-semibold {{ $merah ? 'text-red-600':'text-slate-800' }}">{{ $r['nama'] }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $r['jabatan'] }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $r['mitra'] }}</td>
                        <td class="px-4 py-3 text-center font-bold text-emerald-600">{{ $r['hadir'] }}</td>
                        <td class="px-4 py-3 text-center font-bold text-amber-500">{{ $r['telat'] }}</td>
                        <td class="px-4 py-3 text-center font-bold text-red-500">{{ $r['alfa'] }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $r['izin'] }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $r['sakit'] }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $r['cuti'] }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $r['dinas'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold
                                {{ $merah ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $r['persen'] }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($r['id'])
                            <a href="{{ route('pimpinan.monitoring.detail', $r['id']) }}"
                               class="text-blue-500 hover:text-blue-700">
                                <i data-lucide="eye" class="w-4 h-4 inline"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="13" class="px-4 py-12 text-center text-slate-400 font-medium">Tidak ada data rekap karyawan tetap.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Panel Karyawan Kontrak --}}
        <div x-show="tabRekap === 'kontrak'" class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#1E3A5F] text-white text-xs font-bold tracking-wider">
                        <th class="px-4 py-3 text-left">No.</th>
                        <th class="px-4 py-3 text-left">Nama Karyawan</th>
                        <th class="px-4 py-3 text-left">Jabatan</th>
                        <th class="px-4 py-3 text-left">Mitra / Cabang</th>
                        <th class="px-4 py-3 text-center">Hadir</th>
                        <th class="px-4 py-3 text-center">Telat</th>
                        <th class="px-4 py-3 text-center">Alfa</th>
                        <th class="px-4 py-3 text-center">Izin</th>
                        <th class="px-4 py-3 text-center">Sakit</th>
                        <th class="px-4 py-3 text-center">Cuti</th>
                        <th class="px-4 py-3 text-center">Dinas</th>
                        <th class="px-4 py-3 text-center">% Hadir</th>
                        <th class="px-4 py-3 text-center">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rekapKontrak as $i => $r)
                    @php $merah = $r['persen'] < 80; @endphp
                    <tr class="{{ $i%2===0 ? 'bg-white':'bg-slate-50' }} {{ $merah ? 'hover:bg-red-50':'hover:bg-blue-50' }}">
                        <td class="px-4 py-3 text-slate-400">{{ $i+1 }}</td>
                        <td class="px-4 py-3 font-semibold {{ $merah ? 'text-red-600':'text-slate-800' }}">{{ $r['nama'] }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $r['jabatan'] }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $r['mitra'] }}</td>
                        <td class="px-4 py-3 text-center font-bold text-emerald-600">{{ $r['hadir'] }}</td>
                        <td class="px-4 py-3 text-center font-bold text-amber-500">{{ $r['telat'] }}</td>
                        <td class="px-4 py-3 text-center font-bold text-red-500">{{ $r['alfa'] }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $r['izin'] }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $r['sakit'] }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $r['cuti'] }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $r['dinas'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold
                                {{ $merah ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $r['persen'] }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($r['id'])
                            <a href="{{ route('pimpinan.monitoring.detail', $r['id']) }}"
                               class="text-blue-500 hover:text-blue-700">
                                <i data-lucide="eye" class="w-4 h-4 inline"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="13" class="px-4 py-12 text-center text-slate-400 font-medium">Tidak ada data rekap karyawan kontrak.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const trenLabels = @json($trenPerHari->keys());
    const trenData   = @json($trenPerHari->values());

    new Chart(document.getElementById('trenChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: trenLabels,
            datasets: [{
                label: 'Tidak Hadir',
                data: trenData,
                backgroundColor: 'rgba(239,68,68,0.7)',
                borderRadius: 6,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    const top10Names = @json($top10->pluck('nama'));
    const top10Vals  = @json($top10->pluck('total'));

    new Chart(document.getElementById('top10Chart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: top10Names,
            datasets: [{
                label: 'Telat + Alfa',
                data: top10Vals,
                backgroundColor: 'rgba(245,158,11,0.75)',
                borderRadius: 6,
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
</script>
@endpush
@endsection
