<div class="overflow-x-auto">
    <table class="w-full text-left">
        <thead>
            <tr class="text-[10px] font-black text-gray-400  border-b border-gray-50 bg-gray-50/30">
                <th class="px-8 py-5">Karyawan</th>
                <th class="px-8 py-5">Kategori & Konsekuensi</th>
                <th class="px-8 py-5">Periode / Waktu</th>
                <th class="px-8 py-5 text-center">Durasi</th>
                <th class="px-8 py-5 text-center">Status</th>
                <th class="px-8 py-5 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($items as $item)
                <tr class="hover:bg-gray-50/50 transition-all group cursor-pointer"
                    @click="openDetail({{ json_encode($item->load('karyawan')) }}, '{{ $type }}')">
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400 font-black text-xs  shadow-inner group-hover:bg-blue-100 group-hover:text-blue-600 transition-all">
                                {{ substr($item->karyawan->nama, 0, 2) }}
                            </div>
                            <div>
                                <p class="text-[12px] font-black text-[#1E3A5F]  group-hover:text-blue-600 transition-colors">{{ $item->karyawan->nama }}</p>
                                <p class="text-[9px] text-gray-400 font-bold  tracking-tight">{{ $item->karyawan->jabatan }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <span class="px-3 py-1 rounded-xl text-[9px] font-black  shadow-sm border
                            {{ $type === 'perizinan' ? ($item->jenis_izin === 'cuti' ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-blue-50 text-blue-700 border-blue-100') : 'bg-gray-50 text-gray-600 border-gray-100' }}">
                            {{ $type === 'perizinan' ? $item->labelJenis() : ($type === 'lembur' ? 'Lembur' : 'Dinas Luar') }}
                        </span>
                        <div class="flex items-center gap-1.5 mt-2">
                            @if($type === 'perizinan')
                                @if($item->jenis_izin === 'cuti')
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                                    <p class="text-[9px] font-black italic  text-indigo-500">Potong Uang Makan</p>
                                @elseif($item->memotongCuti())
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                                    <p class="text-[9px] font-black italic  text-orange-500">Memotong Kuota</p>
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <p class="text-[9px] font-black italic  text-emerald-500">Bebas Kuota</p>
                                @endif
                            @elseif($type === 'lembur')
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                <p class="text-[9px] font-black italic  text-amber-500">Perlu ACC Pimpinan</p>
                            @else
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                <p class="text-[9px] font-black italic  text-blue-500">Validasi SPJ</p>
                            @endif
                        </div>
                    </td>
                    <td class="px-8 py-6 text-[11px] font-bold text-gray-700">
                        <p>{{ ($item->tanggal_mulai ?? ($item->tanggal ?? $item->tanggal_berangkat))->format('d M Y') }}</p>
                        @if (isset($item->tanggal_selesai) || isset($item->tanggal_kembali))
                            <p class="text-[9px] text-gray-400 font-medium">s/d {{ ($item->tanggal_selesai ?? $item->tanggal_kembali)->format('d M Y') }}</p>
                        @endif
                    </td>
                    <td class="px-8 py-6 text-center">
                        <span class="text-base font-black text-[#1E3A5F]">{{ $type === 'lembur' ? $item->formatDurasi() : ($item->jumlah_hari ?? '-') }}</span>
                        <span class="text-[10px] font-black text-gray-400  italic ml-1">{{ $type === 'lembur' ? 'Jam' : 'Hari' }}</span>
                    </td>
                    <td class="px-8 py-6 text-center">
                        @php
                            $sb = match($item->status_approval) {
                                'menunggu'  => 'bg-yellow-50 text-yellow-600 border-yellow-100',
                                'disetujui' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                'ditolak'   => 'bg-red-50 text-red-600 border-red-100',
                                default     => 'bg-gray-50 text-gray-500 border-gray-100',
                            };
                        @endphp
                        <span class="px-4 py-1.5 rounded-full text-[9px] font-black  border {{ $sb }}">
                            {{ $item->status_approval }}
                        </span>
                    </td>
                    <td class="px-8 py-6 text-right" @click.stop>
                        <div class="flex justify-end gap-2">
                            @if($item->status_approval === 'menunggu')
                                <form method="POST" action="{{ route('pimpinan.approval.'.$type.'.setuju', $item->id) }}" onsubmit="return confirm('Setujui?')">
                                    @csrf @method('PATCH')
                                    <button class="p-2.5 rounded-2xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                <button @click="openDetail({{ json_encode($item->load('karyawan')) }}, '{{ $type }}')" 
                                        class="p-2.5 rounded-2xl bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            @else
                                <button @click="openDetail({{ json_encode($item->load('karyawan')) }}, '{{ $type }}')"
                                        class="p-2.5 rounded-2xl bg-gray-50 text-gray-400 hover:bg-[#1E3A5F] hover:text-white transition-all shadow-sm">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-8 py-14 text-center">
                    <div class="flex flex-col items-center gap-3 opacity-20">
                        <i data-lucide="inbox" class="w-10 h-10"></i>
                        <p class="text-[9px] font-black  tracking-[0.2em]">Tidak Ada Pengajuan</p>
                    </div>
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
