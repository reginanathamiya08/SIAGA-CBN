@extends('admin.sidebar')
@section('title', 'Kelola Mitra')

@section('content')

<header class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] ">Kelola Mitra</h1>
        <p class="text-gray-500 mt-1 text-sm">Manajemen perusahaan mitra <span class="text-red-600 font-bold">PT Citra Bangun Nagari</span></p>
    </div>
    <a href="{{ route('admin.mitra.create') }}"
       class="flex items-center gap-2 bg-[#1E3A5F] hover:bg-red-600 text-white
              font-black text-xs   italic px-5 py-3 rounded-xl transition-all shadow-sm">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Tambah Mitra
    </a>
</header>

{{-- Statistik --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
        <p class="text-[9px] font-black text-gray-400   tracking-widest mb-1">Mitra Induk</p>
        <p class="text-2xl font-black text-[#1E3A5F]">{{ $totalMitra }}</p>
    </div>
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
        <p class="text-[9px] font-black text-gray-400   tracking-widest mb-1">Cabang</p>
        <p class="text-2xl font-black text-blue-600">{{ $totalCabang }}</p>
    </div>
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
        <p class="text-[9px] font-black text-gray-400   tracking-widest mb-1">Karyawan Aktif</p>
        <p class="text-2xl font-black text-green-600">{{ $totalAktif }}</p>
    </div>
</div>

{{-- ── KANTOR PUSAT ────────────────────────────────────────── --}}
@if($kantorPusat)
    <div class="mb-8">
        <h3 class="font-black text-[#1E3A5F] italic text-[11px] mb-4 flex items-center gap-2">
            <span class="w-1 h-4 bg-blue-600 rounded-full"></span>
            KANTOR PUSAT (PT CBN)
        </h3>
        <div class="bg-blue-50/30 rounded-3xl border-2 border-blue-100 shadow-sm overflow-hidden border-dashed">
            <div class="flex items-center justify-between px-6 py-5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-600 text-white rounded-2xl flex items-center
                                justify-center font-black text-lg shrink-0 shadow-lg shadow-blue-200">
                        <i data-lucide="building-2" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-base font-black text-[#1E3A5F] flex items-center gap-2">
                            {{ $kantorPusat->nama_mitra }}
                            <span class="px-2 py-0.5 bg-blue-600 text-white text-[8px] rounded-full uppercase tracking-widest italic">Pusat</span>
                        </p>
                        <div class="flex items-center gap-3 mt-1">
                            <span class="text-[10px] text-blue-500 font-black italic">
                                {{ substr($kantorPusat->jam_masuk, 0, 5) }} - {{ substr($kantorPusat->jam_pulang, 0, 5) }}
                            </span>
                            <span class="text-[9px] text-gray-300">|</span>
                            <span class="text-[10px] text-gray-500 font-mono">
                                {{ $kantorPusat->latitude }}, {{ $kantorPusat->longitude }}
                            </span>
                            <span class="text-[9px] text-gray-300">|</span>
                            <span class="text-[10px] text-green-600 font-bold">
                                {{ $kantorPusat->penempatan_count }} karyawan tetap
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.mitra.show', $kantorPusat->id) }}"
                       class="p-2.5 rounded-xl bg-blue-100 text-blue-700 hover:bg-blue-200 transition-all">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('admin.mitra.edit', $kantorPusat->id) }}"
                       class="p-2.5 rounded-xl bg-amber-100 text-amber-700 hover:bg-amber-200 transition-all">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ── DAFTAR MITRA KERJA ──────────────────────────────────── --}}
<h3 class="font-black text-[#1E3A5F] italic text-[11px] mb-4 flex items-center gap-2">
    <span class="w-1 h-4 bg-orange-500 rounded-full"></span>
    DAFTAR MITRA KERJA & CABANG
</h3>

