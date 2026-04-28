@extends('karyawan.sidebar')
@section('title', 'Dinas Luar Kota')

@section('content')

<header class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight uppercase">Dinas Luar Kota</h1>
        <p class="text-gray-500 mt-1 text-sm">Pengajuan perjalanan dinas ke luar kota</p>
    </div>
    <a href="{{ route('karyawan.dinas-luar.create') }}"
       class="flex items-center gap-2 bg-[#1E3A5F] hover:bg-red-600 text-white
              font-black text-xs uppercase italic px-5 py-3 rounded-xl transition-all shadow-sm">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Ajukan Dinas
    </a>
</header>

<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-wider
                           border-b border-gray-50 bg-gray-50/50">
                    <th class="px-6 py-4">Tujuan</th>
                    <th class="px-6 py-4">Berangkat</th>
                    <th class="px-6 py-4">Kembali</th>
                    <th class="px-6 py-4 text-center">Surat Tugas</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($dinasLuar as $d)
                    <tr class="hover:bg-gray-50 transition-all">
                        <td class="px-6 py-4">
                            <p class="text-[11px] font-black text-[#1E3A5F] uppercase">
                                {{ $d->tujuan }}
                            </p>
                            <p class="text-[9px] text-gray-400 font-medium">
                                Diajukan {{ $d->created_at->diffForHumans() }}
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-[11px] font-semibold text-gray-700">
                                {{ $d->tanggal_berangkat->translatedFormat('d M Y') }}
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-[11px] font-semibold text-gray-700">
                                {{ $d->tanggal_kembali->translatedFormat('d M Y') }}
                            </p>
                            @php
                                $durasi = $d->tanggal_berangkat->diffInDays($d->tanggal_kembali) + 1;
                            @endphp
                            <p class="text-[9px] text-gray-400">{{ $durasi }} hari</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if ($d->file_surat_tugas)
                                <a href="{{ Storage::url($d->file_surat_tugas) }}" target="_blank"
                                   class="p-2 rounded-lg bg-blue-50 text-blue-600
                                          hover:bg-blue-100 transition-all inline-flex"
                                   title="Lihat Surat Tugas">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                </a>
                            @else
                                <span class="text-[9px] text-gray-300 font-semibold">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $badge = match($d->status_approval) {
                                    'menunggu'  => 'bg-yellow-100 text-yellow-700',
                                    'disetujui' => 'bg-green-100 text-green-700',
                                    'ditolak'   => 'bg-red-100 text-red-700',
                                    default     => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase {{ $badge }}">
                                {{ ucfirst($d->status_approval) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('karyawan.dinas-luar.show', $d->id) }}"
                                   class="p-2 rounded-lg bg-blue-50 text-blue-600
                                          hover:bg-blue-100 transition-all" title="Detail">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                </a>
                                @if ($d->status_approval === 'menunggu')
                                    <form method="POST"
                                          action="{{ route('karyawan.dinas-luar.destroy', $d->id) }}"
                                          onsubmit="return confirm('Batalkan pengajuan dinas luar ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="p-2 rounded-lg bg-red-50 text-red-500
                                                       hover:bg-red-100 transition-all" title="Batalkan">
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
                            <i data-lucide="map-pin" class="w-12 h-12 text-gray-200 mx-auto mb-3"></i>
                            <p class="text-sm text-gray-400 font-semibold">
                                Belum ada pengajuan dinas luar kota.
                            </p>
                            <a href="{{ route('karyawan.dinas-luar.create') }}"
                               class="mt-3 inline-block text-xs font-black text-[#1E3A5F]
                                      hover:text-red-600 uppercase italic transition-colors">
                                + Ajukan Sekarang
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($dinasLuar->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">{{ $dinasLuar->links() }}</div>
    @endif
</div>

@endsection