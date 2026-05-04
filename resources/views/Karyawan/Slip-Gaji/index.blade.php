@extends('karyawan.sidebar')
@section('title','Slip Gaji')

@section('content')

<header class="mb-6 pb-4 border-b border-gray-100">
    <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight ">Slip Gaji</h1>
    <p class="text-gray-500 mt-1 text-sm">Riwayat slip gaji bulanan kamu</p>
</header>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse ($slipGaji as $slip)
        <a href="{{ route('karyawan.slip-gaji.show', $slip->id) }}"
           class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5
                  hover:border-blue-200 hover:shadow-md transition-all group">
            <div class="flex justify-between items-center mb-4">
                <p class="text-base font-black text-[#1E3A5F] ">
                    {{ $slip->periodeGaji->nama_periode }}
                </p>
                <i data-lucide="file-text"
                   class="w-5 h-5 text-gray-300 group-hover:text-blue-500 transition-colors"></i>
            </div>
            <div class="space-y-1.5 mb-4">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Gaji Pokok</span>
                    <span class="font-semibold text-gray-700">
                        Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Total Potongan</span>
                    <span class="font-semibold text-red-500">
                        - Rp {{ number_format($slip->total_potongan, 0, ',', '.') }}
                    </span>
                </div>
            </div>
            <div class="flex justify-between items-center p-3 bg-[#1E3A5F]/5 rounded-xl">
                <span class="text-[10px] font-black text-[#1E3A5F] ">
                    Gaji Bersih
                </span>
                <span class="text-sm font-black text-[#1E3A5F]">
                    Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}
                </span>
            </div>
        </a>
    @empty
        <div class="col-span-3 bg-white rounded-3xl border border-gray-100 shadow-sm
                    p-16 text-center">
            <i data-lucide="file-x" class="w-12 h-12 text-gray-200 mx-auto mb-3"></i>
            <p class="text-sm text-gray-400 font-semibold">Belum ada slip gaji tersedia.</p>
            <p class="text-xs text-gray-300 mt-1">
                Slip gaji akan muncul setelah admin memproses penggajian.
            </p>
        </div>
    @endforelse
</div>

@if ($slipGaji->hasPages())
    <div class="mt-6">{{ $slipGaji->links() }}</div>
@endif

@endsection