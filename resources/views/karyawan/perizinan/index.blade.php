@extends('karyawan.sidebar')
@section('title', 'Pengajuan Izin')

@section('content')

<header class="flex justify-between items-center mb-4">
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight">Pengajuan Izin</h1>
        <p class="text-gray-500 mt-1 text-sm">Cuti, Izin Pribadi, dan Sakit</p>
    </div>
    <a href="{{ route('karyawan.perizinan.create') }}"
       class="flex items-center gap-2 bg-[#1E3A5F] hover:bg-red-600 text-white
              font-black text-xs px-5 py-3 rounded-xl transition-all shadow-sm">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Ajukan Baru
    </a>
</header>

<div x-data="{ showKuotaModal: false }">

{{-- Kuota Perizinan --}}
@if ($kuotaPerizinan)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider">
                        Kuota Perizinan {{ now()->year }}
                    </p>
                    <button @click="showKuotaModal = true" 
                            class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 px-2 py-0.5 rounded-full transition-all">
                        <i data-lucide="info" class="w-3 h-3"></i>
                        <span>Lihat Rincian Potongan</span>
                    </button>
                </div>
                <div class="flex items-center gap-4">
                    <div>
                        <span class="text-3xl font-black text-[#1E3A5F]">{{ $kuotaPerizinan->sisa }}</span>
                        <span class="text-sm text-gray-400 ml-1">/ {{ $kuotaPerizinan->kuota_total }} hari</span>
                    </div>
                    <div class="text-xs text-gray-400 border-l border-gray-100 pl-4">
                        Terpakai: <strong class="text-gray-700 font-bold">{{ $kuotaPerizinan->terpakai }} hari</strong>
                        @php
                            $totalApprovedLeaveDays = $approvedLeaves->sum('jumlah_hari');
                            $totalAlfaDays = $alfaRecords->count();
                        @endphp
                        <span class="text-[10px] text-gray-400 block mt-0.5">
                            ({{ $totalApprovedLeaveDays }} Hari Cuti + {{ $totalAlfaDays }} Hari Alfa)
                        </span>
                    </div>
                </div>
            </div>
            <div class="w-full sm:w-40 flex flex-col items-start sm:items-end">
                <button @click="showKuotaModal = true"
                        class="w-full bg-gray-50 hover:bg-blue-50/50 border border-gray-100 rounded-xl p-3 text-left sm:text-right transition-all group">
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1.5 overflow-hidden">
                        @php $pct = $kuotaPerizinan->kuota_total > 0 ? ($kuotaPerizinan->sisa / $kuotaPerizinan->kuota_total) * 100 : 0 @endphp
                        <div class="h-2.5 rounded-full transition-all
                                    {{ $pct > 50 ? 'bg-green-500' : ($pct > 25 ? 'bg-amber-500' : 'bg-red-500') }}"
                             style="width: {{ $pct }}%">
                        </div>
                    </div>
                    <div class="flex items-center justify-between sm:justify-end gap-2">
                        <span class="text-[9px] font-black text-[#1E3A5F] group-hover:text-blue-600 transition-colors uppercase tracking-tight">Rincian Sisa Jatah &rarr;</span>
                        <span class="text-[9px] text-gray-500 font-bold">{{ round($pct) }}% tersisa</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- MODAL DETAIL RINCIAN KUOTA --}}
