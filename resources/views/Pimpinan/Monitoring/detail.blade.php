{{-- resources/views/Pimpinan/Monitoring/detail.blade.php --}}
@extends('Pimpinan.sidebar')
@section('title', 'Detail Absensi — ' . $karyawan->nama)

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ url()->previous() }}"
           class="flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 transition">
            <i data-lucide="arrow-left" class="w-4 h-4 text-slate-600"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-800">Detail Absensi Karyawan</h1>
            <p class="text-sm text-slate-500 mt-0.5">{{ $dari->format('d/m/Y') }} — {{ $sampai->format('d/m/Y') }}</p>
        </div>
    </div>

    {{-- Profil Karyawan --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs font-bold text-slate-400  tracking-wider">Nama</p>
                <p class="font-bold text-slate-800 mt-1">{{ $karyawan->nama }}</p>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400  tracking-wider">Jabatan</p>
                <p class="font-semibold text-slate-700 mt-1">{{ $karyawan->jabatan ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400  tracking-wider">Divisi</p>
                <p class="font-semibold text-slate-700 mt-1">{{ $karyawan->labelDivisi() }}</p>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400  tracking-wider">Penempatan</p>
                <p class="font-semibold text-slate-700 mt-1">
                    {{ $karyawan->penempatanAktif?->mitra?->nama_mitra ?? ($karyawan->isTetap() ? 'Kantor CBN' : '-') }}
                </p>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400  tracking-wider">Jenis Karyawan</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold mt-1
                    {{ $karyawan->isTetap() ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                    {{ $karyawan->jenisKaryawan?->nama_jenis ?? '-' }}
                </span>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400  tracking-wider">Sisa Kuota Perizinan {{ now()->year }}</p>
                <p class="font-bold text-slate-800 mt-1">
                    {{ $kuotaCuti ? $kuotaCuti->sisa . ' hari' : \App\Models\Configuration::getValue('kuota_cuti_tahunan') . ' hari' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Rekap Periode --}}
    <div class="grid grid-cols-4 md:grid-cols-7 gap-3">
        @foreach([
            ['Hadir',    $rekap['hadir'], 'bg-emerald-50 border-emerald-100 text-emerald-700'],
            ['Telat',    $rekap['telat'], 'bg-amber-50 border-amber-100 text-amber-700'],
            ['Alfa',     $rekap['alfa'],  'bg-red-50 border-red-100 text-red-700'],
            ['Izin',     $rekap['izin'],  'bg-purple-50 border-purple-100 text-purple-700'],
            ['Sakit',    $rekap['sakit'], 'bg-blue-50 border-blue-100 text-blue-700'],
            ['Cuti',     $rekap['cuti'],  'bg-sky-50 border-sky-100 text-sky-700'],
            ['Dinas',    $rekap['dinas'], 'bg-indigo-50 border-indigo-100 text-indigo-700'],
        ] as [$label, $val, $cls])
        <div class="rounded-2xl border {{ $cls }} p-3 text-center">
            <p class="text-xs font-bold  tracking-wider opacity-70">{{ $label }}</p>
            <p class="text-2xl font-black mt-1">{{ $val }}</p>
        </div>
        @endforeach
    </div>

    {{-- Filter Rentang --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <form method="GET" action="{{ route('pimpinan.monitoring.detail', $karyawan->id) }}"
              class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Dari</label>
                <input type="date" name="dari" value="{{ $dari->toDateString() }}"
                    class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Sampai</label>
                <input type="date" name="sampai" value="{{ $sampai->toDateString() }}"
                    class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Status</label>
                <select name="status" class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Semua Status</option>
                    <option value="hadir"      @selected($statusFilter=='hadir')>Hadir</option>
                    <option value="telat"      @selected($statusFilter=='telat')>Telat</option>
                    <option value="alfa"       @selected($statusFilter=='alfa')>Alfa</option>
                    <option value="izin"       @selected($statusFilter=='izin')>Izin</option>
                    <option value="sakit"      @selected($statusFilter=='sakit')>Sakit</option>
                    <option value="cuti"       @selected($statusFilter=='cuti')>Cuti</option>
                    <option value="dinas_luar" @selected($statusFilter=='dinas_luar')>Dinas Luar</option>
                </select>
            </div>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-5 py-2 rounded-xl transition">
                Tampilkan
            </button>
        </form>
    </div>

    {{-- Tabel Riwayat + GPS --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-black text-slate-700 text-sm  tracking-wider">
                Riwayat Absensi Lengkap
                <span class="ml-2 text-xs text-slate-400 font-normal normal-case">Klik koordinat GPS untuk buka peta</span>
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-700 text-white text-xs font-bold  tracking-wider">
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-center">Jam Masuk</th>
                        <th class="px-4 py-3 text-left">GPS Masuk</th>
                        <th class="px-4 py-3 text-center">Jam Pulang</th>
                        <th class="px-4 py-3 text-left">GPS Pulang</th>
                        <th class="px-4 py-3 text-center">Durasi</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($riwayat as $i => $abs)
                    @php
                        $sl = match($abs->status) {
                            'hadir'      => $abs->is_telat ? 'Telat' : 'Tepat Waktu',
                            'alfa'       => 'Alfa',
                            'izin'       => 'Izin Pribadi',
                            'sakit'      => 'Sakit',
                            'cuti'       => 'Cuti',
                            'dinas_luar' => 'Dinas Luar Kota',
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
                        $gpsMsk  = ($abs->lat_masuk  && $abs->long_masuk)  ? "{$abs->lat_masuk}, {$abs->long_masuk}"   : null;
                        $gpsPlg  = ($abs->lat_pulang && $abs->long_pulang) ? "{$abs->lat_pulang}, {$abs->long_pulang}" : null;
                        $urlMsk  = $gpsMsk ? "https://www.google.com/maps?q={$abs->lat_masuk},{$abs->long_masuk}" : null;
                        $urlPlg  = $gpsPlg ? "https://www.google.com/maps?q={$abs->lat_pulang},{$abs->long_pulang}" : null;
                        $durasi  = $abs->durasiMenit();
                    @endphp
                    <tr class="{{ $i%2===0 ? 'bg-white':'bg-slate-50' }} hover:bg-blue-50 transition">
                        <td class="px-4 py-3 font-semibold text-slate-700">
                            {{ $abs->tanggal->translatedFormat('D, d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-center font-mono text-slate-700">
                            {{ $abs->waktu_masuk?->format('H:i') ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-xs">
                            @if($urlMsk)
                                <a href="{{ $urlMsk }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-blue-500 hover:text-blue-700 transition font-mono">
                                    <i data-lucide="map-pin" class="w-3 h-3 shrink-0"></i>
                                    {{ $gpsMsk }}
                                </a>
                            @elseif(in_array($abs->status, ['izin','sakit','cuti','dinas_luar']))
                                <span class="text-slate-400 italic">— {{ $sl }}</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-mono text-slate-700">
                            {{ $abs->waktu_pulang?->format('H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-xs">
                            @if($urlPlg)
                                <a href="{{ $urlPlg }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-blue-500 hover:text-blue-700 transition font-mono">
                                    <i data-lucide="map-pin" class="w-3 h-3 shrink-0"></i>
                                    {{ $gpsPlg }}
                                </a>
                            @elseif(!$abs->waktu_pulang && $abs->waktu_masuk)
                                <span class="text-amber-500 italic text-xs">Belum absen pulang</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-slate-600 text-xs">
                            @if($durasi !== null)
                                {{ floor($durasi / 60) }}j {{ $durasi % 60 }}m
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $sc }}">
                                {{ $sl }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-slate-400 text-xs">-</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                            Tidak ada data riwayat absensi untuk rentang tanggal ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
