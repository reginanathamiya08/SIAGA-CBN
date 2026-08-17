{{-- resources/views/Pimpinan/Monitoring/per-mitra.blade.php --}}
@extends('pimpinan.sidebar')

@section('title', 'Monitoring Per Mitra')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Monitoring Per Mitra</h1>
            <p class="text-sm text-slate-500 mt-0.5">Data kehadiran per mitra / cabang</p>
        </div>
        <a href="{{ route('pimpinan.monitoring.index') }}"
           class="flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold px-4 py-2 rounded-xl transition">
            ← Kembali
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <form method="GET" action="{{ route('pimpinan.monitoring.per-mitra') }}" class="flex flex-wrap gap-2 items-end">
            <select name="mitra_id" class="rounded-xl border border-slate-200 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Semua Mitra</option>
                @foreach($semuaMitra as $m)
                    <option value="{{ $m->id }}" @selected($m->id == $mitraId)>{{ $m->nama_mitra }}{{ $m->is_pusat ? ' (Kantor Pusat / Utama)' : '' }}</option>
                @endforeach
            </select>
            <select name="bulan" class="rounded-xl border border-slate-200 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-400">
                @foreach(range(1,12) as $b)
                    <option value="{{ $b }}" @selected($b == $bulan)>{{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}</option>
                @endforeach
            </select>
            <select name="tahun" class="rounded-xl border border-slate-200 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-400">
                @foreach(range(now()->year - 2, now()->year) as $t)
                    <option value="{{ $t }}" @selected($t == $tahun)>{{ $t }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition">Filter</button>
            <a href="{{ route('pimpinan.monitoring.per-mitra') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-600 text-xs font-bold px-3 py-2 rounded-xl transition">Reset</a>
        </form>
    </div>

    {{-- Kartu Ringkasan Per Mitra --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($dataMitra as $item)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-black text-slate-700 text-sm">{{ $item['mitra']->nama_mitra }}</h2>
                <span class="text-xs text-slate-400">{{ $item['total_karyawan'] }} karyawan</span>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="bg-emerald-50 rounded-xl p-2">
                    <p class="text-lg font-black text-emerald-700">{{ $item['hadir'] }}</p>
                    <p class="text-xs text-emerald-500 font-bold">Hadir</p>
                </div>
                <div class="bg-amber-50 rounded-xl p-2">
                    <p class="text-lg font-black text-amber-700">{{ $item['telat'] }}</p>
                    <p class="text-xs text-amber-500 font-bold">Telat</p>
                </div>
                <div class="bg-red-50 rounded-xl p-2">
                    <p class="text-lg font-black text-red-700">{{ $item['alfa'] }}</p>
                    <p class="text-xs text-red-500 font-bold">Alfa</p>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex justify-between text-xs text-slate-500 mb-1">
                    <span>Kehadiran</span>
                    <span class="font-bold">{{ $item['persen'] }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2">
                    <div class="h-2 rounded-full {{ $item['persen'] >= 80 ? 'bg-emerald-500' : ($item['persen'] >= 60 ? 'bg-amber-400' : 'bg-red-500') }}"
                         style="width: {{ $item['persen'] }}%"></div>
                </div>
            </div>
            <div class="mt-3 text-right">
                <a href="{{ route('pimpinan.monitoring.per-mitra', ['mitra_id' => $item['mitra']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                   class="text-xs text-blue-500 hover:text-blue-700 font-bold transition">Lihat Detail →</a>
            </div>
        </div>
        @empty
        <div class="col-span-3 bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400">
            <p class="text-lg font-bold">Tidak ada data mitra.</p>
        </div>
        @endforelse
    </div>

    {{-- Tabel Detail Karyawan (muncul jika mitra dipilih) --}}
    @if($mitraId && count($karyawanMitra) > 0)
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h2 class="font-black text-slate-700">Detail Karyawan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-700 text-white text-xs font-bold  tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama Karyawan</th>
                        <th class="px-4 py-3 text-left">Jabatan</th>
                        <th class="px-4 py-3 text-center">Hadir</th>
                        <th class="px-4 py-3 text-center">Telat</th>
                        <th class="px-4 py-3 text-center">Izin</th>
                        <th class="px-4 py-3 text-center">Sakit</th>
                        <th class="px-4 py-3 text-center">Alfa</th>
                        <th class="px-4 py-3 text-center">Dinas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($karyawanMitra as $i => $k)
                    <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50' }} hover:bg-blue-50 transition">
                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $k['nama'] }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $k['jabatan'] }}</td>
                        <td class="px-4 py-3 text-center text-emerald-600 font-bold">{{ $k['hadir'] }}</td>
                        <td class="px-4 py-3 text-center text-amber-600 font-bold">{{ $k['telat'] }}</td>
                        <td class="px-4 py-3 text-center text-purple-600 font-bold">{{ $k['izin'] }}</td>
                        <td class="px-4 py-3 text-center text-blue-600 font-bold">{{ $k['sakit'] }}</td>
                        <td class="px-4 py-3 text-center text-red-600 font-bold">{{ $k['alfa'] }}</td>
                        <td class="px-4 py-3 text-center text-indigo-600 font-bold">{{ $k['dinas'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
