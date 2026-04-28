@extends('karyawan.sidebar')
@section('title', 'Pengajuan Izin')

@section('content')

<header class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight  ">Pengajuan Izin</h1>
        <p class="text-gray-500 mt-1 text-sm">Cuti, Izin Pribadi, dan Sakit</p>
    </div>
    <a href="{{ route('karyawan.perizinan.create') }}"
       class="flex items-center gap-2 bg-[#1E3A5F] hover:bg-red-600 text-white
              font-black text-xs   italic px-5 py-3 rounded-xl transition-all shadow-sm">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Ajukan Baru
    </a>
</header>

{{-- Kuota Cuti --}}
@if ($kuotaCuti)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black text-gray-400   tracking-widest mb-1">
                    Kuota Cuti {{ now()->year }}
                </p>
                <div class="flex items-center gap-4">
                    <div>
                        <span class="text-3xl font-black text-[#1E3A5F]">{{ $kuotaCuti->sisa }}</span>
                        <span class="text-sm text-gray-400 ml-1">/ {{ $kuotaCuti->kuota_total }} hari</span>
                    </div>
                    <div class="text-xs text-gray-400">
                        Terpakai: <strong class="text-gray-600">{{ $kuotaCuti->terpakai }} hari</strong>
                    </div>
                </div>
            </div>
            <div class="w-32">
                <div class="w-full bg-gray-100 rounded-full h-3">
                    @php $pct = $kuotaCuti->kuota_total > 0 ? ($kuotaCuti->sisa / $kuotaCuti->kuota_total) * 100 : 0 @endphp
                    <div class="h-3 rounded-full transition-all
                                {{ $pct > 50 ? 'bg-green-500' : ($pct > 25 ? 'bg-amber-500' : 'bg-red-500') }}"
                         style="width: {{ $pct }}%">
                    </div>
                </div>
                <p class="text-[9px] text-gray-400 font-semibold mt-1 text-right">
                    {{ round($pct) }}% tersisa
                </p>
            </div>
        </div>
    </div>
@endif

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
                                $badge = match($p->jenis_izin) {
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
                                    'menunggu'  => 'bg-yellow-100 text-yellow-700',
                                    'disetujui' => 'bg-green-100 text-green-700',
                                    'ditolak'   => 'bg-red-100 text-red-700',
                                    default     => 'bg-gray-100 text-gray-600',
                                };
                                $statusLabel = match($p->status_approval) {
                                    'menunggu'  => 'Menunggu',
                                    'disetujui' => 'Disetujui',
                                    'ditolak'   => 'Ditolak',
                                    default     => $p->status_approval,
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
                                @if ($p->status_approval === 'menunggu')
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

@endsection