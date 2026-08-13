@extends('karyawan.sidebar')

@section('title', 'Riwayat Absensi')

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('karyawan.absensi.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F]">Riwayat Absensi</h1>
        <p class="text-gray-500 mt-1 text-sm">
            {{ collect($daftarBulan)->firstWhere('value', $bulan)['label'] }} {{ $tahun }}
        </p>
    </div>
</header>

{{-- FILTER --}}
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('karyawan.absensi.riwayat') }}"
          class="flex flex-wrap gap-3 items-end">

        <div>
            <label class="block text-[9px] font-black text-gray-400 tracking-widest mb-1">BULAN</label>
            <select name="bulan"
                    class="border border-gray-200 rounded-xl px-3 py-2 text-sm font-semibold
                           text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#1E3A5F]">
                @foreach($daftarBulan as $b)
                    <option value="{{ $b['value'] }}"
                            {{ $b['value'] == $bulan ? 'selected' : '' }}>
                        {{ $b['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[9px] font-black text-gray-400 tracking-widest mb-1">TAHUN</label>
            <select name="tahun"
                    class="border border-gray-200 rounded-xl px-3 py-2 text-sm font-semibold
                           text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#1E3A5F]">
                @foreach($daftarTahun as $y)
                    <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit"
                class="flex items-center gap-2 bg-[#1E3A5F] text-white px-4 py-2 rounded-xl
                       text-xs font-black hover:bg-blue-900 transition-all">
            <i data-lucide="filter" class="w-3 h-3"></i> Filter
        </button>
    </form>
</div>

{{-- REKAP KARTU --}}
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
    @foreach([
        'hadir'      => ['Hadir',      'green'],
        'telat'      => ['Telat',      'yellow'],
        'alfa'       => ['Alfa',       'red'],
        'izin'       => ['Izin',       'blue'],
        'sakit'      => ['Sakit',      'purple'],
        'cuti'       => ['Cuti',       'indigo'],
        'dinas_luar' => ['Dinas Luar', 'orange'],
    ] as $key => [$label, $color])
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-black text-{{ $color }}-600">
                {{ $rekapBulan[$key] ?? 0 }}
            </p>
            <p class="text-[9px] font-black text-gray-400 tracking-widest mt-1">
                {{ strtoupper($label) }}
            </p>
        </div>
    @endforeach
</div>

{{-- INFO HARI KERJA --}}
<div class="bg-blue-50 rounded-2xl px-4 py-3 mb-5 flex items-center gap-3">
    <i data-lucide="calendar-days" class="w-4 h-4 text-blue-500 shrink-0"></i>
    <p class="text-xs font-semibold text-blue-700">
        Jumlah hari kerja bulan ini (Senin–Sabtu): <strong>{{ $hariKerja }} hari</strong>
    </p>
</div>

{{-- TABEL RIWAYAT --}}
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
    @if($riwayat->count())
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-[9px] font-black text-gray-400 tracking-widest
                               border-b border-gray-100">
                        <th class="pb-3 pr-4">TANGGAL</th>
                        <th class="pb-3 pr-4">HARI</th>
                        <th class="pb-3 pr-4">STATUS</th>
                        <th class="pb-3 pr-4">MASUK</th>
                        <th class="pb-3 pr-4">PULANG</th>
                        <th class="pb-3 pr-4">DURASI</th>
                        <th class="pb-3">MITRA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($riwayat as $row)
                        @php
                            $statusMap = [
                                'hadir'      => ['Hadir',      'bg-green-100 text-green-700'],
                                'telat'      => ['Telat',      'bg-yellow-100 text-yellow-700'],
                                'alfa'       => ['Alfa',       'bg-red-100 text-red-700'],
                                'izin'       => ['Izin',       'bg-blue-100 text-blue-700'],
                                'sakit'      => ['Sakit',      'bg-purple-100 text-purple-700'],
                                'cuti'       => ['Cuti',       'bg-indigo-100 text-indigo-700'],
                                'dinas_luar' => ['Dinas Luar', 'bg-orange-100 text-orange-700'],
                            ];
                            [$label, $cls] = $statusMap[$row->status] ?? ['?', 'bg-gray-100 text-gray-500'];
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 pr-4 font-semibold text-gray-600">
                                {{ $row->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="py-3 pr-4 text-gray-500">
                                {{ $row->tanggal->translatedFormat('l') }}
                            </td>
                            <td class="py-3 pr-4">
                                <span class="inline-block px-2 py-0.5 rounded-full
                                             text-[9px] font-black {{ $cls }}">
                                    {{ $label }}
                                </span>
                                @if($row->is_telat && $row->status !== 'telat')
                                    <span class="inline-block ml-1 px-2 py-0.5 rounded-full
                                                 text-[9px] font-black bg-yellow-100 text-yellow-700">
                                        Telat
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 pr-4 text-gray-600">
                                {{ $row->waktu_masuk?->format('H:i') ?? '-' }}
                            </td>
                            <td class="py-3 pr-4 text-gray-600">
                                {{ $row->waktu_pulang?->format('H:i') ?? '-' }}
                            </td>
                            <td class="py-3 pr-4 text-gray-600">
                                @if($row->durasiMenit())
                                    {{ intdiv($row->durasiMenit(), 60) }}j {{ $row->durasiMenit() % 60 }}m
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3 text-gray-500 text-[10px]">
                                {{ $row->mitra?->nama_mitra ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-12">
            <i data-lucide="calendar-x" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
            <p class="text-sm font-black text-gray-400">Tidak ada data absensi pada bulan ini.</p>
        </div>
    @endif
</div>

@endsection
