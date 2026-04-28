{{--
    Template slip gaji yang dipakai bersama oleh:
    - admin/penggajian/slip.blade.php
    - karyawan/slip-gaji/show.blade.php

    Variable yang dibutuhkan: $slipGaji (dengan relasi karyawan.user, karyawan.komponenGaji, periodeGaji)
--}}

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="bg-[#1E3A5F] px-8 py-6 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xl font-black uppercase tracking-wider">PT Citra Bangun Nagari</p>
                    <p class="text-[11px] text-white/60 mt-0.5">Sistem Informasi Absensi & Penggajian</p>
                </div>
                <div class="text-right">
                    <p class="text-[9px] text-white/50 uppercase tracking-widest mb-1">Slip Gaji</p>
                    <p class="text-base font-black">{{ $slipGaji->periodeGaji->nama_periode }}</p>
                    <p class="text-[9px] text-white/50 mt-0.5">
                        {{ $slipGaji->periodeGaji->tanggal_mulai->format('d M') }} –
                        {{ $slipGaji->periodeGaji->tanggal_selesai->format('d M Y') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="px-8 py-6">

            {{-- Info Karyawan --}}
            <div class="grid grid-cols-2 gap-4 pb-5 border-b border-gray-100 mb-5">
                @foreach ([
                    ['Nama',        $slipGaji->karyawan->nama],
                    ['ID Karyawan', $slipGaji->karyawan->user->username],
                    ['Jabatan',     $slipGaji->karyawan->jabatan],
                    ['Divisi',      $slipGaji->karyawan->labelDivisi()],
                ] as [$label, $value])
                    <div>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-0.5">
                            {{ $label }}
                        </p>
                        <p class="text-sm font-semibold text-gray-700">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Rekap Kehadiran --}}
            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-3">
                Rekap Kehadiran
            </p>
            <div class="grid grid-cols-3 gap-3 mb-6">
                @foreach ([
                    ['Hadir',    $slipGaji->total_hadir, 'text-green-600'],
                    ['Telat',    $slipGaji->total_telat, $slipGaji->total_telat > 0 ? 'text-amber-600' : 'text-gray-300'],
                    ['Alfa',     $slipGaji->total_alfa,  $slipGaji->total_alfa  > 0 ? 'text-red-500'   : 'text-gray-300'],
                    ['Cuti',     $slipGaji->total_cuti,  $slipGaji->total_cuti  > 0 ? 'text-indigo-600': 'text-gray-300'],
                    ['Izin/Sakit',$slipGaji->total_izin, $slipGaji->total_izin  > 0 ? 'text-blue-600'  : 'text-gray-300'],
                ] as [$label, $val, $color])
                    <div class="text-center p-3 bg-gray-50 rounded-2xl border border-gray-100">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">
                            {{ $label }}
                        </p>
                        <p class="text-xl font-black {{ $color }}">{{ $val }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Pendapatan --}}
            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-3">
                Pendapatan
            </p>
            <div class="space-y-2.5 mb-5">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Gaji Pokok</span>
                    <span class="font-semibold text-gray-800">
                        Rp {{ number_format($slipGaji->gaji_pokok, 0, ',', '.') }}
                    </span>
                </div>
                @if ($slipGaji->uang_makan > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Uang Makan</span>
                        <span class="font-semibold text-gray-800">
                            Rp {{ number_format($slipGaji->uang_makan, 0, ',', '.') }}
                        </span>
                    </div>
                @endif
                @if ($slipGaji->uang_transport > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Uang Transport</span>
                        <span class="font-semibold text-gray-800">
                            Rp {{ number_format($slipGaji->uang_transport, 0, ',', '.') }}
                        </span>
                    </div>
                @endif
                @if ($slipGaji->karyawan->uang_makan_by_mitra)
                    <p class="text-[9px] text-amber-600 font-semibold italic">
                        * Uang makan & transport dibayar langsung oleh mitra
                    </p>
                @endif
                <div class="flex justify-between text-sm font-black border-t border-gray-100 pt-2">
                    <span class="text-gray-700">Total Pendapatan</span>
                    <span class="text-green-600">
                        Rp {{ number_format($slipGaji->totalPendapatan(), 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Potongan --}}
            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-3">
                Potongan
            </p>
            <div class="space-y-2.5 mb-5">
                @php $kg = $slipGaji->karyawan->komponenGaji @endphp
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">
                        BPJS Kesehatan ({{ $kg->persen_bpjs_kes ?? 9.24 }}%)
                    </span>
                    <span class="font-semibold text-red-500">
                        - Rp {{ number_format($slipGaji->potongan_bpjs_kes, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">
                        BPJS Ketenagakerjaan ({{ $kg->persen_bpjs_tk ?? 5 }}%)
                    </span>
                    <span class="font-semibold text-red-500">
                        - Rp {{ number_format($slipGaji->potongan_bpjs_tk, 0, ',', '.') }}
                    </span>
                </div>
                @if ($slipGaji->potongan_telat > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">
                            Potongan Telat ({{ $slipGaji->total_telat }} hari)
                        </span>
                        <span class="font-semibold text-red-500">
                            - Rp {{ number_format($slipGaji->potongan_telat, 0, ',', '.') }}
                        </span>
                    </div>
                @endif
                @if ($slipGaji->potongan_izin > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">
                            Potongan Cuti/Alfa ({{ $slipGaji->total_cuti + $slipGaji->total_alfa }} hari)
                        </span>
                        <span class="font-semibold text-red-500">
                            - Rp {{ number_format($slipGaji->potongan_izin, 0, ',', '.') }}
                        </span>
                    </div>
                @endif
                <div class="flex justify-between text-sm font-black border-t border-gray-100 pt-2">
                    <span class="text-gray-700">Total Potongan</span>
                    <span class="text-red-500">
                        - Rp {{ number_format($slipGaji->total_potongan, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Gaji Bersih --}}
            <div class="flex justify-between items-center bg-[#1E3A5F] rounded-2xl px-6 py-4">
                <span class="text-white font-black uppercase tracking-widest text-sm">Gaji Bersih</span>
                <span class="text-white font-black text-xl">
                    Rp {{ number_format($slipGaji->gaji_bersih, 0, ',', '.') }}
                </span>
            </div>

            {{-- Footer --}}
            <div class="mt-5 pt-4 border-t border-gray-100 text-center">
                <p class="text-[9px] text-gray-400">
                    Slip gaji diterbitkan otomatis oleh sistem pada
                    {{ $slipGaji->diterbitkan_at?->translatedFormat('d F Y, H:i') ?? now()->translatedFormat('d F Y') }} WIB.
                </p>
            </div>

        </div>
    </div>
</div>