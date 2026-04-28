@extends('karyawan.sidebar')
@section('title', 'Detail Perizinan')

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('karyawan.perizinan.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] ">Detail Perizinan</h1>
        <p class="text-gray-500 mt-1 text-sm">{{ $perizinan->labelJenis() }}</p>
    </div>
</header>

<div class="max-w-2xl">
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Status Banner --}}
        @php
            $bannerClass = match($perizinan->status_approval) {
                'disetujui' => 'bg-green-500',
                'ditolak'   => 'bg-red-500',
                default     => 'bg-amber-500',
            };
            $bannerLabel = match($perizinan->status_approval) {
                'disetujui' => '✅ Pengajuan Disetujui',
                'ditolak'   => '❌ Pengajuan Ditolak',
                default     => '⏳ Menunggu Persetujuan Pimpinan',
            };
        @endphp
        <div class="{{ $bannerClass }} px-6 py-4 text-white">
            <p class="font-black text-sm ">{{ $bannerLabel }}</p>
            @if ($perizinan->approved_at)
                <p class="text-[10px] text-white/70 mt-0.5">
                    {{ $perizinan->approved_at->translatedFormat('d F Y, H:i') }} WIB
                </p>
            @endif
        </div>

        <div class="p-6 space-y-4">

            @foreach ([
                ['Jenis Izin',       $perizinan->labelJenis()],
                ['Tanggal Mulai',    $perizinan->tanggal_mulai->translatedFormat('d F Y')],
                ['Tanggal Selesai',  $perizinan->tanggal_selesai->translatedFormat('d F Y')],
                ['Jumlah Hari',      $perizinan->jumlah_hari . ' hari'],
                ['Keterangan',       $perizinan->keterangan ?? '-'],
                ['Diajukan Pada',    $perizinan->created_at->translatedFormat('d F Y, H:i')],
            ] as [$label, $value])
                <div class="flex justify-between items-start border-b border-gray-50 pb-3 last:border-0 last:pb-0">
                    <span class="text-[10px] font-black text-gray-400  tracking-widest w-36 shrink-0">
                        {{ $label }}
                    </span>
                    <span class="text-sm font-semibold text-gray-700 text-right">{{ $value }}</span>
                </div>
            @endforeach

            {{-- File Bukti --}}
            @if ($perizinan->file_bukti)
                <div class="flex justify-between items-center border-b border-gray-50 pb-3">
                    <span class="text-[10px] font-black text-gray-400  tracking-widest w-36 shrink-0">
                        Surat Dokter
                    </span>
                    <a href="{{ Storage::url($perizinan->file_bukti) }}" target="_blank"
                       class="flex items-center gap-2 text-blue-600 font-semibold text-sm hover:text-blue-800">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        Lihat File
                    </a>
                </div>
            @endif

            {{-- Alasan Tolak --}}
            @if ($perizinan->status_approval === 'ditolak' && $perizinan->alasan_tolak)
                <div class="p-4 bg-red-50 rounded-2xl border border-red-100">
                    <p class="text-[10px] font-black text-red-700  mb-1">Alasan Penolakan</p>
                    <p class="text-sm font-semibold text-red-600">{{ $perizinan->alasan_tolak }}</p>
                </div>
            @endif

            {{-- Batalkan jika masih menunggu --}}
            @if ($perizinan->status_approval === 'menunggu')
                <form method="POST"
                      action="{{ route('karyawan.perizinan.destroy', $perizinan->id) }}"
                      onsubmit="return confirm('Batalkan pengajuan ini?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full mt-2 bg-red-50 hover:bg-red-100 text-red-600 font-black
                                   text-xs  italic py-3 rounded-2xl transition-all
                                   flex items-center justify-center gap-2">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                        Batalkan Pengajuan
                    </button>
                </form>
            @endif

        </div>
    </div>
</div>

@endsection