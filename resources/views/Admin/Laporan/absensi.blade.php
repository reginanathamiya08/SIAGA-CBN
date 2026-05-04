{{-- resources/views/Admin/Laporan/absensi.blade.php --}}
@extends('Admin.sidebar')

@section('title', 'Laporan Absensi')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Laporan Absensi</h1>
            <p class="text-sm text-slate-500 mt-0.5">Rekap kehadiran karyawan per periode</p>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <form method="GET" action="{{ route('admin.laporan.absensi.index') }}"
              class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 items-end">

            {{-- Bulan --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Bulan</label>
                <select name="bulan"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" @selected($m == $bulan)>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tahun --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Tahun</label>
                <select name="tahun"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    @foreach(range(now()->year - 2, now()->year) as $y)
                        <option value="{{ $y }}" @selected($y == $tahun)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Mitra --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Mitra / Cabang</label>
                <select name="mitra_id"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Semua Mitra</option>
                    @foreach($semuaMitra as $m)
                        <option value="{{ $m->id }}" @selected($m->id == $mitraId)>{{ $m->nama_mitra }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Jenis Karyawan --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Jenis Karyawan</label>
                <select name="jenis_karyawan"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Semua</option>
                    <option value="tetap"   @selected($jenisKaryawan == 'tetap')>Tetap</option>
                    <option value="kontrak" @selected($jenisKaryawan == 'kontrak')>Kontrak</option>
                </select>
            </div>

            {{-- Divisi --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Divisi</label>
                <select name="divisi"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Semua Divisi</option>
                    <option value="hc"             @selected($divisi == 'hc')>HC</option>
                    <option value="umum"           @selected($divisi == 'umum')>Umum</option>
                    <option value="keuangan"       @selected($divisi == 'keuangan')>Keuangan</option>
                    <option value="koordinator_cs" @selected($divisi == 'koordinator_cs')>Koordinator CS</option>
                    <option value="adm_umum"       @selected($divisi == 'adm_umum')>Adm &amp; Umum</option>
                </select>
            </div>

            {{-- Tombol --}}
            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition">
                    Tampilkan
                </button>
                <a href="{{ route('admin.laporan.absensi.index') }}"
                   class="flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-2 rounded-xl transition"
                   title="Reset">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Info & Export --}}
    @if($absensiList->count() > 0)

    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">
            Menampilkan <span class="font-bold text-slate-700">{{ $absensiList->count() }}</span> record —
            Total hari kerja bulan ini: <span class="font-bold text-blue-600">{{ $totalHariKerja }} hari</span>
        </p>

        {{-- Tombol Export Excel — gunakan <a> dengan target="_blank" agar download otomatis --}}
        <a href="{{ route('admin.laporan.absensi.export', [
                'bulan'          => $bulan,
                'tahun'          => $tahun,
                'mitra_id'       => $mitraId,
                'divisi'         => $divisi,
                'jenis_karyawan' => $jenisKaryawan,
                'karyawan_id'    => $karyawanId,
            ]) }}"
           target="_blank"
           class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition">
            <i data-lucide="download" class="w-4 h-4"></i>
            Export Excel
        </a>
    </div>

    {{-- Rekap Per Karyawan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-black text-slate-700 text-sm uppercase tracking-wider">Rekap Per Karyawan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-blue-600 text-white text-xs font-bold uppercase tracking-wider">
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rekap as $i => $r)
                        @php
                            $persen = $totalHariKerja > 0 ? round(($r['hadir'] / $totalHariKerja) * 100, 1) : 0;
                            $merah  = $persen < 80;
                        @endphp
                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50' }} hover:bg-blue-50 transition">
                            <td class="px-4 py-3 text-slate-500">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-semibold {{ $merah ? 'text-red-600' : 'text-slate-800' }}">
                                {{ $r['nama'] }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $r['jabatan'] }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $r['mitra'] }}</td>
                            <td class="px-4 py-3 text-center font-bold text-emerald-600">{{ $r['hadir'] }}</td>
                            <td class="px-4 py-3 text-center font-bold text-amber-500">{{ $r['telat'] }}</td>
                            <td class="px-4 py-3 text-center font-bold text-red-500">{{ $r['alfa'] }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $r['izin'] }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $r['sakit'] }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $r['cuti'] }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $r['dinas'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                    {{ $merah ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $persen }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Detail Harian --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-black text-slate-700 text-sm uppercase tracking-wider">Detail Absensi Harian</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-700 text-white text-xs font-bold uppercase tracking-wider">
                        <th class="px-4 py-3 text-left">No.</th>
                        <th class="px-4 py-3 text-left">Nama Karyawan</th>
                        <th class="px-4 py-3 text-left">Mitra / Cabang</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-center">Jam Masuk</th>
                        <th class="px-4 py-3 text-center">Jam Pulang</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($absensiList as $i => $abs)
                        @php
                            $statusColor = match($abs->status) {
                                'hadir'      => $abs->is_telat ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700',
                                'telat'      => 'bg-amber-100 text-amber-700',
                                'alfa'       => 'bg-red-100 text-red-700',
                                'izin'       => 'bg-purple-100 text-purple-700',
                                'sakit'      => 'bg-blue-100 text-blue-700',
                                'cuti'       => 'bg-sky-100 text-sky-700',
                                'dinas_luar' => 'bg-indigo-100 text-indigo-700',
                                default      => 'bg-slate-100 text-slate-600',
                            };
                            $statusLabel = match($abs->status) {
                                'hadir'      => $abs->is_telat ? 'Telat' : 'Tepat Waktu',
                                'telat'      => 'Telat',
                                'alfa'       => 'Alfa',
                                'izin'       => 'Izin Pribadi',
                                'sakit'      => 'Sakit',
                                'cuti'       => 'Cuti',
                                'dinas_luar' => 'Dinas Luar Kota',
                                default      => ucfirst($abs->status),
                            };
                        @endphp
                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50' }} hover:bg-blue-50 transition">
                            <td class="px-4 py-3 text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">
                                {{ $abs->karyawan?->nama ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $abs->mitra?->nama_mitra ?? ($abs->karyawan?->isTetap() ? 'Kantor CBN' : '-') }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $abs->tanggal?->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono text-slate-700">
                                {{ $abs->waktu_masuk?->format('H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono text-slate-700">
                                {{ $abs->waktu_pulang?->format('H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @else
    {{-- Empty State --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-16 text-center">
        <i data-lucide="calendar-x" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
        <p class="font-bold text-slate-500">Tidak ada data absensi untuk filter yang dipilih.</p>
        <p class="text-sm text-slate-400 mt-1">Coba ubah filter periode atau mitra.</p>
    </div>
    @endif

</div>
@endsection