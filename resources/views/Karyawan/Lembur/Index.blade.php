@extends('karyawan.sidebar')
@section('title', 'Pengajuan Lembur')

@section('content')

<header class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight uppercase">Pengajuan Lembur</h1>
        <p class="text-gray-500 mt-1 text-sm">Harus mendapat persetujuan pimpinan sebelum dilaksanakan</p>
    </div>
    <a href="{{ route('karyawan.lembur.create') }}"
       class="flex items-center gap-2 bg-[#1E3A5F] hover:bg-red-600 text-white
              font-black text-xs uppercase italic px-5 py-3 rounded-xl transition-all shadow-sm">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Ajukan Lembur
    </a>
</header>

<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-wider
                           border-b border-gray-50 bg-gray-50/50">
                    <th class="px-6 py-4">Tanggal</th>
                    <th class="px-6 py-4">Jam</th>
                    <th class="px-6 py-4 text-center">Durasi</th>
                    <th class="px-6 py-4">Keperluan</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($lembur as $l)
                    <tr class="hover:bg-gray-50 transition-all">
                        <td class="px-6 py-4">
                            <p class="text-[11px] font-black text-[#1E3A5F]">
                                {{ $l->tanggal->translatedFormat('l') }}
                            </p>
                            <p class="text-[9px] text-gray-400">
                                {{ $l->tanggal->format('d M Y') }}
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-[11px] font-semibold text-gray-700">
                                {{ $l->jam_mulai }} — {{ $l->jam_selesai }}
                            </p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-black text-gray-700">
                                {{ $l->formatDurasi() }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-[11px] text-gray-500 max-w-48 truncate">
                                {{ $l->keterangan }}
                            </p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $badge = match($l->status_approval) {
                                    'menunggu'  => 'bg-yellow-100 text-yellow-700',
                                    'disetujui' => 'bg-green-100 text-green-700',
                                    'ditolak'   => 'bg-red-100 text-red-700',
                                    default     => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase {{ $badge }}">
                                {{ ucfirst($l->status_approval) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('karyawan.lembur.show', $l->id) }}"
                                   class="p-2 rounded-lg bg-blue-50 text-blue-600
                                          hover:bg-blue-100 transition-all" title="Detail">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                </a>
                                @if ($l->status_approval === 'menunggu')
                                    <form method="POST"
                                          action="{{ route('karyawan.lembur.destroy', $l->id) }}"
                                          onsubmit="return confirm('Batalkan pengajuan lembur ini?')">
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
                            <i data-lucide="clock" class="w-12 h-12 text-gray-200 mx-auto mb-3"></i>
                            <p class="text-sm text-gray-400 font-semibold">
                                Belum ada pengajuan lembur.
                            </p>
                            <a href="{{ route('karyawan.lembur.create') }}"
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
    @if ($lembur->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">{{ $lembur->links() }}</div>
    @endif
</div>

@endsection