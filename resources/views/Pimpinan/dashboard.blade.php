{{-- resources/views/pimpinan/dashboard.blade.php --}}
@extends('pimpinan.sidebar')

@section('title', 'Dashboard Pimpinan')

@section('content')
    <header class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
        <div>
            <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight uppercase">Dashboard Pimpinan</h1>
            <p class="text-gray-500 mt-1 text-sm">Monitoring & Persetujuan <span class="text-red-600 font-bold uppercase">PT CBN</span></p>
        </div>
        <span class="hidden md:block text-[11px] font-black bg-[#1E3A5F] text-white px-5 py-2.5 rounded-xl italic shadow-md uppercase tracking-widest">
            {{ now()->translatedFormat('d M Y') }}
        </span>
    </header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-50 flex items-center gap-4">
            <div class="p-3 bg-blue-600 text-white rounded-xl"><i data-lucide="users" class="w-6 h-6"></i></div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Karyawan</p>
                <p class="text-xl font-black text-[#1E3A5F]">{{ $totalKaryawan }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-50 flex items-center gap-4">
            <div class="p-3 bg-green-600 text-white rounded-xl"><i data-lucide="user-check" class="w-6 h-6"></i></div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Hadir Hari Ini</p>
                <p class="text-xl font-black text-green-700">{{ $hadirHariIni }} <span class="text-xs text-gray-400">({{ $persenHadir }}%)</span></p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-50 flex items-center gap-4">
            <div class="p-3 bg-yellow-500 text-white rounded-xl"><i data-lucide="clock" class="w-6 h-6"></i></div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pengajuan Masuk</p>
                <p class="text-xl font-black text-yellow-600">{{ $totalMenunggu }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-50 flex items-center gap-4">
            <div class="p-3 bg-[#1E3A5F] text-white rounded-xl"><i data-lucide="wallet" class="w-6 h-6"></i></div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Periode Aktif</p>
                <p class="text-sm font-black text-[#1E3A5F]">{{ $periodeAktif?->nama_periode ?? 'Belum ada' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 text-center py-16">
        <i data-lucide="construction" class="w-12 h-12 text-gray-200 mx-auto mb-4"></i>
        <p class="text-gray-400 font-black uppercase text-sm">Modul monitoring & approval akan dikerjakan di tahap berikutnya.</p>
    </div>
@endsection