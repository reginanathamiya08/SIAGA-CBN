@extends('pimpinan.sidebar')

@section('title', 'Monitoring Gaji')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#1E3A5F]">Monitoring Gaji</h1>
            <p class="text-gray-500 text-sm">Pantau pengeluaran gaji karyawan <span class="text-red-600 font-bold">PT CBN</span></p>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="{{ route('pimpinan.monitoring-gaji.export', request()->all()) }}" 
               class="flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black transition-all shadow-lg shadow-emerald-100 hover:-translate-y-0.5 active:scale-95">
                <i data-lucide="download" class="w-4 h-4"></i>
                Ekspor Excel
            </a>
        </div>
    </header>

    @if($periodeId)
        @php
            $selectedPeriode = $semuaPeriode->firstWhere('id', $periodeId);
        @endphp
            @if($selectedPeriode && $selectedPeriode->isProses())
                <div class="p-6 bg-amber-50 border border-amber-200 rounded-3xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-amber-100 text-amber-800 rounded-2xl flex items-center justify-center shrink-0">
                            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-amber-900">Persetujuan Penggajian Diperlukan</h4>
                            <p class="text-xs text-amber-700 font-semibold mt-0.5">
                                Status periode <strong>{{ $selectedPeriode->nama_periode }}</strong> saat ini sedang diproses. Tentukan keputusan persetujuan per karyawan di bawah, kemudian kirimkan keputusan Anda.
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0 w-full md:w-auto">
                        <button type="submit" form="form-persetujuan" class="w-full px-6 py-3.5 bg-[#1E3A5F] hover:bg-blue-900 text-white rounded-xl text-xs font-black transition-all shadow-lg shadow-blue-100 uppercase tracking-wider">
                            Kirim Keputusan Penggajian
                        </button>
                    </div>
                </div>
            @elseif($selectedPeriode && $selectedPeriode->isFinal())
                <div class="p-5 bg-green-50 border border-green-200 rounded-3xl flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-green-100 text-green-800 rounded-2xl flex items-center justify-center shrink-0">
                        <i data-lucide="check-circle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-green-900">Penggajian Disetujui</h4>
                        <p class="text-xs text-green-700 font-semibold mt-0.5">
                            Periode <strong>{{ $selectedPeriode->nama_periode }}</strong> telah disetujui dan diselesaikan pada {{ $selectedPeriode->finalisasi_at ? $selectedPeriode->finalisasi_at->translatedFormat('d F Y, H:i') : '-' }} oleh {{ $selectedPeriode->finalizer?->nama ?? 'Pimpinan' }}.
                        </p>
                    </div>
                </div>
            @endif

    <!-- Statistik Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 hover:-translate-y-1 transition-all duration-300 group">
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i data-lucide="wallet" class="w-7 h-7"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 mb-1 uppercase">Total Pengeluaran</p>
                <p class="text-xl font-black text-[#1E3A5F]">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 hover:-translate-y-1 transition-all duration-300 group">
            <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i data-lucide="users" class="w-7 h-7"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 mb-1 uppercase">Total Karyawan</p>
                <p class="text-xl font-black text-[#1E3A5F]">{{ $totalKaryawan }} Orang</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 hover:-translate-y-1 transition-all duration-300 group">
            <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i data-lucide="trending-up" class="w-7 h-7"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 mb-1 uppercase">Rata-rata Gaji</p>
                <p class="text-xl font-black text-[#1E3A5F]">Rp {{ number_format($rataRataGaji, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Tab Card Gaji (dengan Filter Terintegrasi) -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Header: Tab Buttons + Filter dalam satu baris -->
        <form id="form-filter" action="{{ route('pimpinan.monitoring-gaji.index') }}" method="GET">
            <input type="hidden" name="periode_id" id="filter-periode-id" value="{{ $periodeId }}">

            <!-- Baris 1: Pilihan Bulan/Periode (Button per Bulan) -->
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/30">
                <p class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-wider mb-2.5">Pilih Periode Penggajian</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="selectPeriode('')"
                            class="px-4 py-2.5 rounded-xl text-xs font-black transition-all duration-200 {{ !$periodeId ? 'bg-[#1E3A5F] text-white shadow-md' : 'bg-gray-100 text-[#1E3A5F] hover:bg-gray-200' }}">
                        Semua Periode
                    </button>
                    @foreach($semuaPeriode as $p)
                        <button type="button" onclick="selectPeriode('{{ $p->id }}')"
                                class="px-4 py-2.5 rounded-xl text-xs font-black transition-all duration-200 flex items-center gap-2 relative {{ $periodeId == $p->id ? 'bg-[#1E3A5F] text-white shadow-md' : 'bg-gray-100 text-[#1E3A5F] hover:bg-gray-200' }}">
                            {{ $p->nama_periode }}
                            @if($p->status === 'proses')
                                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse" title="Menunggu Persetujuan Pimpinan"></span>
                            @elseif($p->status === 'final')
                                <span class="w-2 h-2 rounded-full bg-emerald-500" title="Selesai / Diterbitkan"></span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-gray-300" title="Draft"></span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Baris 2: Tab Buttons + Filter Mitra -->
            <div class="px-6 pt-4 pb-3 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center gap-3">
                <button id="btn-tab-gaji-tetap" type="button" onclick="switchTabGaji('tetap')"
                    class="tab-btn-gaji active-tab-gaji flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black transition-all">
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                    Karyawan Tetap
                    <span class="ml-1 px-2 py-0.5 rounded-full text-[9px] font-black bg-teal-100 text-teal-700">
                        {{ $slipTetap->count() }}
                    </span>
                </button>
                <button id="btn-tab-gaji-kontrak" type="button" onclick="switchTabGaji('kontrak')"
                    class="tab-btn-gaji flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black transition-all">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    Karyawan Kontrak
                    <span class="ml-1 px-2 py-0.5 rounded-full text-[9px] font-black bg-amber-100 text-amber-700">
                        {{ $slipKontrak->count() }}
                    </span>
                </button>

                <!-- Filter di kanan tab buttons -->
                <div class="ml-auto flex flex-wrap items-center gap-2">
                    <!-- Mitra: hanya tampil di tab Kontrak -->
                    <select id="filter-mitra-gaji" name="mitra_id"
                        class="bg-gray-100 border-none rounded-xl px-3 py-2 text-xs font-bold text-[#1E3A5F] focus:ring-2 focus:ring-blue-500 transition-all hidden">
                        <option value="">Semua Mitra</option>
                        @foreach($semuaMitra as $m)
                            <option value="{{ $m->id }}" {{ $mitraId == $m->id ? 'selected' : '' }}>
                                @if($m->is_cabang)
                                    ↳ {{ $m->nama_mitra }} (Cabang)
                                @else
                                    {{ $m->nama_mitra }} (Mitra Utama)
                                @endif
                            </option>
                        @endforeach
                    </select>

                    <button type="submit"
                        class="bg-[#1E3A5F] hover:bg-blue-900 text-white text-xs font-black px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 shadow-md shadow-blue-100 active:scale-95">
                        <i data-lucide="filter" class="w-3 h-3"></i>
                        Filter
                    </button>

                    @if($mitraId || $periodeId)
                    <a href="{{ route('pimpinan.monitoring-gaji.index') }}"
                        class="text-xs font-bold text-gray-400 hover:text-gray-600 px-3 py-2 rounded-xl hover:bg-gray-100 transition-all">
                        Reset
                    </a>
                    @endif
                </div>
            </div>
        </form>

        <form id="form-persetujuan" action="{{ route('pimpinan.monitoring-gaji.submit', $periodeId) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyimpan dan mengirimkan keputusan persetujuan penggajian ini?')">
            @csrf

        <!-- Panel: Karyawan Tetap -->
        <div id="panel-gaji-tetap" class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/30 border-b border-gray-100 uppercase">
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] tracking-tight">Karyawan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] tracking-tight">Jabatan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] tracking-tight">Gaji Pokok</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] text-center">Tunjangan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] text-center">Potongan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] text-right">Gaji Bersih</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] text-center w-[220px]">Persetujuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($slipTetap as $slip)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 text-blue-700 rounded-xl flex items-center justify-center font-black text-xs shadow-sm">
                                    {{ strtoupper(substr($slip->karyawan?->nama ?? '?', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-black text-[#1E3A5F] group-hover:text-blue-600 transition-colors">
                                            {{ $slip->karyawan?->nama ?? 'Karyawan Tidak Ditemukan' }}
                                        </p>
                                        @if($slip->status === 'direvisi')
                                            <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 text-[8px] font-black rounded-md border border-emerald-200 uppercase tracking-wider shrink-0">Sudah Direvisi</span>
                                        @elseif($slip->status === 'ditolak')
                                            <span class="px-1.5 py-0.5 bg-red-100 text-red-800 text-[8px] font-black rounded-md border border-red-200 uppercase tracking-wider shrink-0">Ditolak</span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] font-bold text-gray-400 tracking-tighter mt-0.5">{{ $slip->karyawan?->nip ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <p class="text-xs font-bold text-[#1E3A5F]">{{ $slip->karyawan?->jabatan ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-5">
                            <p class="text-xs font-bold text-gray-700">Rp {{ number_format($slip->getNominal('Gaji Pokok'), 0, ',', '.') }}</p>
                        </td>
                        <td class="px-6 py-5 text-center">
                            @php $totalTunjangan = $slip->totalPendapatan() - $slip->getNominal('Gaji Pokok'); @endphp
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
                            <p class="text-sm font-black text-[#1E3A5F]">Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}</p>
                        </td>
                        <td class="px-6 py-5">
                            @if($selectedPeriode && $selectedPeriode->isProses())
                                <div class="flex flex-col gap-2" x-data="{ keputusan: 'setuju', alasan: '', isSaved: false }">
                                    <div class="flex items-center justify-center gap-3">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" name="slips[{{ $slip->id }}][status]" value="setuju" checked 
                                                   @change="keputusan = 'setuju'; isSaved = false; alasan = ''"
                                                   class="text-emerald-600 focus:ring-emerald-500 border-gray-300 w-4 h-4">
                                            <span class="text-xs font-black text-emerald-600">Setuju</span>
                                        </label>
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" name="slips[{{ $slip->id }}][status]" value="tolak" 
                                                   @change="keputusan = 'tolak'"
                                                   class="text-red-600 focus:ring-red-500 border-gray-300 w-4 h-4">
                                            <span class="text-xs font-black text-red-600">Tolak</span>
                                        </label>
                                    </div>
                                    
                                    <!-- Hidden input to submit the reason value -->
                                    <input type="hidden" name="slips[{{ $slip->id }}][alasan]" :value="alasan">

                                    <!-- Form input alasan -->
                                    <div x-show="keputusan === 'tolak' && !isSaved" x-transition class="mt-1 flex flex-col gap-1.5">
                                        <textarea x-model="alasan" placeholder="Tulis alasan penolakan..." rows="2"
                                                  class="w-full p-2.5 border border-red-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all resize-none"></textarea>
                                        <button type="button" @click="if(alasan.trim() !== '') isSaved = true"
                                                class="self-end px-3 py-1 bg-[#1E3A5F] hover:bg-blue-900 text-white rounded-lg text-[10px] font-black transition-all">
                                            OK
                                        </button>
                                    </div>

                                    <!-- Tampilan setelah di-OK -->
                                    <div x-show="keputusan === 'tolak' && isSaved" x-transition class="mt-1 text-left p-2.5 bg-red-50 border border-red-100 rounded-xl max-w-[200px]">
                                        <p class="text-[10px] text-red-700 font-bold italic leading-relaxed">Catatan: "<span x-text="alasan"></span>"</p>
                                        <button type="button" @click="isSaved = false" class="text-[9px] text-blue-600 underline font-bold mt-1 block hover:text-blue-800">
                                            Ubah Catatan
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="text-center">
                                    @if($slip->isDiterbitkan() || $slip->status === 'disetujui')
                                        <span class="px-2.5 py-1 bg-green-50 text-green-700 rounded-lg text-[10px] font-black uppercase">Disetujui</span>
                                    @elseif($slip->status === 'ditolak')
                                        <span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-lg text-[10px] font-black uppercase">Ditolak</span>
                                        @if($slip->alasan_tolak)
                                            <p class="text-[9px] text-red-500 mt-1 italic font-semibold">{{ $slip->alasan_tolak }}</p>
                                        @endif
                                    @else
                                        <span class="px-2.5 py-1 bg-gray-50 text-gray-500 rounded-lg text-[10px] font-black uppercase">Menunggu</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-14 text-center text-gray-400 font-bold text-xs tracking-wider">Tidak ada data gaji karyawan tetap.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Panel: Karyawan Kontrak -->
        <div id="panel-gaji-kontrak" class="overflow-x-auto hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/30 border-b border-gray-100 uppercase">
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] tracking-tight">Karyawan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] tracking-tight">Jabatan &amp; Mitra</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] tracking-tight">Gaji Pokok</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] text-center">Tunjangan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] text-center">Potongan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] text-right">Gaji Bersih</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] text-center w-[220px]">Persetujuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($slipKontrak as $slip)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 text-blue-700 rounded-xl flex items-center justify-center font-black text-xs shadow-sm">
                                    {{ strtoupper(substr($slip->karyawan?->nama ?? '?', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-black text-[#1E3A5F] group-hover:text-blue-600 transition-colors">
                                            {{ $slip->karyawan?->nama ?? 'Karyawan Tidak Ditemukan' }}
                                        </p>
                                        @if($slip->status === 'direvisi')
                                            <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 text-[8px] font-black rounded-md border border-emerald-200 uppercase tracking-wider shrink-0">Sudah Direvisi</span>
                                        @elseif($slip->status === 'ditolak')
                                            <span class="px-1.5 py-0.5 bg-red-100 text-red-800 text-[8px] font-black rounded-md border border-red-200 uppercase tracking-wider shrink-0">Ditolak</span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] font-bold text-gray-400 tracking-tighter mt-0.5">{{ $slip->karyawan?->nip ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <p class="text-xs font-bold text-[#1E3A5F]">{{ $slip->karyawan?->jabatan ?? '-' }}</p>
                            <p class="text-[10px] text-gray-400 font-bold uppercase mt-0.5">
                                {{ $slip->karyawan?->penempatanAktif?->mitra?->nama_mitra ?? '-' }}
                            </p>
                        </td>
                        <td class="px-6 py-5">
                            <p class="text-xs font-bold text-gray-700">Rp {{ number_format($slip->getNominal('Gaji Pokok'), 0, ',', '.') }}</p>
                        </td>
                        <td class="px-6 py-5 text-center">
                            @php $totalTunjangan = $slip->totalPendapatan() - $slip->getNominal('Gaji Pokok'); @endphp
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
                            <p class="text-sm font-black text-[#1E3A5F]">Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}</p>
                        </td>
                        <td class="px-6 py-5">
                            @if($selectedPeriode && $selectedPeriode->isProses())
                                <div class="flex flex-col gap-2" x-data="{ keputusan: 'setuju', alasan: '', isSaved: false }">
                                    <div class="flex items-center justify-center gap-3">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" name="slips[{{ $slip->id }}][status]" value="setuju" checked 
                                                   @change="keputusan = 'setuju'; isSaved = false; alasan = ''"
                                                   class="text-emerald-600 focus:ring-emerald-500 border-gray-300 w-4 h-4">
                                            <span class="text-xs font-black text-emerald-600">Setuju</span>
                                        </label>
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" name="slips[{{ $slip->id }}][status]" value="tolak" 
                                                   @change="keputusan = 'tolak'"
                                                   class="text-red-600 focus:ring-red-500 border-gray-300 w-4 h-4">
                                            <span class="text-xs font-black text-red-600">Tolak</span>
                                        </label>
                                    </div>
                                    
                                    <!-- Hidden input to submit the reason value -->
                                    <input type="hidden" name="slips[{{ $slip->id }}][alasan]" :value="alasan">

                                    <!-- Form input alasan -->
                                    <div x-show="keputusan === 'tolak' && !isSaved" x-transition class="mt-1 flex flex-col gap-1.5">
                                        <textarea x-model="alasan" placeholder="Tulis alasan penolakan..." rows="2"
                                                  class="w-full p-2.5 border border-red-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all resize-none"></textarea>
                                        <button type="button" @click="if(alasan.trim() !== '') isSaved = true"
                                                class="self-end px-3 py-1 bg-[#1E3A5F] hover:bg-blue-900 text-white rounded-lg text-[10px] font-black transition-all">
                                            OK
                                        </button>
                                    </div>

                                    <!-- Tampilan setelah di-OK -->
                                    <div x-show="keputusan === 'tolak' && isSaved" x-transition class="mt-1 text-left p-2.5 bg-red-50 border border-red-100 rounded-xl max-w-[200px]">
                                        <p class="text-[10px] text-red-700 font-bold italic leading-relaxed">Catatan: "<span x-text="alasan"></span>"</p>
                                        <button type="button" @click="isSaved = false" class="text-[9px] text-blue-600 underline font-bold mt-1 block hover:text-blue-800">
                                            Ubah Catatan
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="text-center">
                                    @if($slip->isDiterbitkan() || $slip->status === 'disetujui')
                                        <span class="px-2.5 py-1 bg-green-50 text-green-700 rounded-lg text-[10px] font-black uppercase">Disetujui</span>
                                    @elseif($slip->status === 'ditolak')
                                        <span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-lg text-[10px] font-black uppercase">Ditolak</span>
                                        @if($slip->alasan_tolak)
                                            <p class="text-[9px] text-red-500 mt-1 italic font-semibold">{{ $slip->alasan_tolak }}</p>
                                        @endif
                                    @else
                                        <span class="px-2.5 py-1 bg-gray-50 text-gray-500 rounded-lg text-[10px] font-black uppercase">Menunggu</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-14 text-center text-gray-400 font-bold text-xs tracking-wider">Tidak ada data gaji karyawan kontrak.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>{{-- /Tab Card Gaji --}}
    </form>
    @else
        <div class="mt-8 bg-white rounded-[2rem] p-12 text-center border border-gray-100 shadow-sm">
            <div class="w-16 h-16 bg-blue-50 text-[#1E3A5F] rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="info" class="w-8 h-8"></i>
            </div>
            <h4 class="text-base font-black text-[#1E3A5F]">Pilih Periode Penggajian</h4>
            <p class="text-xs text-gray-500 font-semibold mt-1 max-w-md mx-auto leading-relaxed">
                Silakan pilih salah satu periode bulan di atas untuk melihat rekapitulasi pengeluaran gaji dan melakukan keputusan persetujuan.
            </p>
        </div>
    @endif
</div>

<style>
.tab-btn-gaji { color: #6b7280; background: transparent; }
.tab-btn-gaji.active-tab-gaji { background: #1E3A5F; color: #fff; }
</style>

@push('scripts')
<script>
    function selectPeriode(id) {
        document.getElementById('filter-periode-id').value = id;
        document.getElementById('form-filter').submit();
    }
    function switchTabGaji(tab) {
        const panels = { tetap: 'panel-gaji-tetap', kontrak: 'panel-gaji-kontrak' };
        const btns   = { tetap: 'btn-tab-gaji-tetap', kontrak: 'btn-tab-gaji-kontrak' };
        Object.keys(panels).forEach(key => {
            document.getElementById(panels[key]).classList.toggle('hidden', key !== tab);
            document.getElementById(btns[key]).classList.toggle('active-tab-gaji', key === tab);
        });
        // Tampilkan filter Mitra hanya di tab Kontrak
        const mitraFilter = document.getElementById('filter-mitra-gaji');
        if (mitraFilter) mitraFilter.classList.toggle('hidden', tab !== 'kontrak');
    }
    // Inisialisasi: cek apakah halaman dibuka dengan filter mitra aktif
    document.addEventListener('DOMContentLoaded', function() {
        @if($mitraId)
        switchTabGaji('kontrak');
        @endif
    });
</script>
@endpush
@endsection
