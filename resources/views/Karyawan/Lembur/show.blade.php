@extends('karyawan.sidebar')
@section('title', 'Detail Lembur')

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('karyawan.lembur.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight uppercase">
            Detail Lembur
        </h1>
        <p class="text-gray-500 mt-1 text-sm">
            {{ $lembur->tanggal->translatedFormat('l, d F Y') }}
        </p>
    </div>
</header>

<div class="max-w-2xl">
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Status Banner --}}
        @php
            $bannerClass = match($lembur->status_approval) {
                'disetujui' => 'bg-green-500',
                'ditolak'   => 'bg-red-500',
                default     => 'bg-amber-500',
            };
            $bannerLabel = match($lembur->status_approval) {
                'disetujui' => '✅ Lembur Disetujui',
                'ditolak'   => '❌ Lembur Ditolak',
                default     => '⏳ Menunggu Persetujuan Pimpinan',
            };
        @endphp
        <div class="{{ $bannerClass }} px-6 py-4 text-white">
            <p class="font-black text-sm uppercase">{{ $bannerLabel }}</p>
            @if ($lembur->approved_at)
                <p class="text-[10px] text-white/70 mt-0.5">
                    {{ $lembur->approved_at->translatedFormat('d F Y, H:i') }} WIB
                </p>
            @endif
        </div>

        <div class="p-6 space-y-4">

            @foreach ([
                ['Tanggal',      $lembur->tanggal->translatedFormat('l, d F Y')],
                ['Jam Mulai',    $lembur->jam_mulai],
                ['Jam Selesai',  $lembur->jam_selesai],
                ['Durasi',       $lembur->formatDurasi()],
                ['Keperluan',    $lembur->keterangan],
                ['Diajukan',     $lembur->created_at->translatedFormat('d F Y, H:i')],
            ] as [$label, $value])
                <div class="flex justify-between items-start border-b border-gray-50
                            pb-3 last:border-0 last:pb-0">
                    <span class="text-[10px] font-black text-gray-400 uppercase
                                 tracking-widest w-32 shrink-0">
                        {{ $label }}
                    </span>
                    <span class="text-sm font-semibold text-gray-700 text-right">
                        {{ $value }}
                    </span>
                </div>
            @endforeach

            {{-- Alasan Tolak --}}
            @if ($lembur->status_approval === 'ditolak' && $lembur->alasan_tolak)
                <div class="p-4 bg-red-50 rounded-2xl border border-red-100">
                    <p class="text-[10px] font-black text-red-700 uppercase mb-1">
                        Alasan Penolakan
                    </p>
                    <p class="text-sm font-semibold text-red-600">{{ $lembur->alasan_tolak }}</p>
                </div>
            @endif

            {{-- Tombol Batalkan --}}
            @if ($lembur->status_approval === 'menunggu')
                <form method="POST"
                      action="{{ route('karyawan.lembur.destroy', $lembur->id) }}"
                      onsubmit="return confirm('Batalkan pengajuan lembur ini?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full mt-2 bg-red-50 hover:bg-red-100 text-red-600
                                   font-black text-xs uppercase italic py-3 rounded-2xl
                                   transition-all flex items-center justify-center gap-2">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                        Batalkan Pengajuan
                    </button>
                </form>
            @endif

        </div>
    </div>
</div>

@endsection