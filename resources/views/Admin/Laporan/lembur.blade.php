{{-- resources/views/Admin/Laporan/lembur.blade.php --}}
@extends('Admin.sidebar')

@section('title', 'Rekap Lembur Karyawan')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#1E3A5F]">Rekap Lembur Karyawan</h1>
            <p class="text-sm text-slate-500 mt-0.5">Monitoring & Rekapitulasi Pengajuan Lembur Karyawan PT CBN</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.laporan.lembur.export', request()->all()) }}"
               target="_blank"
               class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all shadow-md shadow-emerald-600/20">
                <i data-lucide="download" class="w-4 h-4"></i>
                Export Excel
            </a>
        </div>
    </div>

    {{-- Kartu Ringkasan / Summary Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold shrink-0">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pengajuan</p>
                <h3 class="text-xl font-black text-slate-800 mt-0.5">{{ $ringkasan['total_pengajuan'] }} <span class="text-xs font-normal text-slate-400">berkas</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold shrink-0">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Disetujui</p>
                <h3 class="text-xl font-black text-emerald-600 mt-0.5">{{ $ringkasan['total_disetujui'] }} <span class="text-xs font-normal text-slate-400">pengajuan</span></h3>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <form method="GET" action="{{ route('admin.laporan.lembur.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">

            {{-- Bulan --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Bulan</label>
                <select name="bulan" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Bulan</option>
                    @foreach(range(1, 12) as $b)
                        <option value="{{ $b }}" @selected($bulan !== null && $bulan != '' && $b == $bulan)>
                            {{ \Carbon\Carbon::create(null, $b, 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tahun --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Tahun</label>
                <select name="tahun" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(range(now()->year - 2, now()->year + 1) as $t)
                        <option value="{{ $t }}" @selected($t == $tahun)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status Approval --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Status Approval</label>
                <select name="status_approval" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="semua" @selected($statusApproval == 'semua')>Semua Status</option>
                    <option value="disetujui" @selected($statusApproval == 'disetujui')>Disetujui</option>
                    <option value="menunggu" @selected($statusApproval == 'menunggu')>Menunggu</option>
                    <option value="ditolak" @selected($statusApproval == 'ditolak')>Ditolak</option>
                </select>
            </div>

            {{-- Karyawan --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Karyawan</label>
                <select name="user_id" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Karyawan</option>
                    @foreach($semuaKaryawan as $k)
                        <option value="{{ $k->id }}" @selected($k->id == $karyawanId)>
                            {{ $k->nama }} ({{ $k->nip }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol Action --}}
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-[#1E3A5F] hover:bg-[#152a45] text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-md shadow-blue-900/10">
                    Tampilkan
                </button>
                <a href="{{ route('admin.laporan.lembur.index') }}" class="flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-2.5 rounded-xl transition" title="Reset Filter">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 text-center">No</th>
                        <th class="px-4 py-3.5">ID Lembur</th>
                        <th class="px-4 py-3.5">Karyawan</th>
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5 text-center">Waktu</th>
                        <th class="px-4 py-3.5 text-center">Durasi</th>
                        <th class="px-4 py-3.5">Keperluan</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($lemburList as $index => $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3.5 text-center text-slate-400 font-bold">{{ $index + 1 }}</td>
                            <td class="px-4 py-3.5 font-bold text-[#1E3A5F]">{{ $item->id }}</td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-800">{{ $item->karyawan?->nama ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400 font-semibold">{{ $item->karyawan?->nip }} • {{ $item->karyawan?->jabatan ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5 font-semibold text-slate-700">
                                {{ $item->tanggal->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-4 py-3.5 text-center font-mono text-slate-600">
                                {{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-[11px]">
                                    {{ $item->formatDurasi() }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 max-w-xs truncate" title="{{ $item->keterangan }}">
                                {{ $item->keterangan }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($item->isDisetujui())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px] uppercase border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Disetujui
                                    </span>
                                @elseif($item->isMenunggu())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 font-bold text-[10px] uppercase border border-amber-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Menunggu
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-50 text-red-700 font-bold text-[10px] uppercase border border-red-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <a href="{{ route('lembur.print', $item->id) }}" target="_blank"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 hover:bg-[#1E3A5F] hover:text-white text-slate-600 transition-colors"
                                   title="Cetak Slip Lembur">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400 space-y-2">
                                    <i data-lucide="inbox" class="w-10 h-10 stroke-1"></i>
                                    <p class="font-bold text-sm text-slate-600">Tidak Ada Data Lembur</p>
                                    <p class="text-xs">Tidak ditemukan pengajuan lembur karyawan untuk filter ini.</p>
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
