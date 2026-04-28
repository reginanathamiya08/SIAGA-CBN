@extends('karyawan.sidebar')

@section('title', 'Dashboard')

@section('content')
    <header class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
        <div>
            <h1 class="text-2xl font-black text-[#1E3A5F] ">
                Halo, {{ $karyawan->nama }}
            </h1>
            <p class="text-gray-500 mt-1 text-sm">
                {{ $karyawan->jabatan }} •
                <span class="font-bold {{ $karyawan->isTetap() ? 'text-blue-600' : 'text-red-600' }}  ">
                    {{ $karyawan->isTetap() ? 'Karyawan Tetap' : 'Karyawan Kontrak' }}
                </span>
            </p>
        </div>
        <span class="hidden md:block text-[11px] font-black bg-[#1E3A5F] text-white px-5 py-2.5 rounded-xl italic shadow-md   tracking-widest">
            {{ now()->translatedFormat('d M Y') }}
        </span>
    </header>

    {{-- Status Absensi Hari Ini --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-6">
        <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-4 flex items-center gap-2">
            <span class="w-1 h-4 bg-blue-500 rounded-full"></span> Absensi Hari Ini
        </h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-2xl p-4">
                <p class="text-[9px] font-black text-gray-400   tracking-widest mb-1">Masuk</p>
                <p class="text-xl font-black {{ $absensiHariIni?->waktu_masuk ? 'text-green-600' : 'text-gray-300' }}">
                    {{ $absensiHariIni?->waktu_masuk?->format('H:i') ?? '--:--' }}
                </p>
                @if ($absensiHariIni?->is_telat)
                    <span class="text-[9px] text-yellow-600 font-black  ">Telat</span>
                @endif
            </div>
            <div class="bg-gray-50 rounded-2xl p-4">
                <p class="text-[9px] font-black text-gray-400   tracking-widest mb-1">Pulang</p>
                <p class="text-xl font-black {{ $absensiHariIni?->waktu_pulang ? 'text-blue-600' : 'text-gray-300' }}">
                    {{ $absensiHariIni?->waktu_pulang?->format('H:i') ?? '--:--' }}
                </p>
            </div>
        </div>
        @if (!$absensiHariIni?->waktu_masuk)
            <a href="{{ route('karyawan.absensi.index') }}" class="mt-4 flex items-center justify-center gap-2 bg-[#1E3A5F] text-white rounded-2xl py-3 font-black   text-xs italic hover:bg-red-600 transition-all">
                <i data-lucide="fingerprint" class="w-4 h-4"></i>
                Absen Masuk Sekarang
            </a>
        @elseif (!$absensiHariIni?->waktu_pulang)
            <a href="{{ route('karyawan.absensi.index') }}" class="mt-4 flex items-center justify-center gap-2 bg-gray-700 text-white rounded-2xl py-3 font-black   text-xs italic hover:bg-red-600 transition-all">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                Absen Pulang
            </a>
        @else
            <div class="mt-4 text-center text-xs font-black text-green-600  ">
                Absensi hari ini selesai
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Rekap Bulan Ini --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-4 flex items-center gap-2">
                <span class="w-1 h-4 bg-teal-500 rounded-full"></span> Rekap Bulan Ini
            </h3>
            <div class="space-y-3">
                @foreach (['hadir' => ['Hadir', 'text-green-600'], 'telat' => ['Telat', 'text-yellow-600'], 'alfa' => ['Alfa', 'text-red-600'], 'izin' => ['Izin', 'text-blue-600'], 'cuti' => ['Cuti', 'text-purple-600']] as $key => [$label, $color])
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-black text-gray-500  ">{{ $label }}</span>
                        <span class="text-sm font-black {{ $color }}">{{ $rekapBulan[$key] ?? 0 }} hari</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Kuota Cuti --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-4 flex items-center gap-2">
                <span class="w-1 h-4 bg-purple-500 rounded-full"></span> Kuota Cuti {{ now()->year }}
            </h3>
            @if ($kuotaCuti)
                <div class="text-center mb-4">
                    <p class="text-4xl font-black text-[#1E3A5F]">{{ $kuotaCuti->sisa }}</p>
                    <p class="text-[10px] font-black text-gray-400  ">Hari Tersisa</p>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-[#1E3A5F] h-2 rounded-full transition-all"
                         style="width: {{ ($kuotaCuti->sisa / $kuotaCuti->kuota_total) * 100 }}%"></div>
                </div>
                <div class="flex justify-between mt-2">
                    <span class="text-[9px] text-gray-400 font-bold">Terpakai: {{ $kuotaCuti->terpakai }}</span>
                    <span class="text-[9px] text-gray-400 font-bold">Total: {{ $kuotaCuti->kuota_total }}</span>
                </div>
            @else
                <p class="text-xs text-gray-400 text-center py-4">Data kuota cuti belum tersedia.</p>
            @endif
        </div>

        {{-- Slip Gaji Terbaru --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-4 flex items-center gap-2">
                <span class="w-1 h-4 bg-green-500 rounded-full"></span> Slip Gaji Terakhir
            </h3>
            @if ($slipTerbaru)
                <div>
                    <p class="text-[10px] font-black text-gray-400  ">{{ $slipTerbaru->periode->nama_periode }}</p>
                    <p class="text-2xl font-black text-green-700 mt-1">
                        Rp {{ number_format($slipTerbaru->gaji_bersih, 0, ',', '.') }}
                    </p>
                    <a href="#" class="mt-4 flex items-center gap-2 text-[10px] font-black text-[#1E3A5F]   hover:text-red-600 transition-colors">
                        <i data-lucide="download" class="w-3 h-3"></i> Download PDF
                    </a>
                </div>
            @else
                <p class="text-xs text-gray-400 text-center py-4">Belum ada slip gaji.</p>
            @endif
        </div>
    </div>
@endsection