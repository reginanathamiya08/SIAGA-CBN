@extends('karyawan.sidebar')
@section('title', 'Detail Dinas Luar Kota')

@section('content')

<header class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-4">
        <a href="{{ route('karyawan.dinas-luar.index') }}"
           class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-[#1E3A5F]">Detail Dinas Luar Kota</h1>
            <p class="text-gray-500 mt-1 text-sm">Penugasan Perjalanan Dinas Resmi</p>
        </div>
    </div>
</header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Kolom Utama --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Detail Informasi --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center font-black">
                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-[#1E3A5F]">Dinas Luar Kota</h2>
                        <p class="text-xs text-gray-400 font-semibold">Diajukan pada {{ $perizinan->created_at->format('d M Y, H:i') }} WIB</p>
                    </div>
                </div>
                @php
                    $badgeStyle = match($perizinan->status_approval) {
                        'disetujui' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'ditolak'   => 'bg-red-50 text-red-700 border-red-200',
                        default     => 'bg-amber-50 text-amber-700 border-amber-200',
                    };
                    $statusLabel = match($perizinan->status_approval) {
                        'disetujui' => 'Disetujui Pimpinan',
                        'ditolak'   => 'Ditolak',
                        default     => 'Menunggu Persetujuan Pimpinan',
                    };
                @endphp
                <span class="px-3.5 py-1.5 rounded-full text-xs font-black border {{ $badgeStyle }}">
                    {{ $statusLabel }}
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Tanggal Berangkat</p>
                    <p class="text-sm font-black text-[#1E3A5F] mt-1">{{ \Carbon\Carbon::parse($perizinan->tanggal_mulai)->format('d M Y') }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Tanggal Kembali</p>
                    <p class="text-sm font-black text-[#1E3A5F] mt-1">{{ \Carbon\Carbon::parse($perizinan->tanggal_selesai)->format('d M Y') }}</p>
                </div>
                <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100 col-span-2 sm:col-span-1">
                    <p class="text-[10px] font-black text-emerald-700 uppercase tracking-wider">Total Durasi</p>
                    <p class="text-sm font-black text-emerald-800 mt-1">{{ $perizinan->jumlah_hari }} Hari Tugas</p>
                </div>
            </div>

            {{-- Maksud Penugasan --}}
            <div>
                <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Maksud & Description Penugasan</h4>
                <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 text-sm text-gray-700 font-semibold leading-relaxed">
                    {{ $perizinan->keterangan ?? 'Tidak ada rincian keterangan.' }}
                </div>
            </div>

            {{-- Surat Tugas --}}
            <div>
                <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Dokumen Surat Tugas</h4>
                @if ($perizinan->file_bukti)
                    <div class="flex items-center justify-between p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                        <div class="flex items-center gap-3">
                            <i data-lucide="file-text" class="w-6 h-6 text-emerald-600"></i>
                            <div>
                                <p class="text-xs font-black text-emerald-900">Surat Tugas Resmi</p>
                                <p class="text-[10px] text-emerald-600 font-medium">Klik tombol di kanan untuk membuka dokumen</p>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $perizinan->file_bukti) }}" target="_blank"
                           class="flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition-all shadow-sm">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                            <span>Buka Berkas</span>
                        </a>
                    </div>
                @else
                    <p class="text-xs text-gray-400 italic">Tidak ada berkas terlampir.</p>
                @endif
            </div>
        </div>

    </div>

    {{-- Sidebar Status Approval --}}
    <div class="space-y-5">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F] italic text-xs uppercase tracking-wider mb-4">Status Approval</h3>

            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 font-bold text-xs
                                {{ $perizinan->status_approval === 'disetujui' ? 'bg-green-100 text-green-600' : ($perizinan->status_approval === 'ditolak' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600') }}">
                        @if($perizinan->status_approval === 'disetujui')
                            <i data-lucide="check" class="w-4 h-4"></i>
                        @elseif($perizinan->status_approval === 'ditolak')
                            <i data-lucide="x" class="w-4 h-4"></i>
                        @else
                            <i data-lucide="clock" class="w-4 h-4"></i>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-black text-[#1E3A5F]">Persetujuan Pimpinan</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">
                            @if($perizinan->status_approval === 'disetujui')
                                Disetujui oleh {{ $perizinan->approver?->nama ?? 'Pimpinan' }}
                            @elseif($perizinan->status_approval === 'ditolak')
                                Ditolak oleh {{ $perizinan->approver?->nama ?? 'Pimpinan' }}
                            @else
                                Menunggu konfirmasi Pimpinan
                            @endif
                        </p>
                    </div>
                </div>

                @if($perizinan->status_approval === 'ditolak' && $perizinan->alasan_tolak)
                    <div class="p-4 bg-red-50 rounded-2xl border border-red-100">
                        <p class="text-[10px] font-black text-red-700 uppercase tracking-wider">Alasan Penolakan:</p>
                        <p class="text-xs text-red-600 font-semibold mt-1">{{ $perizinan->alasan_tolak }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection
