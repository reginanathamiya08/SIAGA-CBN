@extends('admin.sidebar')

@section('title', 'Dashboard')

@section('content')
    {{-- Header --}}
    <header class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
        <div>
            <h1 class="text-2xl font-black text-[#1E3A5F]">Dashboard Operasional</h1>
            <p class="text-gray-500 mt-1 text-sm">Monitoring SDM <span class="text-red-600 font-bold">PT Citra Bangun Nagari</span></p>
        </div>
        <span class="hidden md:block text-[11px] font-black bg-[#1E3A5F] text-white px-5 py-2.5 rounded-xl shadow-md uppercase">
            {{ now()->translatedFormat('d M Y') }}
        </span>
    </header>

    {{-- KARTU STATISTIK --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-50 flex items-center gap-4 group hover:shadow-md hover:-translate-y-1 transition-all duration-300">
            <div class="p-3 bg-blue-600 text-white rounded-xl shadow-lg shadow-blue-100 group-hover:scale-110 transition-transform">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 mb-1 uppercase">Karyawan Tetap</p>
                <p class="text-xl font-black text-[#1E3A5F]">{{ $stats['karyawan_tetap'] }} <span class="text-xs font-normal">Orang</span></p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-50 flex items-center gap-4 group hover:shadow-md hover:-translate-y-1 transition-all duration-300">
            <div class="p-3 bg-red-600 text-white rounded-xl shadow-lg shadow-red-100 group-hover:scale-110 transition-transform">
                <i data-lucide="user-plus" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 mb-1 uppercase">Karyawan Kontrak</p>
                <p class="text-xl font-black text-[#1E3A5F]">{{ $stats['karyawan_kontrak'] }} <span class="text-xs font-normal"> Orang</span></p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-50 flex items-center gap-4 group hover:shadow-md hover:-translate-y-1 transition-all duration-300">
            <div class="p-3 bg-[#1E3A5F] text-white rounded-xl shadow-lg shadow-blue-900/10 group-hover:scale-110 transition-transform">
                <i data-lucide="building-2" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 mb-1 uppercase">Mitra Aktif</p>
                <p class="text-xl font-black text-[#1E3A5F]">{{ $stats['mitra_aktif'] }} <span class="text-xs font-normal">Unit</span></p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-50 flex items-center gap-4 group hover:shadow-md hover:-translate-y-1 transition-all duration-300">
            <div class="p-3 bg-green-600 text-white rounded-xl shadow-lg shadow-green-100 group-hover:scale-110 transition-transform">
                <i data-lucide="user-check" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 mb-1 uppercase">Pool Tersedia</p>
                <p class="text-xl font-black text-green-700">{{ $stats['karyawan_tersedia'] }} <span class="text-xs font-normal text-gray-400"> Orang</span></p>
            </div>
        </div>
    </div>

    {{-- BARIS TENGAH: Donut Kehadiran + Tabel Absensi --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- Donut Kehadiran --}}
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-50 flex flex-col items-center">
            <h3 class="font-black text-[#1E3A5F] text-[11px] mb-6 self-start flex items-center gap-2 uppercase">
                <span class="w-1 h-4 bg-red-600 rounded-full"></span> Kehadiran Hari Ini
            </h3>

            @php
                $circumference = 2 * M_PI * 62;
                $offset = $circumference * (1 - $persenHadir / 100);
            @endphp

            <div class="relative w-36 h-36 flex items-center justify-center mb-6">
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="72" cy="72" r="62" stroke="#F8FAFC" stroke-width="12" fill="transparent"/>
                    <circle cx="72" cy="72" r="62" stroke="#1E3A5F" stroke-width="12" fill="transparent"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $offset }}"
                            stroke-linecap="round"/>
                </svg>
                <div class="absolute flex flex-col items-center">
                    <span class="text-2xl font-black text-[#1E3A5F]">{{ $persenHadir }}%</span>
                    <span class="text-[9px] font-bold text-gray-400 uppercase">Hadir</span>
                </div>
            </div>

            <div class="flex gap-6 w-full justify-center">
                <div class="text-center">
                    <p class="text-[9px] font-black text-gray-400 uppercase">Hadir</p>
                    <p class="text-sm font-black text-blue-600">{{ $hadirHariIni }}</p>
                </div>
                <div class="text-center border-l border-gray-100 pl-6">
                    <p class="text-[9px] font-black text-gray-400 uppercase">Telat</p>
                    <p class="text-sm font-black text-red-600">{{ $telatHariIni }}</p>
                </div>
                <div class="text-center border-l border-gray-100 pl-6">
                    <p class="text-[9px] font-black text-gray-400 uppercase">Total</p>
                    <p class="text-sm font-black text-gray-600">{{ $totalKaryawan }}</p>
                </div>
            </div>
        </div>

        {{-- Tabel Absensi Terbaru --}}
        <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-50 overflow-hidden"
             x-data="{ filter: 'Semua', open: false }">
            <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-black text-[#1E3A5F] text-[12px] uppercase">Absensi Terbaru</h3>
                <div class="relative">
                    <button @click="open = !open" @click.away="open = false"
                            class="flex items-center gap-2 bg-white border border-gray-200 px-4 py-2 rounded-xl shadow-sm hover:border-[#1E3A5F] transition-all">
                        <i data-lucide="filter" class="w-3.5 h-3.5 text-[#1E3A5F]"></i>
                        <span class="text-[10px] font-black text-[#1E3A5F]" x-text="'Filter: ' + filter"></span>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-transition
                         class="absolute right-0 mt-2 w-40 bg-white border border-gray-100 rounded-xl shadow-xl z-50 overflow-hidden">
                        <button @click="filter = 'Semua'; open = false"   class="w-full text-left px-4 py-2.5 text-[10px] font-black hover:bg-gray-50 border-b border-gray-50">Semua</button>
                        <button @click="filter = 'Tetap'; open = false"   class="w-full text-left px-4 py-2.5 text-[10px] font-black hover:bg-blue-50 text-blue-700 border-b border-gray-50">Karyawan Tetap</button>
                        <button @click="filter = 'Kontrak'; open = false" class="w-full text-left px-4 py-2.5 text-[10px] font-black hover:bg-red-50 text-red-700">Karyawan Kontrak</button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[#1E3A5F] text-[10px] font-black border-b border-gray-50 uppercase">
                            <th class="px-6 py-4">Karyawan</th>
                            <th class="px-6 py-4">Jenis</th>
                            <th class="px-6 py-4 text-center">Waktu Masuk</th>
                            <th class="px-6 py-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm font-bold">
                        @forelse ($absensiTerbaru as $absen)
                            @php
                                $jenis = $absen->karyawan->isTetap() ? 'tetap' : 'kontrak';
                                $filterVal = $jenis === 'tetap' ? 'Tetap' : 'Kontrak';
                            @endphp
                            <tr x-show="filter === 'Semua' || filter === '{{ $filterVal }}'"
                                class="border-b last:border-0 hover:bg-gray-50 transition-all">
                                <td class="px-6 py-3">
                                    <span class="text-[#1E3A5F] block font-black">{{ $absen->karyawan->nama }}</span>
                                    <span class="text-[9px] text-gray-400 font-bold uppercase tracking-tight">{{ $absen->karyawan->nik }}</span>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="text-[9px] font-black {{ $jenis === 'tetap' ? 'text-blue-600' : 'text-red-600' }} uppercase">
                                        {{ $jenis }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-center text-xs font-black {{ $absen->is_telat ? 'text-red-500' : 'text-gray-500' }}">
                                    {{ $absen->waktu_masuk ? $absen->waktu_masuk->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    @php
                                        $badge = match($absen->status) {
                                            'hadir'  => 'bg-emerald-50 text-emerald-600',
                                            'telat'  => 'bg-amber-50 text-amber-600',
                                            'alfa'   => 'bg-red-50 text-red-600',
                                            default  => 'bg-gray-50 text-gray-500',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-[9px] font-black {{ $badge }} uppercase">
                                        {{ $absen->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-xs text-gray-400 font-semibold">
                                    Belum ada data absensi hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- BARIS BAWAH: Pengajuan + Pool Karyawan --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Pengajuan Menunggu --}}
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-black text-[#1E3A5F] text-[11px] flex items-center gap-2 uppercase">
                    <span class="w-1 h-4 bg-red-500 rounded-full"></span> Persetujuan Baru
                </h3>
                <span class="text-[9px] font-black text-gray-400 uppercase">
                    {{ $pengajuanMenunggu->count() }} Menunggu
                </span>
            </div>

            <div class="space-y-3">
                @forelse ($pengajuanMenunggu as $item)
                    <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-2xl border border-gray-100 hover:border-red-200 hover:bg-red-50/30 transition-all group cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white border border-gray-200 text-[#1E3A5F] rounded-xl flex items-center justify-center font-black text-xs shadow-sm group-hover:bg-red-500 group-hover:text-white transition-all">
                                {{ strtoupper(substr($item['karyawan']->nama, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-xs font-black text-[#1E3A5F]">{{ $item['karyawan']->nama }}</p>
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tight mt-0.5">
                                    {{ $item['label'] }} • {{ $item['karyawan']->divisi ?? '-' }}
                                </p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-red-500 group-hover:translate-x-1 transition-all"></i>
                    </div>
                @empty
                    <div class="text-center py-10">
                        <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3 text-gray-200">
                             <i data-lucide="check-circle" class="w-6 h-6"></i>
                        </div>
                        <p class="text-[10px] text-gray-400 font-black uppercase">Tidak ada pengajuan yang menunggu</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pool Karyawan --}}
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="flex items-center justify-between mb-6 relative z-10">
                <h3 class="font-black text-[#1E3A5F] text-[11px] flex items-center gap-2 uppercase">
                    <span class="w-1 h-4 bg-blue-500 rounded-full"></span> Karyawan Siap Tugas
                </h3>
                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[9px] font-black uppercase">
                    {{ $stats['karyawan_tersedia'] }} Tersedia
                </span>
            </div>

            <div class="space-y-3 relative z-10">
                @forelse ($poolKaryawan as $kar)
                    <div class="flex justify-between items-center p-4 bg-white border border-gray-100 rounded-2xl hover:shadow-md hover:border-blue-100 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i data-lucide="user-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="text-[#1E3A5F] text-xs font-black block">{{ $kar->nama }}</span>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[9px] text-gray-400 font-bold uppercase tracking-tight">{{ $kar->jabatan ?? '-' }}</span>
                                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                    <span class="text-[9px] text-emerald-600 font-black uppercase">Ready</span>
                                </div>
                            </div>
                        </div>
                        <button class="bg-[#1E3A5F] hover:bg-blue-900 text-white px-4 py-2 rounded-xl font-black text-[9px] transition-all active:scale-95 shadow-lg shadow-blue-900/10">
                            PLOTTING
                        </button>
                    </div>
                @empty
                    <div class="text-center py-10">
                        <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3 text-gray-200">
                             <i data-lucide="users" class="w-6 h-6"></i>
                        </div>
                        <p class="text-[10px] text-gray-400 font-black uppercase">Tidak ada karyawan dalam pool</p>
                    </div>
                @endforelse
            </div>
            <i data-lucide="briefcase" class="absolute -right-2 -bottom-2 w-20 h-20 text-gray-50 -rotate-12"></i>
        </div>
    </div>

@endsection
