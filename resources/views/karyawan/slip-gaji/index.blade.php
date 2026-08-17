@extends('karyawan.sidebar')
@section('title','Slip Gaji')

@section('content')

<div class="mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight ">E-Slip Gaji</h1>
            <p class="text-gray-500 mt-1 text-sm">Riwayat Penghasilan Bulanan — <span class="text-red-600 font-bold">PT CBN</span></p>
        </div>
        <div class="bg-white px-5 py-3 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-3">
            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
            <span class="text-[10px] font-black text-[#1E3A5F] ">Update Terakhir: {{ now()->translatedFormat('F Y') }}</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    @forelse ($slipGaji as $slip)
        <a href="{{ route('karyawan.slip-gaji.show', $slip->id) }}"
           class="group relative bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8
                  hover:shadow-xl hover:shadow-[#1E3A5F]/5 hover:-translate-y-1 transition-all duration-500">
            
            {{-- Header Card --}}
            <div class="flex justify-between items-start mb-8">
                <div class="w-12 h-12 bg-gray-50 text-[#1E3A5F] rounded-2xl flex items-center justify-center group-hover:bg-red-600 group-hover:text-white transition-all duration-500 shadow-sm">
                    <i data-lucide="receipt-text" class="w-6 h-6"></i>
                </div>
                <div class="text-right">
                    <p class="text-[9px] font-black text-gray-400 mb-1">Periode</p>
                    <p class="text-sm font-black text-[#1E3A5F] group-hover:text-red-600 transition-colors">
                        {{ $slip->periodeGaji->nama_periode }}
                    </p>
                </div>
            </div>

            {{-- Info Ringkas --}}
            <div class="space-y-4 mb-8">
                <div class="flex justify-between items-center">
                    <span class="text-[10px] font-bold text-gray-400">Gaji Pokok</span>
                    <span class="text-xs font-black text-[#1E3A5F]">
                        Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[10px] font-bold text-gray-400">Potongan</span>
                    <span class="text-xs font-black text-rose-500">
                        -Rp {{ number_format($slip->total_potongan, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Footer Card: Total --}}
            <div class="pt-6 border-t border-gray-50 flex justify-between items-center">
                <div>
                    <p class="text-[9px] font-black text-gray-400">Gaji Bersih</p>
                    <p class="text-lg font-black text-[#1E3A5F]">
                        <span class="text-xs font-bold mr-0.5">Rp</span>{{ number_format($slip->gaji_bersih, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-gray-50 text-[#1E3A5F] rounded-xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-500">
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </div>
            </div>

            {{-- Accent Element --}}
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-12 h-1 bg-gray-100 group-hover:bg-red-600 group-hover:w-24 transition-all duration-500 rounded-full"></div>
        </a>
    @empty
        <div class="col-span-full bg-white rounded-[3rem] border border-gray-100 shadow-sm p-20 text-center relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-gray-50 rounded-full blur-3xl"></div>
            <div class="relative z-10">
                <div class="w-20 h-20 bg-gray-50 text-gray-200 rounded-[2rem] flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="file-x-2" class="w-10 h-10"></i>
                </div>
                <h4 class="text-xl font-black text-[#1E3A5F] tracking-tight">Belum Ada Data</h4>
                <p class="text-sm font-medium text-gray-400 mt-2">Slip gaji kamu akan muncul secara otomatis<br>setiap bulan setelah diproses admin.</p>
            </div>
        </div>
    @endforelse
</div>

@if ($slipGaji->hasPages())
    <div class="mt-12 flex justify-center">
        {{ $slipGaji->links() }}
    </div>
@endif

@endsection
