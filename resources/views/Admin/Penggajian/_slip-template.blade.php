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
                    ['NIP', $slipGaji->karyawan->nip],
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


            {{-- Pendapatan --}}
            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-3">
                Pendapatan
            </p>
            <div class="space-y-2.5 mb-5">
                @foreach ($slipGaji->details->where('tipe', 'pendapatan') as $detail)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $detail->nama_komponen }}</span>
                        <span class="font-semibold text-gray-800">
                            Rp {{ number_format($detail->nominal, 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach
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
                @foreach ($slipGaji->details->where('tipe', 'potongan') as $detail)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $detail->nama_komponen }}</span>
                        <span class="font-semibold text-red-500">
                            - Rp {{ number_format($detail->nominal, 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach

                {{-- Detail Kehadiran (Text Based) --}}
                <div class="bg-gray-50/50 rounded-xl p-3 mt-4 border border-gray-100">
                    <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-2 text-center">Rekap Kehadiran Bulan Ini</p>
                    <div class="flex justify-around text-[11px] font-bold text-gray-500">
                        <div class="text-center">
                            <p class="text-gray-400 text-[8px] uppercase">Hadir</p>
                            <p class="text-green-600">{{ $slipGaji->total_hadir }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-400 text-[8px] uppercase">Telat</p>
                            <p class="{{ $slipGaji->total_telat > 0 ? 'text-amber-600' : '' }}">{{ $slipGaji->total_telat }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-400 text-[8px] uppercase">Alfa</p>
                            <p class="{{ $slipGaji->total_alfa > 0 ? 'text-red-500' : '' }}">{{ $slipGaji->total_alfa }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-400 text-[8px] uppercase">Cuti</p>
                            <p class="{{ $slipGaji->total_cuti > 0 ? 'text-indigo-600' : '' }}">{{ $slipGaji->total_cuti }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-400 text-[8px] uppercase">Izin</p>
                            <p class="{{ $slipGaji->total_izin > 0 ? 'text-blue-600' : '' }}">{{ $slipGaji->total_izin }}</p>
                        </div>
                    </div>
                </div>
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
