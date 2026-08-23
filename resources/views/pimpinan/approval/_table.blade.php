<div class="overflow-x-auto">
    <table class="w-full text-left">
        <thead>
            <tr class="text-[12px] font-black text-[#1E3A5F] border-b border-gray-50 bg-gray-50/30 tracking-tight">
                <th class="px-6 py-4">Karyawan</th>
                <th class="px-6 py-4">Kategori</th>
                <th class="px-6 py-4">Periode / Waktu</th>
                <th class="px-6 py-4 text-center">Durasi</th>
                <th class="px-6 py-4 text-center">Status</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($items as $item)
                <tr class="hover:bg-gray-50/50 transition-all group cursor-pointer"
                    @click="openDetail({{ json_encode($type === 'perizinan' ? $item->load(['karyawan.role', 'jenisPerizinan', 'rekanKerja']) : $item->load('karyawan.role')) }}, '{{ $type }}')">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400 font-black text-xs shadow-inner group-hover:bg-blue-100 group-hover:text-blue-600 transition-all">
                                {{ strtoupper(substr($item->karyawan->nama, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-[12px] font-black text-[#1E3A5F] group-hover:text-blue-600 transition-colors">{{ $item->karyawan->nama }}</p>
                                <p class="text-[9px] text-gray-400 font-bold tracking-tight">{{ $item->karyawan->jabatan }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1.5 rounded-xl text-[10px] font-black shadow-sm border
                             {{ $type === 'perizinan' ? ($item->jenisPerizinan?->slug === 'cuti' ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : ($item->jenisPerizinan?->slug === 'dinas_luar' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-blue-50 text-blue-700 border-blue-100')) : ($type === 'lembur' ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-gray-50 text-gray-600 border-gray-100') }}">
                            {{ $type === 'perizinan' ? $item->labelJenis() : ($type === 'lembur' ? 'Lembur' : 'Dinas Luar') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-[11px] font-bold text-gray-700">
                        <p>{{ ($item->tanggal_mulai ?? ($item->tanggal ?? $item->tanggal_berangkat))->format('d M Y') }}</p>
                        @if (isset($item->tanggal_selesai) || isset($item->tanggal_kembali))
                            <p class="text-[9px] text-gray-400 font-medium">s/d {{ ($item->tanggal_selesai ?? $item->tanggal_kembali)->format('d M Y') }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-black text-[#1E3A5F]">{{ $type === 'lembur' ? $item->formatDurasi() : ($item->jumlah_hari ?? '-') }}</span>
                        <span class="text-[10px] font-black text-gray-400 ml-1">{{ $type === 'lembur' ? 'Jam' : 'Hari' }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @php
                            $sb = match($item->status_approval) {
                                'menunggu'  => 'bg-yellow-50 text-yellow-600 border-yellow-100',
                                'disetujui' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                'ditolak'   => 'bg-red-50 text-red-600 border-red-100',
                                default     => 'bg-gray-50 text-gray-500 border-gray-100',
                            };
                        @endphp
                        <span class="px-4 py-1.5 rounded-full text-[9px] font-black border {{ $sb }} uppercase">
                            {{ $item->status_approval }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right" @click.stop>
                        <div class="flex justify-end gap-2">
                            @if($item->status_approval === 'menunggu')
                                <form method="POST" action="{{ route('pimpinan.approval.'.$type.'.setuju', $item->id) }}" onsubmit="return confirm('Setujui?')">
                                    @csrf @method('PATCH')
                                    <button class="p-2 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                <button @click="openDetail({{ json_encode($type === 'perizinan' ? $item->load(['karyawan.role', 'jenisPerizinan', 'rekanKerja']) : $item->load('karyawan.role')) }}, '{{ $type }}')" 
                                        class="p-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            @else
                                <button @click="openDetail({{ json_encode($type === 'perizinan' ? $item->load(['karyawan.role', 'jenisPerizinan', 'rekanKerja']) : $item->load('karyawan.role')) }}, '{{ $type }}')"
                                        class="p-2 rounded-xl bg-gray-50 text-gray-400 hover:bg-[#1E3A5F] hover:text-white transition-all shadow-sm">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-14 text-center">
                        <div class="flex flex-col items-center gap-3 opacity-20">
                            <i data-lucide="inbox" class="w-10 h-10 text-[#1E3A5F]"></i>
                            <p class="text-[10px] font-black uppercase tracking-widest">Tidak Ada Pengajuan</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