@forelse ($mitraInduk as $mitra)
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm mb-4 overflow-hidden">

        {{-- Header Mitra Induk --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50 bg-gray-50/50">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-[#1E3A5F] text-white rounded-xl flex items-center
                            justify-center font-black text-sm shrink-0">
                    {{ strtoupper(substr($mitra->nama_mitra, 0, 2)) }}
                </div>
                <div>
                    <p class="text-sm font-black text-[#1E3A5F]  ">{{ $mitra->nama_mitra }}</p>
                    <div class="flex items-center gap-3 mt-0.5">
                        <span class="text-[9px] text-gray-400 font-mono">
                            {{ $mitra->latitude }}, {{ $mitra->longitude }}
                        </span>
                        <span class="text-[9px] text-gray-400">|</span>
                        <span class="text-[9px] text-blue-600 font-semibold">
                            Radius: {{ $mitra->radius_meter }}m
                        </span>
                        <span class="text-[9px] text-gray-400">|</span>
                        <span class="text-[9px] text-green-600 font-semibold">
                            {{ $mitra->penempatan_count }} karyawan aktif
                        </span>
                        <span class="text-[9px] text-gray-400">|</span>
                        <span class="text-[9px] text-orange-600 font-black italic">
                            {{ substr($mitra->jam_masuk, 0, 5) }} - {{ substr($mitra->jam_pulang, 0, 5) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-lg
                             text-[9px] font-black  ">
                    {{ $mitra->cabang_count }} Cabang
                </span>
                <a href="{{ route('admin.mitra.show', $mitra->id) }}"
                   class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all"
                   title="Detail">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                </a>
                <a href="{{ route('admin.mitra.edit', $mitra->id) }}"
                   class="p-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-all"
                   title="Edit">
                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                </a>
                <form method="POST" action="{{ route('admin.mitra.destroy', $mitra->id) }}"
                      onsubmit="return confirm('Hapus mitra {{ addslashes($mitra->nama_mitra) }}?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-all"
                            title="Hapus">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- Cabang Mitra --}}
        @if ($mitra->cabang->count() > 0)
            <div class="divide-y divide-gray-50">
                @foreach ($mitra->cabang as $cabang)
                    <div class="flex items-center justify-between px-6 py-3 hover:bg-gray-50 transition-all">
                        <div class="flex items-center gap-3 pl-6">
                            <div class="w-1 h-6 bg-gray-200 rounded-full shrink-0"></div>
                            <div>
                                <p class="text-[11px] font-black text-gray-700  ">
                                    {{ $cabang->nama_mitra }}
                                </p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[9px] text-gray-400 font-mono">
                                        {{ $cabang->latitude }}, {{ $cabang->longitude }}
                                    </span>
                                    <span class="text-[9px] text-blue-500 font-semibold">
                                        Radius: {{ $cabang->radius_meter }}m
                                    </span>
                                    <span class="text-[9px] text-gray-400">|</span>
                                    <span class="text-[9px] text-orange-500 font-black italic">
                                        {{ substr($cabang->jam_masuk, 0, 5) }} - {{ substr($cabang->jam_pulang, 0, 5) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.mitra.show', $cabang->id) }}"
                               class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all">
                                <i data-lucide="eye" class="w-3 h-3"></i>
                            </a>
                            <a href="{{ route('admin.mitra.edit', $cabang->id) }}"
                               class="p-1.5 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-all">
                                <i data-lucide="pencil" class="w-3 h-3"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.mitra.destroy', $cabang->id) }}"
                                  onsubmit="return confirm('Hapus cabang {{ addslashes($cabang->nama_mitra) }}?')">
                                @csrf @method('DELETE')
                                <button class="p-1.5 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-all">
                                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-6 py-3 pl-14 text-[10px] text-gray-400 italic">
                Belum ada cabang untuk mitra ini.
                <a href="{{ route('admin.mitra.create') }}?induk={{ $mitra->id }}"
                   class="text-[#1E3A5F] font-black hover:text-red-500 transition-colors ml-1">
                    + Tambah Cabang
                </a>
            </div>
        @endif

    </div>
@empty
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-16 text-center">
        <i data-lucide="building" class="w-12 h-12 text-gray-200 mx-auto mb-3"></i>
        <p class="text-sm text-gray-400 font-semibold">Belum ada data mitra.</p>
        <a href="{{ route('admin.mitra.create') }}"
           class="mt-3 inline-block text-xs font-black text-[#1E3A5F]
                  hover:text-red-600   italic transition-colors">
            + Tambah Mitra Pertama
        </a>
    </div>
@endforelse

@endsection