@extends('karyawan.sidebar')
@section('title', 'Dinas Luar Kota')

@section('content')

<header class="flex justify-between items-center mb-4">
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight">Dinas Luar Kota</h1>
        <p class="text-gray-500 mt-1 text-sm">Pengajuan Penugasan Perjalanan Dinas — PT CBN</p>
    </div>
    <a href="{{ route('karyawan.dinas-luar.create') }}"
       class="flex items-center gap-2 bg-[#1E3A5F] hover:bg-emerald-600 text-white
              font-black text-xs px-5 py-3 rounded-xl transition-all shadow-sm">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Ajukan Dinas Luar
    </a>
</header>

{{-- Tabel Riwayat --}}
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-black text-[#1E3A5F] italic text-xs uppercase tracking-wider">Riwayat Pengajuan Dinas Luar</h3>
        <span class="text-xs text-gray-400 font-medium">Total: {{ $dinasLuarRequests->total() }} Pengajuan</span>
    </div>

    @if ($dinasLuarRequests->isEmpty())
        <div class="p-12 text-center">
            <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-3">
                <i data-lucide="map-pin-off" class="w-8 h-8"></i>
            </div>
            <h4 class="text-sm font-black text-[#1E3A5F]">Belum Ada Pengajuan Dinas Luar Kota</h4>
            <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                Anda belum pernah mengajukan perjalanan dinas luar kota. Klik tombol di atas untuk mengajukan.
            </p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal Tugas</th>
                        <th class="px-5 py-3.5">Durasi</th>
                        <th class="px-5 py-3.5">Maksud / Keterangan</th>
                        <th class="px-5 py-3.5">Surat Tugas</th>
                        <th class="px-5 py-3.5 text-center">Status Approval</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-semibold text-gray-700">
                    @foreach ($dinasLuarRequests as $item)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-black text-[#1E3A5F]">
                                    {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') }}
                                </div>
                                <div class="text-[10px] text-gray-400">
                                    s/d {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d M Y') }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-bold rounded-lg text-[10px] border border-emerald-100">
                                    {{ $item->jumlah_hari }} Hari
                                </span>
                            </td>
                            <td class="px-5 py-4 max-w-xs">
                                <p class="line-clamp-2 text-gray-600">{{ $item->keterangan ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                @if ($item->file_bukti)
                                    <a href="{{ asset('storage/' . $item->file_bukti) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 text-emerald-600 hover:text-emerald-700 font-bold text-[11px] underline">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                        <span>Lihat Surat Tugas</span>
                                    </a>
                                @else
                                    <span class="text-gray-400 italic text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                @php
                                    $badgeStyle = match($item->status_approval) {
                                        'disetujui' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'ditolak'   => 'bg-red-50 text-red-700 border-red-200',
                                        default     => 'bg-amber-50 text-amber-700 border-amber-200',
                                    };
                                    $statusLabel = match($item->status_approval) {
                                        'disetujui' => 'Disetujui Pimpinan',
                                        'ditolak'   => 'Ditolak',
                                        default     => 'Menunggu Persetujuan Pimpinan',
                                    };
                                @endphp
                                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-black border {{ $badgeStyle }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('karyawan.dinas-luar.show', $item->id) }}"
                                   class="inline-flex items-center gap-1 bg-gray-100 hover:bg-gray-200 text-[#1E3A5F] px-3 py-1.5 rounded-lg text-[11px] font-bold transition-all">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>Detail</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($dinasLuarRequests->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $dinasLuarRequests->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
