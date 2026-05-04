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

    <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 pb-6 border-b border-gray-100">
        <div>
            <h1 class="text-2xl font-black text-[#1E3A5F] ">Approval Pengajuan</h1>
            <div class="flex items-center gap-2 mt-2">
                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                <p class="text-gray-500 text-xs font-bold u">Pusat Validasi — PT CBN</p>
            </div>
        </div>
        <div class="flex gap-4 mt-6 md:mt-0">
            <div class="bg-blue-50 px-4 py-3 rounded-2xl border border-blue-100 text-center min-w-[120px]">
                <p class="text-[9px] font-black text-blue-400  mb-1">Total Menunggu</p>
                <p class="text-xl font-black text-blue-700">{{ array_sum($jumlahMenunggu) }}</p>
            </div>
        </div>
    </header>

    {{-- Tab navigasi Tipe Utama --}}
    <div class="flex gap-3 mb-10 overflow-x-auto pb-2 scrollbar-hide">
        @foreach ([
            ['perizinan', 'Perizinan', $jumlahMenunggu['perizinan'], 'calendar'],
            ['lembur',    'Lembur',    $jumlahMenunggu['lembur'], 'clock'],
            ['dinas_luar','Dinas Luar',$jumlahMenunggu['dinas_luar'], 'map-pin'],
        ] as [$val, $label, $jumlah, $icon])
            <a href="{{ route('pimpinan.approval.index', ['tipe' => $val, 'status' => request('status','menunggu'), 'jenis' => request('jenis','semua')]) }}"
               class="flex items-center gap-3 px-6 py-4 rounded-[2rem] font-black text-xs 
                      italic transition-all whitespace-nowrap group
                      {{ $tipe === $val
                           ? 'bg-[#1E3A5F] text-white shadow-xl shadow-blue-200/50 -translate-y-1'
                           : 'bg-white border border-gray-100 text-gray-400 hover:border-blue-200 hover:text-[#1E3A5F] hover:shadow-lg' }}">
                <i data-lucide="{{ $icon }}" class="w-4 h-4 {{ $tipe === $val ? 'text-blue-400' : 'text-gray-300 group-hover:text-blue-400' }}"></i>
                {{ $label }}
                @if ($jumlah > 0)
                    <span class="{{ $tipe === $val ? 'bg-white text-[#1E3A5F]' : 'bg-red-500 text-white' }}
                                   text-[9px] font-black px-2 py-0.5 rounded-full min-w-[20px] text-center">
                        {{ $jumlah }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Baris Filter --}}
    <div class="bg-gray-50/50 p-6 rounded-[2rem] border border-gray-100 mb-8 flex flex-col lg:flex-row gap-6 justify-between items-center">
        {{-- Filter Jenis Karyawan --}}
        <div class="flex flex-col gap-3 w-full lg:w-auto">
            <p class="text-[9px] font-black text-gray-400  ml-1 italic">Filter Jenis Karyawan</p>
            <div class="flex bg-white p-1 rounded-2xl border border-gray-100 shadow-sm">
                @foreach (['semua' => 'Semua', 'tetap' => 'Tetap', 'kontrak' => 'Kontrak'] as $val => $label)
                    <a href="{{ route('pimpinan.approval.index', array_merge(request()->query(), ['jenis' => $val])) }}"
                       class="px-6 py-2.5 rounded-xl text-[10px] font-black  italic transition-all
                              {{ request('jenis', 'semua') === $val
                                   ? 'bg-[#1E3A5F] text-white shadow-md'
                                   : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Filter Status --}}
        <div class="flex flex-col gap-3 w-full lg:w-auto">
            <p class="text-[9px] font-black text-gray-400  ml-1 italic text-right">Filter Status Approval</p>
            <div class="flex bg-white p-1 rounded-2xl border border-gray-100 shadow-sm">
                @foreach (['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'semua' => 'Semua'] as $val => $label)
                    <a href="{{ route('pimpinan.approval.index', array_merge(request()->query(), ['status' => $val])) }}"
                       class="px-6 py-2.5 rounded-xl text-[10px] font-black  italic transition-all
                              {{ request('status', 'menunggu') === $val
                                   ? 'bg-gray-800 text-white shadow-md'
                                   : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @php
        $activeItems = match($tipe){'perizinan'=>$perizinan, 'lembur'=>$lembur, 'dinas_luar'=>$dinasLuar};
        $titleTipe = ucfirst(str_replace('_', ' ', $tipe));
    @endphp

    {{-- TABEL UTAMA --}}
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-xl shadow-blue-50/20 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/10">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-6 bg-blue-600 rounded-full"></div>
                <h2 class="text-lg font-black text-[#1E3A5F]  italic">Daftar {{ $titleTipe }}</h2>
            </div>
            <div class="text-[10px] font-black text-gray-400  italic">
                Menampilkan: <span class="text-blue-600">{{ request('jenis','Semua') }}</span> &bull; Status: <span class="text-blue-600">{{ request('status','Menunggu') }}</span>
            </div>
        </div>
        
        @include('pimpinan.approval._table', ['items' => $activeItems, 'type' => $tipe])

        @if ($activeItems->hasPages())
            <div class="px-8 py-6 border-t border-gray-50 bg-gray-50/10">{{ $activeItems->links() }}</div>
        @endif
    </div>

    {{-- ── MODAL DETAIL PREMIUM ──────────────────────────────────────────── --}}
    <div x-show="detailModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-cloak>
        
        <div class="absolute inset-0 bg-[#1E3A5F]/60 backdrop-blur-md" @click="detailModal = false"></div>

        <div class="relative bg-white w-full max-w-2xl rounded-[3rem] shadow-2xl overflow-hidden border border-white/20">
            <template x-if="activeItem">
                <div class="flex flex-col md:flex-row h-full max-h-[90vh]">
                    <div class="w-full md:w-5/12 bg-gray-50 flex flex-col p-8 border-r border-gray-100">
                        <div class="mb-6">
                            <h4 class="text-[10px] font-black text-gray-400  mb-3">Lampiran Dokumen</h4>
                            <div class="aspect-[3/4] bg-white rounded-3xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center p-6 text-center group hover:border-blue-300 transition-all">
                                <template x-if="activeItem.file_bukti || activeItem.file_surat_tugas">
                                    <a :href="'/storage/' + (activeItem.file_bukti || activeItem.file_surat_tugas)" target="_blank" class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-[2rem] flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-sm">
                                            <i data-lucide="file-text" class="w-8 h-8"></i>
                                        </div>
                                        <p class="text-[10px] font-black text-[#1E3A5F] ">Buka File</p>
                                    </a>
                                </template>
                                <template x-if="!activeItem.file_bukti && !activeItem.file_surat_tugas">
                                    <div class="flex flex-col items-center opacity-40">
                                        <i data-lucide="file-x" class="w-12 h-12 mb-4 text-gray-300"></i>
                                        <p class="text-[10px] font-black  text-gray-400">Tidak Ada File</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="mt-auto bg-blue-50 p-5 rounded-3xl border border-blue-100">
                            <h4 class="text-[9px] font-black text-blue-700  mb-2">Policy Summary</h4>
                            <ul class="space-y-2 text-[9px] font-bold text-blue-600 leading-tight">
                                <template x-if="activeItem.type === 'perizinan'">
                                    <li><span x-text="activeItem.jenis_izin === 'cuti' ? '• Potong Uang Makan Rp 35k/hari' : (['izin_pribadi', 'sakit_no_surat'].includes(activeItem.jenis_izin) ? '• Memotong Kuota Tahunan' : '• Tidak Memotong Kuota')"></span></li>
                                </template>
                                <template x-if="activeItem.type === 'lembur'"><li>• Hitungan jam tambahan terencana</li></template>
                                <template x-if="activeItem.type === 'dinas_luar'"><li>• Perlu pelaporan SPJ setelah dinas</li></template>
                                <li>• Tercatat otomatis di absensi harian</li>
                            </ul>
                        </div>
                    </div>

                    <div class="w-full md:w-7/12 p-10 flex flex-col overflow-y-auto">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <div class="flex gap-2">
                                    <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-black " x-text="activeItem.type"></span>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black  shadow-sm" 
                                          :class="activeItem.karyawan.jenis_karyawan === 'tetap' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                                          x-text="activeItem.karyawan.jenis_karyawan"></span>
                                </div>
                                <h2 class="text-2xl font-black text-[#1E3A5F]  mt-3" x-text="activeItem.karyawan.nama"></h2>
                                <p class="text-[10px] font-bold text-gray-400 mt-2 " x-text="activeItem.karyawan.jabatan"></p>
                            </div>
                            <button @click="detailModal = false" class="text-gray-300 hover:text-red-500 transition-colors p-2">
                                <i data-lucide="x" class="w-6 h-6"></i>
                            </button>
                        </div>

                        <div class="space-y-6 flex-1">
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
                                <h4 class="text-[10px] font-black text-gray-400  mb-3 italic">Catatan Pengajuan</h4>
                                <div class="bg-gray-50/50 border border-gray-100 p-5 rounded-2xl italic text-xs text-gray-600 leading-relaxed shadow-inner">
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
                                    <p class="text-[9px] font-black text-gray-300  mb-2 tracking-widest">Status Terakhir</p>
                                    <div class="inline-block px-8 py-3 rounded-[2rem] font-black  italic text-sm" 
                                         :class="activeItem.status_approval === 'disetujui' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600'">
                                        <span x-text="activeItem.status_approval"></span>
                                    </div>
                                    <template x-if="activeItem.alasan_tolak">
                                        <p class="mt-6 text-[11px] font-bold text-red-400 italic bg-red-50 p-4 rounded-2xl">"<span x-text="activeItem.alasan_tolak"></span>"</p>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

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