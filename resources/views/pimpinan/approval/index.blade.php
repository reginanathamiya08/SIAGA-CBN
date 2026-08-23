@extends('pimpinan.sidebar')
@section('title', 'Approval Pengajuan')

@section('content')

<div x-data="{ 
    detailModal: false, 
    activeItem: null,
    openDetail(item, type) {
        this.activeItem = item;
        this.activeItem.type = type;
        this.detailModal = true;
    }
}">

    <header class="mb-6">
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight">Approval Pengajuan</h1>
        <div class="flex items-center gap-2 mt-1">
            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
            <p class="text-gray-500 text-xs font-bold">Pusat Validasi — PT CBN</p>
        </div>
    </header>

    {{-- Tab navigasi Tipe Utama & Statistik --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
        <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ([
                ['perizinan', 'Perizinan', $jumlahMenunggu['perizinan'], 'calendar'],
                ['lembur',    'Lembur',    $jumlahMenunggu['lembur'], 'clock'],
            ] as [$val, $label, $jumlah, $icon])
                <a href="{{ route('pimpinan.approval.index', ['tipe' => $val, 'status' => request('status','menunggu'), 'jenis' => request('jenis','semua')]) }}"
                   class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-xs 
                          transition-all whitespace-nowrap group
                          {{ $tipe === $val
                               ? 'bg-[#1E3A5F] text-white shadow-xl shadow-blue-200/50'
                               : 'bg-white border border-gray-100 text-gray-400 hover:border-blue-200 hover:text-[#1E3A5F]' }}">
                    <i data-lucide="{{ $icon }}" class="w-4 h-4 {{ $tipe === $val ? 'text-blue-400' : 'text-gray-300 group-hover:text-blue-400' }}"></i>
                    {{ $label }}
                    @if ($jumlah > 0)
                        <span class="{{ $tipe === $val ? 'bg-blue-400 text-white' : 'bg-red-500 text-white' }}
                                       text-[9px] font-black px-2 py-0.5 rounded-full min-w-[20px] text-center">
                            {{ $jumlah }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="bg-blue-50 px-6 py-3 rounded-2xl border border-blue-100 text-center min-w-[160px] shadow-sm">
            <p class="text-[9px] font-black text-blue-400 mb-1 uppercase">Menunggu Persetujuan</p>
            <p class="text-xl font-black text-blue-700">{{ array_sum($jumlahMenunggu) }}</p>
        </div>
    </div>
    {{-- Baris Filter Terpisah & Berlabel --}}
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-6 space-y-4">
        
        {{-- Status Approval --}}
        <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                <i data-lucide="filter" class="w-3 h-3 text-blue-500"></i>
                Filter Status Approval:
            </p>
            <div class="flex flex-wrap gap-2.5">
                {{-- Menunggu --}}
                <a href="{{ route('pimpinan.approval.index', array_merge(request()->query(), ['status' => 'menunggu'])) }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-xs transition-all whitespace-nowrap border
                          {{ request('status', 'menunggu') === 'menunggu'
                               ? 'bg-[#1E3A5F] text-white shadow-md border-[#1E3A5F]'
                               : 'bg-gray-50 border-gray-100 text-gray-600 hover:bg-gray-100' }}">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    Menunggu
                    <span class="px-1.5 py-0.5 rounded-md text-[9px] font-black {{ request('status', 'menunggu') === 'menunggu' ? 'bg-amber-400 text-amber-950' : 'bg-amber-100 text-amber-800' }}">
                        {{ $countMenunggu }}
                    </span>
                </a>

                {{-- Disetujui --}}
                <a href="{{ route('pimpinan.approval.index', array_merge(request()->query(), ['status' => 'disetujui'])) }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-xs transition-all whitespace-nowrap border
                          {{ request('status') === 'disetujui'
                               ? 'bg-[#1E3A5F] text-white shadow-md border-[#1E3A5F]'
                               : 'bg-gray-50 border-gray-100 text-gray-600 hover:bg-gray-100' }}">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    Disetujui
                    <span class="px-1.5 py-0.5 rounded-md text-[9px] font-black {{ request('status') === 'disetujui' ? 'bg-emerald-400 text-emerald-950' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $countDisetujui }}
                    </span>
                </a>

                {{-- Ditolak --}}
                <a href="{{ route('pimpinan.approval.index', array_merge(request()->query(), ['status' => 'ditolak'])) }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-xs transition-all whitespace-nowrap border
                          {{ request('status') === 'ditolak'
                               ? 'bg-[#1E3A5F] text-white shadow-md border-[#1E3A5F]'
                               : 'bg-gray-50 border-gray-100 text-gray-600 hover:bg-gray-100' }}">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    Ditolak
                    <span class="px-1.5 py-0.5 rounded-md text-[9px] font-black {{ request('status') === 'ditolak' ? 'bg-red-400 text-red-950' : 'bg-red-100 text-red-800' }}">
                        {{ $countDitolak }}
                    </span>
                </a>

                {{-- Semua Status --}}
                <a href="{{ route('pimpinan.approval.index', array_merge(request()->query(), ['status' => 'semua'])) }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-xs transition-all whitespace-nowrap border
                          {{ request('status') === 'semua'
                               ? 'bg-[#1E3A5F] text-white shadow-md border-[#1E3A5F]'
                               : 'bg-gray-50 border-gray-100 text-gray-600 hover:bg-gray-100' }}">
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                    Semua Status
                    <span class="px-1.5 py-0.5 rounded-md text-[9px] font-black {{ request('status') === 'semua' ? 'bg-blue-300 text-blue-950' : 'bg-blue-100 text-blue-800' }}">
                        {{ $countSemuaStatus }}
                    </span>
                </a>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-3">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                <i data-lucide="users" class="w-3 h-3 text-emerald-500"></i>
                Filter Jenis Karyawan:
            </p>
            <div class="flex flex-wrap gap-2.5">
                {{-- Semua Karyawan --}}
                <a href="{{ route('pimpinan.approval.index', array_merge(request()->query(), ['jenis' => 'semua'])) }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-xs transition-all whitespace-nowrap border
                          {{ request('jenis', 'semua') === 'semua'
                               ? 'bg-slate-700 text-white shadow-md border-slate-700'
                               : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                    Semua Karyawan
                    <span class="px-1.5 py-0.5 rounded-md text-[9px] font-black {{ request('jenis', 'semua') === 'semua' ? 'bg-slate-500 text-white' : 'bg-slate-200 text-slate-700' }}">
                        {{ $countSemua }}
                    </span>
                </a>

                {{-- Karyawan Tetap --}}
                <a href="{{ route('pimpinan.approval.index', array_merge(request()->query(), ['jenis' => 'tetap'])) }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-xs transition-all whitespace-nowrap border
                          {{ request('jenis') === 'tetap'
                               ? 'bg-slate-700 text-white shadow-md border-slate-700'
                               : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                    Karyawan Tetap
                    <span class="px-1.5 py-0.5 rounded-md text-[9px] font-black {{ request('jenis') === 'tetap' ? 'bg-slate-500 text-white' : 'bg-slate-200 text-slate-700' }}">
                        {{ $countTetap }}
                    </span>
                </a>

                {{-- Karyawan Kontrak --}}
                <a href="{{ route('pimpinan.approval.index', array_merge(request()->query(), ['jenis' => 'kontrak'])) }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-xs transition-all whitespace-nowrap border
                          {{ request('jenis') === 'kontrak'
                               ? 'bg-slate-700 text-white shadow-md border-slate-700'
                               : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                    Karyawan Kontrak
                    <span class="px-1.5 py-0.5 rounded-md text-[9px] font-black {{ request('jenis') === 'kontrak' ? 'bg-slate-500 text-white' : 'bg-slate-200 text-slate-700' }}">
                        {{ $countKontrak }}
                    </span>
                </a>
        </div>

    </div>

    @php
        $activeItems = match($tipe){'perizinan'=>$perizinan, 'lembur'=>$lembur, default=>$perizinan};
        $titleTipe = ucfirst(str_replace('_', ' ', $tipe));
    @endphp

    {{-- TABEL UTAMA --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/10">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-6 bg-blue-600 rounded-full"></div>
                <h2 class="text-sm font-black text-[#1E3A5F]">Daftar {{ $titleTipe }}</h2>
            </div>
            <div class="text-[10px] font-black text-gray-400">
                Menampilkan: <span class="text-blue-600">{{ request('jenis','Semua') }}</span> &bull; Status: <span class="text-blue-600">{{ request('status','Menunggu') }}</span>
            </div>
        </div>
        
        @include('pimpinan.approval._table', ['items' => $activeItems, 'type' => $tipe])

        @if ($activeItems->hasPages())
            <div class="px-8 py-6 border-t border-gray-50 bg-gray-50/10">{{ $activeItems->links() }}</div>
        @endif
    </div>

    {{-- ── MODAL DETAIL PREMIUM ──────────────────────────────────────────── --}}
    <template x-teleport="body">
        <div x-show="detailModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="fixed inset-0 z-[3000] flex items-center justify-center p-4" x-cloak>
        
        <div class="absolute inset-0 bg-[#1E3A5F]/60 backdrop-blur-md" @click="detailModal = false"></div>

        <div class="relative bg-white w-full rounded-[3rem] shadow-2xl overflow-hidden border border-white/20 transition-all duration-300 max-h-[85vh] flex flex-col"
             :class="(activeItem && (activeItem.file_bukti || activeItem.file_surat_tugas)) ? 'max-w-2xl' : 'max-w-lg'">
            
            <!-- Close Button (Fixed & Floating, never scrolls!) -->
            <button @click="detailModal = false" class="absolute top-6 right-6 text-gray-400 hover:text-red-600 transition-colors p-2 z-50 rounded-full bg-gray-50 hover:bg-red-50 hover:shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <!-- Tailwind Compiler Safelist -->
            <div class="hidden max-w-lg max-w-2xl md:w-7/12 w-full max-h-[85vh] overflow-y-auto"></div>

            <!-- Scrollable Content Wrapper -->
            <div class="overflow-y-auto w-full h-full">
                <template x-if="activeItem">
                    <div class="flex flex-col md:flex-row">
                    <!-- Left column (Hanya muncul jika ada file bukti atau surat tugas) -->
                    <template x-if="activeItem.file_bukti || activeItem.file_surat_tugas">
                        <div class="w-full md:w-5/12 bg-gray-50 flex flex-col p-8 border-r border-gray-100">
                            <!-- Diketahui Oleh Rekan Kerja (Jika ada) -->
                            <template x-if="activeItem.rekan_kerja">
                                <div class="mb-5 bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                                    <h4 class="text-[9px] font-black text-gray-400 mb-2.5 uppercase tracking-wider">Diketahui Oleh</h4>
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center font-bold text-xs shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-black text-gray-800 truncate" x-text="activeItem.rekan_kerja.nama"></p>
                                            <p class="text-[8px] text-gray-400 font-bold uppercase tracking-wider truncate" x-text="activeItem.rekan_kerja.jabatan || '-'"></p>
                                        </div>
                                    </div>
                                    <div class="mt-2.5">
                                        <template x-if="activeItem.status_rekan === 'disetujui'">
                                            <span class="inline-block px-2 py-0.5 rounded text-[8px] font-black bg-green-100 text-green-700">
                                                ✅ Disetujui Rekan
                                            </span>
                                        </template>
                                        <template x-if="activeItem.status_rekan !== 'disetujui'">
                                            <span class="inline-block px-2 py-0.5 rounded text-[8px] font-black bg-amber-100 text-amber-700">
                                                ⏳ Menunggu Konfirmasi Rekan
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Lampiran Berkas -->
                            <div class="mb-5">
                                <h4 class="text-[9px] font-black text-gray-400 mb-2.5 uppercase tracking-wider">Lampiran Dokumen</h4>
                                <div class="aspect-[3/2] bg-white rounded-2xl border border-gray-100 flex flex-col items-center justify-center p-4 text-center group hover:border-blue-300 transition-all shadow-sm">
                                    <a :href="'/storage/' + (activeItem.file_bukti || activeItem.file_surat_tugas)" target="_blank" class="flex flex-col items-center">
                                        <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center mb-2 group-hover:scale-105 transition-transform shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </div>
                                        <p class="text-[9px] font-black text-[#1E3A5F]">Buka Lampiran</p>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="mt-auto bg-blue-50 p-5 rounded-3xl border border-blue-100">
                                <h4 class="text-[9px] font-black text-blue-700  mb-2">Policy Summary</h4>
                                <ul class="space-y-2 text-[9px] font-bold text-blue-600 leading-tight">
                                    <template x-if="activeItem.type === 'perizinan' && activeItem.jenis_perizinan">
                                        <li><span x-text="activeItem.jenis_perizinan.slug === 'dinas_luar' ? '• Perlu pelaporan SPJ setelah dinas' : (activeItem.jenis_perizinan.memotong_uang_makan ? '• Potong Uang Makan Rp 35k/hari' : (activeItem.jenis_perizinan.memotong_kuota ? '• Memotong Kuota Tahunan' : '• Tidak Memotong Kuota'))"></span></li>
                                    </template>
                                    <template x-if="activeItem.type === 'lembur'"><li>• Hitungan jam tambahan terencana</li></template>
                                    <li>• Tercatat otomatis di absensi harian</li>
                                </ul>
                            </div>
                        </div>
                    </template>

                    <!-- Main Column / Right Column -->
                    <div class="p-8 flex flex-col w-full"
                         :class="(activeItem.file_bukti || activeItem.file_surat_tugas) ? 'md:w-7/12' : ''">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <div class="flex gap-2">
                                    <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-black uppercase" x-text="activeItem.type"></span>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black shadow-sm uppercase" 
                                          :class="(activeItem.karyawan.role && activeItem.karyawan.role.slug === 'karyawan_tetap') ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                                          x-text="(activeItem.karyawan.role && activeItem.karyawan.role.slug === 'karyawan_tetap') ? 'Tetap' : 'Kontrak'"></span>
                                </div>
                                <h2 class="text-2xl font-black text-[#1E3A5F] mt-3" x-text="activeItem.karyawan.nama"></h2>
                                <p class="text-[10px] font-bold text-gray-400 mt-2 uppercase" x-text="activeItem.karyawan.jabatan"></p>
                            </div>
                        </div>

                        <div class="space-y-6 flex-1">
                            <!-- Diketahui Oleh Rekan Kerja (Hanya muncul jika tidak ada berkas file) -->
                            <template x-if="!(activeItem.file_bukti || activeItem.file_surat_tugas) && activeItem.rekan_kerja">
                                <div class="bg-indigo-50/50 p-4 rounded-2xl border border-indigo-100 flex items-center justify-between shadow-sm">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 bg-indigo-100 text-indigo-700 rounded-xl flex items-center justify-center font-bold text-xs shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-black text-gray-800 truncate" x-text="activeItem.rekan_kerja.nama"></p>
                                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider truncate" x-text="'Diketahui Oleh • ' + (activeItem.rekan_kerja.jabatan || '-')"></p>
                                        </div>
                                    </div>
                                    <div>
                                        <template x-if="activeItem.status_rekan === 'disetujui'">
                                            <span class="inline-block px-2.5 py-1 rounded-lg text-[9px] font-black bg-green-100 text-green-700">
                                                ✅ Disetujui Rekan
                                            </span>
                                        </template>
                                        <template x-if="activeItem.status_rekan !== 'disetujui'">
                                            <span class="inline-block px-2.5 py-1 rounded-lg text-[9px] font-black bg-amber-100 text-amber-700">
                                                ⏳ Menunggu Konfirmasi Rekan
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Policy Summary (Hanya muncul jika tidak ada berkas file) -->
                            <template x-if="!(activeItem.file_bukti || activeItem.file_surat_tugas)">
                                <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100 flex items-center gap-2.5 text-[10px] font-bold text-blue-600 shadow-sm">
                                    <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span>
                                        <span x-text="activeItem.type === 'perizinan' && activeItem.jenis_perizinan ? (activeItem.jenis_perizinan.slug === 'dinas_luar' ? 'Perlu pelaporan SPJ setelah dinas' : (activeItem.jenis_perizinan.memotong_uang_makan ? 'Potong Uang Makan Rp 35k/hari' : (activeItem.jenis_perizinan.memotong_kuota ? 'Memotong Kuota Tahunan' : 'Tidak Memotong Kuota'))) : 'Hitungan jam tambahan terencana'"></span>
                                        • Tercatat otomatis di absensi harian
                                    </span>
                                </div>
                            </template>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400  mb-1 tracking-tighter">Tanggal Mulai</p>
                                    <p class="text-xs font-black text-[#1E3A5F]" x-text="new Date(activeItem.tanggal_mulai || activeItem.tanggal || activeItem.tanggal_berangkat).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'})"></p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400  mb-1 tracking-tighter">Tanggal Selesai</p>
                                    <p class="text-xs font-black text-[#1E3A5F]" x-text="new Date(activeItem.tanggal_selesai || activeItem.tanggal || activeItem.tanggal_kembali).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'})"></p>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-[10px] font-black text-gray-400 mb-3 uppercase">Catatan Pengajuan</h4>
                                <div class="bg-gray-50/50 border border-gray-100 p-5 rounded-2xl text-xs text-gray-600 leading-relaxed">
                                    "<span x-text="activeItem.keterangan || activeItem.tujuan || 'Tidak ada keterangan tambahan.'"></span>"
                                </div>
                            </div>

                            <template x-if="activeItem.status_approval === 'menunggu'">
                                <form :action="'/pimpinan/approval/' + activeItem.type + '/' + activeItem.id + '/tolak'" method="POST" class="mt-8 border-t border-gray-100 pt-8">
                                    @csrf @method('PATCH')
                                    <label class="block text-[10px] font-black text-gray-500  mb-3 italic">Alasan Penolakan</label>
                                    <textarea name="alasan_tolak" rows="2" placeholder="Tuliskan alasan penolakan..." required
                                              class="w-full px-4 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-red-400 focus:bg-white text-xs font-bold outline-none transition-all resize-none shadow-sm"></textarea>
                                    
                                    <div class="grid grid-cols-2 gap-3 mt-6">
                                        <button type="submit" class="bg-red-600 text-white font-black text-[10px]  italic py-4 rounded-2xl hover:bg-red-700 transition-all shadow-xl shadow-red-100">Tolak Pengajuan</button>
                                        <a :href="'/pimpinan/approval/' + activeItem.type + '/' + activeItem.id + '/setuju'" 
                                           onclick="event.preventDefault(); if(confirm('Setujui pengajuan ini?')) { document.getElementById('form-setuju-modal').action = this.href; document.getElementById('form-setuju-modal').submit(); }"
                                           class="bg-[#1E3A5F] text-white font-black text-[10px]  italic py-4 rounded-2xl hover:bg-emerald-600 transition-all shadow-xl shadow-blue-100 text-center">Setujui Pengajuan</a>
                                    </div>
                                </form>
                            </template>

                            <template x-if="activeItem.status_approval !== 'menunggu'">
                                <div class="mt-8 border-t border-gray-100 pt-8 text-center">
                                    <p class="text-[9px] font-black text-gray-300 mb-2 uppercase tracking-tight">Status Terakhir</p>
                                    <div class="inline-block px-8 py-3 rounded-full font-black text-sm uppercase" 
                                         :class="activeItem.status_approval === 'disetujui' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600'">
                                        <span x-text="activeItem.status_approval"></span>
                                    </div>
                                    <template x-if="activeItem.status_approval === 'disetujui' && activeItem.type === 'lembur'">
                                        <div class="mt-4">
                                            <a :href="'/lembur/' + activeItem.id + '/print'"
                                               target="_blank"
                                               class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-blue-50 hover:bg-blue-100 text-[#1E3A5F] text-xs font-black transition-all">
                                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                Cetak Slip Lembur (H+1)
                                            </a>
                                        </div>
                                    </template>
                                    <template x-if="activeItem.alasan_tolak">
                                        <p class="mt-6 text-[11px] font-bold text-red-400 bg-red-50 p-4 rounded-2xl">"<span x-text="activeItem.alasan_tolak"></span>"</p>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
            </div>
        </div>
    </div>
    </template>

    <form id="form-setuju-modal" method="POST" style="display: none;">
        @csrf @method('PATCH')
    </form>

</div>

@endsection

@push('scripts')
<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush
