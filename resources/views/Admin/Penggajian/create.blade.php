@extends('admin.sidebar')
@section('title','Proses Gaji Baru')

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('admin.penggajian.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight uppercase">Proses Gaji Baru</h1>
        <p class="text-gray-500 mt-1 text-sm">Hitung dan terbitkan slip gaji semua karyawan</p>
    </div>
</header>

@if ($sudahAda)
    <div class="mb-6 p-5 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
        <div>
            <p class="text-sm font-black text-amber-700">
                Penggajian bulan ini sudah pernah diproses.
            </p>
            <p class="text-xs text-amber-600 mt-1">
                Kamu masih bisa memproses bulan lain dengan mengubah pilihan bulan di bawah.
            </p>
        </div>
    </div>
@endif

@if ($karyawanBelumAda > 0)
    <div class="mb-6 p-5 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 shrink-0 mt-0.5"></i>
        <div>
            <p class="text-sm font-black text-red-700">
                {{ $karyawanBelumAda }} karyawan belum memiliki gaji pokok.
            </p>
            <p class="text-xs text-red-500 mt-1">
                Karyawan tersebut tidak diikutkan dalam penggajian ini.
                <a href="{{ route('admin.komponen-gaji.index') }}"
                   class="font-black underline">Isi sekarang →</a>
            </p>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Form & Daftar Karyawan --}}
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F] uppercase italic text-[11px] mb-5
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-green-500 rounded-full"></span>
                Pilih Periode
            </h3>

            <form method="POST" action="{{ route('admin.penggajian.proses') }}">
            @csrf
                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-[11px] font-black text-gray-500
                                      uppercase tracking-widest mb-2">
                            Bulan <span class="text-red-500">*</span>
                        </label>
                        <select name="bulan"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200
                                       bg-gray-50 text-sm font-semibold text-gray-700
                                       outline-none focus:border-[#1E3A5F] focus:bg-white">
                            @foreach ([
                                1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
                                5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
                                9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
                            ] as $num => $nama)
                                <option value="{{ $num }}"
                                        {{ $bulanIni === $num ? 'selected' : '' }}>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-gray-500
                                      uppercase tracking-widest mb-2">
                            Tahun <span class="text-red-500">*</span>
                        </label>
                        <select name="tahun"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200
                                       bg-gray-50 text-sm font-semibold text-gray-700
                                       outline-none focus:border-[#1E3A5F] focus:bg-white">
                            @for ($y = now()->year; $y >= now()->year - 3; $y--)
                                <option value="{{ $y }}"
                                        {{ $tahunIni === $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100 mb-5">
                    <p class="text-[10px] font-black text-blue-700 uppercase mb-2">
                        Dihitung otomatis dari data absensi:
                    </p>
                    <ul class="space-y-1 text-[9px] text-blue-600 font-semibold">
                        <li>✓ Gaji pokok + uang makan + uang transport</li>
                        <li>✓ Potongan BPJS Kesehatan & Ketenagakerjaan</li>
                        <li>✓ Potongan telat (karyawan tetap CBN)</li>
                        <li>✓ Potongan cuti & alfa</li>
                        <li>✓ Rekap kehadiran bulan tersebut</li>
                    </ul>
                </div>

                <button type="submit"
                        onclick="return confirm('Proses penggajian? Tindakan ini tidak dapat dibatalkan.')"
                        class="w-full bg-[#1E3A5F] hover:bg-green-600 text-white font-black
                               text-sm uppercase tracking-widest py-4 rounded-2xl transition-all
                               shadow-sm active:scale-95 italic flex items-center justify-center gap-2">
                    <i data-lucide="calculator" class="w-5 h-5"></i>
                    Proses Penggajian Sekarang
                </button>
            </form>
        </div>

        {{-- Daftar karyawan --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 flex justify-between items-center">
                <h3 class="font-black text-[#1E3A5F] uppercase italic text-[11px]
                           flex items-center gap-2">
                    <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                    Karyawan Akan Diproses
                    <span class="font-normal text-gray-400 normal-case text-[9px] ml-1">
                        ({{ $karyawan->count() }} orang)
                    </span>
                </h3>
            </div>
            <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
                @forelse ($karyawan as $kar)
                    <div class="flex items-center justify-between px-6 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center
                                        font-black text-xs shrink-0
                                        {{ $kar->isTetap() ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700' }}">
                                {{ strtoupper(substr($kar->nama, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-[11px] font-black text-[#1E3A5F] uppercase">
                                    {{ $kar->nama }}
                                </p>
                                <p class="text-[9px] text-gray-400">
                                    {{ $kar->jabatan }} • {{ $kar->labelDivisi() }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-black text-gray-700">
                                Rp {{ number_format($kar->komponenGaji->gaji_pokok, 0, ',', '.') }}
                            </p>
                            @if ($kar->uang_makan_by_mitra)
                                <p class="text-[8px] text-amber-500 font-semibold">Makan by Mitra</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center">
                        <p class="text-sm text-gray-400 font-semibold">
                            Tidak ada karyawan dengan komponen gaji yang lengkap.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Info --}}
    <div class="space-y-4">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-3">
                Ringkasan
            </p>
            <div class="space-y-2.5">
                <div class="flex justify-between">
                    <span class="text-xs text-gray-500 font-semibold">Siap diproses</span>
                    <span class="text-xs font-black text-green-600">{{ $karyawan->count() }} orang</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-gray-500 font-semibold">Belum lengkap</span>
                    <span class="text-xs font-black {{ $karyawanBelumAda > 0 ? 'text-amber-600' : 'text-gray-300' }}">
                        {{ $karyawanBelumAda }} orang
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-amber-50 rounded-2xl border border-amber-100 p-4">
            <p class="text-[10px] font-black text-amber-700 uppercase mb-2">⚠️ Perhatian</p>
            <p class="text-[9px] text-amber-600 font-semibold leading-relaxed">
                Penggajian yang sudah diproses tidak bisa dibatalkan.
                Pastikan data absensi bulan ini sudah lengkap dan komponen gaji
                semua karyawan sudah benar sebelum memproses.
            </p>
        </div>

    </div>

</div>

@endsection