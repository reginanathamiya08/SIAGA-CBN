@extends('pimpinan.sidebar')

@section('title', 'Monitoring Gaji')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#1E3A5F] ">Monitoring Gaji</h1>
            <p class="text-gray-500 text-sm">Pantau pengeluaran gaji karyawan <span class="text-red-600 font-bold ">PT CBN</span></p>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="{{ route('pimpinan.monitoring-gaji.export', request()->all()) }}" 
               class="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black  tracking-widest transition-all shadow-lg shadow-emerald-200">
                <i data-lucide="download" class="w-4 h-4"></i>
                Ekspor Excel
            </a>
        </div>
    </header>

    <!-- Statistik Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5">
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center shadow-inner">
                <i data-lucide="wallet" class="w-7 h-7"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400  tracking-widest mb-1">Total Pengeluaran</p>
                <p class="text-xl font-black text-[#1E3A5F]">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5">
            <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center shadow-inner">
                <i data-lucide="users" class="w-7 h-7"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400  tracking-widest mb-1">Total Karyawan</p>
                <p class="text-xl font-black text-[#1E3A5F]">{{ $totalKaryawan }} Orang</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5">
            <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center shadow-inner">
                <i data-lucide="trending-up" class="w-7 h-7"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400  tracking-widest mb-1">Rata-rata Gaji</p>
                <p class="text-xl font-black text-[#1E3A5F]">Rp {{ number_format($rataRataGaji, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
        <form action="{{ route('pimpinan.monitoring-gaji.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-[10px] font-black text-gray-400  tracking-widest mb-2">Periode Gaji</label>
                <select name="periode_id" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-blue-500 transition-all">
                    <option value="">Semua Periode</option>
                    @foreach($semuaPeriode as $p)
                        <option value="{{ $p->id }}" {{ $periodeId == $p->id ? 'selected' : '' }}>
                            {{ $p->nama_periode }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-black text-gray-400  tracking-widest mb-2">Mitra / Cabang</label>
                <select name="mitra_id" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-blue-500 transition-all">
                    <option value="">Semua Mitra</option>
                    @foreach($semuaMitra as $m)
                        <option value="{{ $m->id }}" {{ $mitraId == $m->id ? 'selected' : '' }}>
                            {{ $m->nama_mitra }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-[#1E3A5F] hover:bg-[#2a4d7a] text-white font-black py-3 rounded-xl text-xs  tracking-widest transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-100">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400  tracking-widest">Karyawan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400  tracking-widest">Jabatan & Mitra</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400  tracking-widest">Gaji Pokok</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400  tracking-widest text-center">Tunjangan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400  tracking-widest text-center">Potongan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400  tracking-widest text-right">Gaji Bersih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($slipGaji as $slip)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 text-blue-700 rounded-xl flex items-center justify-center font-black text-xs shadow-sm">
                                    {{ strtoupper(substr($slip->karyawan?->nama ?? '?', 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-black text-[#1E3A5F] group-hover:text-blue-600 transition-colors">
                                        {{ $slip->karyawan?->nama ?? 'Karyawan Tidak Ditemukan' }}
                                    </p>
                                    <p class="text-[10px] font-bold text-gray-400  tracking-tighter">
                                        {{ $slip->karyawan?->user?->username ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <p class="text-xs font-bold text-[#1E3A5F]">{{ $slip->karyawan?->jabatan ?? '-' }}</p>
                            <p class="text-[10px] text-gray-500 font-medium italic">
                                {{ $slip->karyawan?->penempatanAktif?->mitra?->nama_mitra ?? ($slip->karyawan?->isTetap() ? 'Kantor CBN' : '-') }}
                            </p>
                        </td>
                        <td class="px-6 py-5">
                            <p class="text-xs font-bold text-gray-700">Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}</p>
                        </td>
                        <td class="px-6 py-5 text-center">
                            @php
                                $totalTunjangan = $slip->total_tunjangan + $slip->uang_makan + $slip->uang_transport + $slip->total_lembur;
                            @endphp
                            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-black">
                                + Rp {{ number_format($totalTunjangan, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span class="px-3 py-1 bg-red-50 text-red-700 rounded-full text-[10px] font-black">
                                - Rp {{ number_format($slip->total_potongan, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <p class="text-sm font-black text-[#1E3A5F]">
                                Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}
                            </p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 bg-gray-50 text-gray-200 rounded-3xl flex items-center justify-center">
                                    <i data-lucide="search-x" class="w-8 h-8"></i>
                                </div>
                                <p class="text-gray-400 font-black  text-xs tracking-widest">Tidak ada data gaji ditemukan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