<template x-teleport="body">
    <div x-show="showKuotaModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[3000] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">

        <div @click.away="showKuotaModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            
            {{-- Modal Header --}}
            <div class="px-6 py-5 bg-[#1E3A5F] text-white flex items-center justify-between shrink-0">
                <div>
                    <h3 class="text-base font-black tracking-tight">Rincian Penggunaan Kuota Cuti {{ now()->year }}</h3>
                    <p class="text-xs text-blue-200/80 font-medium mt-0.5">Transparansi pemotongan jatah cuti tahunan Anda</p>
                </div>
                <button @click="showKuotaModal = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-all">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Modal Content Scrollable --}}
            <div class="p-6 overflow-y-auto space-y-6 flex-1 bg-slate-50/50">

                {{-- Summary Cards Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-white border border-gray-100 rounded-2xl p-3 text-center shadow-sm">
                        <p class="text-[9px] font-black text-gray-400 uppercase">Jatah Total</p>
                        <p class="text-xl font-black text-[#1E3A5F] mt-1">{{ $kuotaPerizinan?->kuota_total ?? 0 }} <span class="text-xs font-normal text-gray-400">Hari</span></p>
                    </div>
                    <div class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-3 text-center shadow-sm">
                        <p class="text-[9px] font-black text-indigo-500 uppercase">Cuti Disetujui</p>
                        <p class="text-xl font-black text-indigo-700 mt-1">{{ $totalApprovedLeaveDays }} <span class="text-xs font-normal text-indigo-400">Hari</span></p>
                    </div>
                    <div class="bg-red-50/50 border border-red-100 rounded-2xl p-3 text-center shadow-sm">
                        <p class="text-[9px] font-black text-red-500 uppercase">Potongan Alfa</p>
                        <p class="text-xl font-black text-red-600 mt-1">{{ $totalAlfaDays }} <span class="text-xs font-normal text-red-400">Hari</span></p>
                    </div>
                    <div class="bg-emerald-50/50 border border-emerald-100 rounded-2xl p-3 text-center shadow-sm">
                        <p class="text-[9px] font-black text-emerald-600 uppercase">Sisa Kuota</p>
                        <p class="text-xl font-black text-emerald-700 mt-1">{{ $kuotaPerizinan?->sisa ?? 0 }} <span class="text-xs font-normal text-emerald-500">Hari</span></p>
                    </div>
                </div>

                {{-- Section 1: Pengajuan Cuti Resmi --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                    <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
                        <div class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                            <i data-lucide="file-check" class="w-3.5 h-3.5"></i>
                        </div>
                        <h4 class="text-xs font-black text-[#1E3A5F] uppercase tracking-wider">1. Pengajuan Cuti Resmi (Disetujui)</h4>
                        <span class="ml-auto text-[10px] font-black bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full">{{ $totalApprovedLeaveDays }} Hari</span>
                    </div>

                    @if($approvedLeaves->count() > 0)
                        <div class="divide-y divide-gray-50">
                            @foreach($approvedLeaves as $leave)
                                <div class="py-2.5 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-800">{{ $leave->labelJenis() }}</span>
                                            <span class="text-[10px] text-gray-400">({{ $leave->tanggal_mulai->format('d M Y') }} @if($leave->tanggal_mulai != $leave->tanggal_selesai) - {{ $leave->tanggal_selesai->format('d M Y') }} @endif)</span>
                                        </div>
                                        @if($leave->keterangan)
                                            <p class="text-[10px] text-gray-500 mt-0.5 italic">"{{ $leave->keterangan }}"</p>
                                        @endif
                                    </div>
                                    <span class="font-black text-indigo-600 bg-indigo-50 px-2 py-1 rounded-lg shrink-0">-{{ $leave->jumlah_hari }} Hari</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-gray-400 italic py-2 text-center">Belum ada pengajuan cuti resmi yang disetujui tahun ini.</p>
                    @endif
                </div>

                {{-- Section 2: Potongan Otomatis Alfa --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                    <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
                        <div class="w-6 h-6 rounded-lg bg-red-100 text-red-600 flex items-center justify-center">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                        </div>
                        <h4 class="text-xs font-black text-[#1E3A5F] uppercase tracking-wider">2. Potongan Otomatis Alfa (Absensi Tanpa Keterangan)</h4>
                        <span class="ml-auto text-[10px] font-black bg-red-50 text-red-600 px-2 py-0.5 rounded-full">{{ $totalAlfaDays }} Hari</span>
                    </div>

                    @if($alfaRecords->count() > 0)
                        <div class="divide-y divide-gray-50 max-h-48 overflow-y-auto pr-1">
                            @foreach($alfaRecords as $alfa)
                                <div class="py-2 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                        <span class="font-semibold text-slate-700">{{ \Carbon\Carbon::parse($alfa->tanggal)->translatedFormat('l, d F Y') }}</span>
                                    </div>
                                    <span class="font-black text-red-600 bg-red-50 px-2 py-0.5 rounded-lg shrink-0">-1 Hari</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-gray-400 italic py-2 text-center">Tidak ada riwayat Alfa (Absensi tanpa keterangan).</p>
                    @endif
                </div>

                {{-- Catatan Kebijakan Sistem --}}
                <div class="p-3.5 bg-amber-50/70 border border-amber-200/70 rounded-2xl flex items-start gap-2.5">
                    <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                    <p class="text-[11px] text-amber-800 leading-relaxed">
                        <strong>Catatan Sistem PT CBN:</strong> Setiap hari kerja di mana Anda tidak hadir tanpa membuat pengajuan izin/cuti resmi, sistem secara otomatis mencatat status <strong>Alfa</strong> dan memotong 1 hari dari kuota cuti tahunan Anda.
                    </p>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end shrink-0">
                <button @click="showKuotaModal = false" class="bg-[#1E3A5F] hover:bg-slate-800 text-white font-bold text-xs px-5 py-2.5 rounded-xl transition-all">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>

{{-- Daftar Pengajuan --}}
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[10px] font-black text-[#1E3A5F]   tracking-wider
                           border-b border-gray-50 bg-gray-50/50">
                    <th class="px-6 py-4">Jenis</th>
                    <th class="px-6 py-4">Periode</th>
                    <th class="px-6 py-4 text-center">Hari</th>
                    <th class="px-6 py-4">Keterangan</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($perizinan as $p)
                    <tr class="hover:bg-gray-50 transition-all">
                        <td class="px-6 py-4">
                            @php
                                $badge = match($p->jenisPerizinan?->slug) {
                                    'cuti'           => 'bg-indigo-100 text-indigo-700',
                                    'izin_pribadi'   => 'bg-blue-100 text-blue-700',
                                    'sakit_surat'    => 'bg-purple-100 text-purple-700',
                                    'sakit_no_surat' => 'bg-orange-100 text-orange-700',
                                    default          => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black   {{ $badge }}">
                                {{ $p->labelJenis() }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-[11px] font-semibold text-gray-700">
                                {{ $p->tanggal_mulai->format('d M Y') }}
                            </p>
                            @if ($p->tanggal_mulai != $p->tanggal_selesai)
                                <p class="text-[9px] text-gray-400">
                                    s/d {{ $p->tanggal_selesai->format('d M Y') }}
                                </p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-black text-gray-700">{{ $p->jumlah_hari }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-[11px] text-gray-500 max-w-48 truncate">
                                {{ $p->keterangan ?? '-' }}
                            </p>
                        </td>
                        <td class="px-6 py-4 text-center">
                             @php
                                 $statusBadge = match($p->status_approval) {
                                     'menunggu'            => 'bg-yellow-100 text-yellow-700',
                                     'menunggu_rekan'      => 'bg-indigo-100 text-indigo-700',
                                     'menunggu_form_mitra' => 'bg-blue-100 text-blue-700',
                                     'disetujui'           => 'bg-green-100 text-green-700',
                                     'ditolak'             => 'bg-red-100 text-red-700',
                                     default               => 'bg-gray-100 text-gray-600',
                                 };
                                 $statusLabel = match($p->status_approval) {
                                     'menunggu'            => 'Menunggu Pimpinan',
                                     'menunggu_rekan'      => 'Menunggu Rekan',
                                     'menunggu_form_mitra' => 'Belum Upload Form Mitra',
                                     'disetujui'           => 'Disetujui',
                                     'ditolak'             => 'Ditolak',
                                     default               => $p->status_approval,
                                 };
                             @endphp
                             <span class="px-2.5 py-1 rounded-lg text-[9px] font-black   {{ $statusBadge }}">
                                 {{ $statusLabel }}
                             </span>
                         </td>
                         <td class="px-6 py-4 text-center">
                             <div class="flex items-center justify-center gap-1.5">
                                 <a href="{{ route('karyawan.perizinan.show', $p->id) }}"
                                    class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all"
                                    title="Detail">
                                     <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                 </a>
                                 @if ($p->jenisPerizinan?->slug === 'cuti')
                                     <a href="{{ route('karyawan.perizinan.print', $p->id) }}" target="_blank"
                                        class="p-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all"
                                        title="Cetak Surat Cuti">
                                         <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                     </a>
                                 @endif
                                 @if (in_array($p->status_approval, ['menunggu', 'menunggu_rekan', 'menunggu_form_mitra']))
                                     <form method="POST"
                                           action="{{ route('karyawan.perizinan.destroy', $p->id) }}"
                                           onsubmit="return confirm('Batalkan pengajuan ini?')">
                                         @csrf @method('DELETE')
                                         <button type="submit"
                                                 class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-all"
                                                 title="Batalkan">
                                             <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                         </button>
                                     </form>
                                 @endif
                             </div>
                         </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <i data-lucide="calendar-x" class="w-12 h-12 text-gray-200 mx-auto mb-3"></i>
                            <p class="text-sm text-gray-400 font-semibold">Belum ada pengajuan izin.</p>
                            <a href="{{ route('karyawan.perizinan.create') }}"
                               class="mt-3 inline-block text-xs font-black text-[#1E3A5F]
                                      hover:text-red-600   italic transition-colors">
                                + Ajukan Sekarang
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($perizinan->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">
            {{ $perizinan->links() }}
        </div>
    @endif
</div>

</div>

@endsection
