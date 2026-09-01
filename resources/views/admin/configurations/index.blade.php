@extends('admin.sidebar')

@section('title', 'Komponen & Parameter Gaji')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>

<div x-data="configManager()" class="w-full space-y-6">
    
    {{-- HEADER HALAMAN --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm border border-gray-100">
                <i data-lucide="settings-2" class="w-6 h-6 text-[#1E3A5F]"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight uppercase">Komponen &amp; Parameter Gaji</h1>
                <p class="text-xs font-medium text-gray-400 mt-0.5">Kelola kamus komponen gaji, nilai standar default, dan konfigurasi sistem kepegawaian PT CBN</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="masterView = 'create'" 
                    class="flex items-center gap-2 px-5 py-3 rounded-xl bg-[#1E3A5F] text-white hover:bg-blue-900 transition-all font-black text-xs uppercase tracking-wider shadow-lg shadow-blue-900/10 active:scale-95">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Tambah Komponen Baru
            </button>
        </div>
    </div>

    {{-- FORM UTAMA TERPADU: PARAMETER GLOBAL & MASTER KOMPONEN GAJI --}}
    <form action="{{ route('admin.konfigurasi.update') }}" method="POST" x-data="{ editingRow: null }">
        @csrf

        {{-- RINGKASAN KONFIGURASI KEPEGAWAIAN & SISTEM (BARAT ATAS) --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm mb-6 transition-all"
             :class="editingRow === 'top' ? 'ring-4 ring-blue-500/5 border-blue-200' : ''">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center text-[#1E3A5F]">
                        <i data-lucide="sliders" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-[#1E3A5F] uppercase tracking-wider">Konfigurasi Kepegawaian &amp; Tanggal Gaji</h3>
                        <p class="text-[10px] text-gray-400 font-bold">Atur standar UMR, kuota cuti tahunan, dan batas pemrosesan penggajian</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" x-show="editingRow !== 'top'" @click="editingRow = 'top'; $nextTick(() => { if (window.lucide) window.lucide.createIcons(); });" 
                            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-50 text-blue-600 hover:bg-[#1E3A5F] hover:text-white transition-all font-black text-xs uppercase tracking-wider border border-blue-100">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                        Edit Parameter
                    </button>
                    <button type="button" x-show="editingRow === 'top'" @click="editingRow = null" 
                            class="px-4 py-2 rounded-xl bg-gray-100 text-gray-500 hover:bg-gray-200 transition-all font-black text-xs uppercase tracking-wider">
                        Batal
                    </button>
                    <button type="submit" x-show="editingRow === 'top'" 
                            class="flex items-center gap-2 px-5 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 transition-all font-black text-xs uppercase tracking-wider shadow-lg shadow-emerald-600/20">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-2 border-t border-gray-50">
                @php
                    $gajiGroup = $configs['gaji'] ?? collect();
                    $cutiGroup = $configs['cuti'] ?? collect();

                    $umrConfig = $gajiGroup->firstWhere('key', 'umr_tahun_ini');
                    $makanConfig = $gajiGroup->firstWhere('key', 'uang_makan_default');
                    $transportConfig = $gajiGroup->firstWhere('key', 'uang_transport_default');
                    $satpamConfig = $gajiGroup->firstWhere('key', 'extra_fooding_satpam');
                    $cutiConfig = $cutiGroup->firstWhere('key', 'kuota_cuti_tahunan');
                    $batasGajiConfig = $gajiGroup->firstWhere('key', 'batas_tanggal_gaji');
                    $kesConfig = $gajiGroup->firstWhere('key', 'persen_bpjs_kes');
                    $tkConfig = $gajiGroup->firstWhere('key', 'persen_bpjs_tk');
                    $tunjJamsostekConfig = $gajiGroup->firstWhere('key', 'persen_tunjangan_jamsostek') ?? $tkConfig;
                    $tunjAskesConfig     = $gajiGroup->firstWhere('key', 'persen_tunjangan_askes') ?? $kesConfig;
                    $potBpjsKesConfig    = $gajiGroup->firstWhere('key', 'persen_potongan_bpjs_kes') ?? $kesConfig;
                    $potBpjsTkConfig     = $gajiGroup->firstWhere('key', 'persen_potongan_bpjs_tk') ?? $tkConfig;
                @endphp

                {{-- UMR BERJALAN --}}
                <div class="space-y-1">
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">UMR Berjalan (Kontrak Umum)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-xs font-bold text-gray-400">Rp</span>
                        <input type="text" name="umr_tahun_ini" value="{{ $umrConfig?->value ?? '3500000' }}"
                               :readonly="editingRow !== 'top'"
                               :class="editingRow === 'top' ? 'bg-white border-blue-300 ring-4 ring-blue-500/10 text-[#1E3A5F]' : 'bg-gray-50/50 border-gray-100 text-gray-500 cursor-not-allowed'"
                               class="w-full pl-9 pr-3 py-2 border rounded-xl text-xs font-black transition-all">
                    </div>
                </div>

                {{-- KUOTA CUTI TAHUNAN --}}
                <div class="space-y-1">
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">Kuota Cuti Tahunan</label>
                    <div class="relative">
                        <input type="number" name="kuota_cuti_tahunan" value="{{ $cutiConfig?->value ?? '12' }}" min="0"
                               :readonly="editingRow !== 'top'"
                               :class="editingRow === 'top' ? 'bg-white border-blue-300 ring-4 ring-blue-500/10 text-[#1E3A5F]' : 'bg-gray-50/50 border-gray-100 text-gray-500 cursor-not-allowed'"
                               class="w-full pl-4 pr-12 py-2 border rounded-xl text-xs font-black transition-all">
                        <span class="absolute inset-y-0 right-3 flex items-center text-[9px] font-black text-gray-400 uppercase">Hari</span>
                    </div>
                </div>

                {{-- BATAS TANGGAL GAJI --}}
                <div class="space-y-1">
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">Batas Tanggal Gaji</label>
                    <div class="relative">
                        <input type="number" name="batas_tanggal_gaji" value="{{ $batasGajiConfig?->value ?? '25' }}" min="1" max="31"
                               :readonly="editingRow !== 'top'"
                               :class="editingRow === 'top' ? 'bg-white border-blue-300 ring-4 ring-blue-500/10 text-[#1E3A5F]' : 'bg-gray-50/50 border-gray-100 text-gray-500 cursor-not-allowed'"
                               class="w-full pl-4 pr-14 py-2 border rounded-xl text-xs font-black transition-all">
                        <span class="absolute inset-y-0 right-3 flex items-center text-[9px] font-black text-gray-400 uppercase">Tanggal</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL TERPADU: MASTER KOMPONEN GAJI & NILAI STANDAR DEFAULT --}}
        <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm space-y-6">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center text-[#1E3A5F]">
                        <i data-lucide="wallet" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-[#1E3A5F] uppercase tracking-widest">Daftar Komponen Gaji &amp; Formulasi Nilai Standar</h3>
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tight">Kamus Komponen Slip Gaji Resmi PT CBN</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <th class="py-4 px-4">ID</th>
                            <th class="py-4 px-4">Nama Komponen</th>
                            <th class="py-4 px-4 text-center">Tipe</th>
                            <th class="py-4 px-4">Nilai Standar / Formulasi Default System</th>
                            <th class="py-4 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-xs font-bold text-[#1E3A5F]">
                        @foreach($masterComponents as $comp)
                            <tr class="hover:bg-slate-50/80 transition-colors"
                                :class="editingRow === '{{ $comp->id }}' ? 'bg-blue-50/20' : ''">
                                <td class="py-4 px-4 text-gray-400 font-black text-[11px]">{{ $comp->id }}</td>
                                <td class="py-4 px-4 font-black text-sm">
                                    {{ $comp->nama_komponen }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($comp->tipe === 'pendapatan')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[9px] font-black bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-wider">
                                            Pendapatan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[9px] font-black bg-rose-50 text-rose-600 border border-rose-100 uppercase tracking-wider">
                                            Potongan
                                        </span>
                                    @endif
                                </td>
                                
                                {{-- KOLOM NILAI STANDAR SYSTEM --}}
                                <td class="py-4 px-4">
                                    @if($comp->id === 'MKG-00001')
                                        <span class="text-xs text-gray-500 font-semibold italic">Disesuaikan Tamatan / UMR Berjalan</span>
                                    @elseif($comp->id === 'MKG-00002')
                                        @php
                                            $panganConfig = $configs['gaji']?->firstWhere('key', 'tunjangan_pangan_kontrak_umum');
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-gray-400">Rp</span>
                                            <input type="text" name="tunjangan_pangan_kontrak_umum" value="{{ $panganConfig?->value ?? '805000' }}"
                                                   :readonly="editingRow !== 'MKG-00002'"
                                                   :class="editingRow === 'MKG-00002' ? 'bg-white border-emerald-500 ring-4 ring-emerald-500/10 text-[#1E3A5F]' : 'bg-gray-50/50 border-gray-100 text-gray-700 cursor-not-allowed'"
                                                   class="w-32 px-3 py-1.5 border rounded-xl text-xs font-black transition-all">
                                            <span class="text-[10px] text-gray-400 font-bold uppercase">/ Bulan (Kontrak Umum)</span>
                                        </div>
                                    @elseif($comp->id === 'MKG-00003')
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-gray-400">Rp</span>
                                            <input type="text" name="uang_makan_default" value="{{ $makanConfig?->value ?? '35000' }}"
                                                   :readonly="editingRow !== 'MKG-00003'"
                                                   :class="editingRow === 'MKG-00003' ? 'bg-white border-emerald-500 ring-4 ring-emerald-500/10 text-[#1E3A5F]' : 'bg-gray-50/50 border-gray-100 text-gray-700 cursor-not-allowed'"
                                                   class="w-32 px-3 py-1.5 border rounded-xl text-xs font-black transition-all">
                                            <span class="text-[10px] text-gray-400 font-bold uppercase">/ Hari (Tetap)</span>
                                        </div>
                                    @elseif($comp->id === 'MKG-00004')
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-gray-400">Rp</span>
                                            <input type="text" name="uang_transport_default" value="{{ $transportConfig?->value ?? '45000' }}"
                                                   :readonly="editingRow !== 'MKG-00004'"
                                                   :class="editingRow === 'MKG-00004' ? 'bg-white border-emerald-500 ring-4 ring-emerald-500/10 text-[#1E3A5F]' : 'bg-gray-50/50 border-gray-100 text-gray-700 cursor-not-allowed'"
                                                   class="w-32 px-3 py-1.5 border rounded-xl text-xs font-black transition-all">
                                            <span class="text-[10px] text-gray-400 font-bold uppercase">/ Hari (Tetap)</span>
                                        </div>
                                    @elseif($comp->id === 'MKG-00005')
                                        <div class="flex items-center gap-2">
                                            <input type="text" name="persen_tunjangan_jamsostek" value="{{ $tunjJamsostekConfig?->value ?? '5' }}"
                                                   :readonly="editingRow !== 'MKG-00005'"
                                                   :class="editingRow === 'MKG-00005' ? 'bg-white border-emerald-500 ring-4 ring-emerald-500/10 text-[#1E3A5F]' : 'bg-gray-50/50 border-gray-100 text-gray-700 cursor-not-allowed'"
                                                   class="w-20 px-3 py-1.5 border rounded-xl text-xs font-black transition-all text-center">
                                            <span class="text-xs font-bold text-gray-400">%</span>
                                            <span class="text-[10px] text-gray-400 font-medium ml-1">dari Gaji Pokok</span>
                                        </div>
                                    @elseif($comp->id === 'MKG-00006')
                                        <div class="flex items-center gap-2">
                                            <input type="text" name="persen_tunjangan_askes" value="{{ $tunjAskesConfig?->value ?? '3' }}"
                                                   :readonly="editingRow !== 'MKG-00006'"
                                                   :class="editingRow === 'MKG-00006' ? 'bg-white border-emerald-500 ring-4 ring-emerald-500/10 text-[#1E3A5F]' : 'bg-gray-50/50 border-gray-100 text-gray-700 cursor-not-allowed'"
                                                   class="w-20 px-3 py-1.5 border rounded-xl text-xs font-black transition-all text-center">
                                            <span class="text-xs font-bold text-gray-400">%</span>
                                            <span class="text-[10px] text-gray-400 font-medium ml-1">dari Gaji Pokok</span>
                                        </div>
                                    @elseif($comp->id === 'MKG-00009')
                                        <div class="flex items-center gap-2">
                                            <input type="text" name="persen_potongan_bpjs_kes" value="{{ $potBpjsKesConfig?->value ?? '3' }}"
                                                   :readonly="editingRow !== 'MKG-00009'"
                                                   :class="editingRow === 'MKG-00009' ? 'bg-white border-emerald-500 ring-4 ring-emerald-500/10 text-[#1E3A5F]' : 'bg-gray-50/50 border-gray-100 text-gray-700 cursor-not-allowed'"
                                                   class="w-20 px-3 py-1.5 border rounded-xl text-xs font-black transition-all text-center">
                                            <span class="text-xs font-bold text-gray-400">%</span>
                                            <span class="text-[10px] text-gray-400 font-medium ml-1">dari Gaji Pokok</span>
                                        </div>
                                    @elseif($comp->id === 'MKG-00010')
                                        <div class="flex items-center gap-2">
                                            <input type="text" name="persen_potongan_bpjs_tk" value="{{ $potBpjsTkConfig?->value ?? '5' }}"
                                                   :readonly="editingRow !== 'MKG-00010'"
                                                   :class="editingRow === 'MKG-00010' ? 'bg-white border-emerald-500 ring-4 ring-emerald-500/10 text-[#1E3A5F]' : 'bg-gray-50/50 border-gray-100 text-gray-700 cursor-not-allowed'"
                                                   class="w-20 px-3 py-1.5 border rounded-xl text-xs font-black transition-all text-center">
                                            <span class="text-xs font-bold text-gray-400">%</span>
                                            <span class="text-[10px] text-gray-400 font-medium ml-1">dari Gaji Pokok</span>
                                        </div>
                                    @elseif($comp->id === 'MKG-00013')
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-gray-400">Rp</span>
                                            <input type="text" name="extra_fooding_satpam" value="{{ $satpamConfig?->value ?? '300000' }}"
                                                   :readonly="editingRow !== 'MKG-00013'"
                                                   :class="editingRow === 'MKG-00013' ? 'bg-white border-emerald-500 ring-4 ring-emerald-500/10 text-[#1E3A5F]' : 'bg-gray-50/50 border-gray-100 text-gray-700 cursor-not-allowed'"
                                                   class="w-32 px-3 py-1.5 border rounded-xl text-xs font-black transition-all">
                                            <span class="text-[10px] text-gray-400 font-bold uppercase">/ Bulan (Satpam)</span>
                                        </div>
                                    @elseif($comp->id === 'MKG-00014')
                                        <span class="text-xs text-gray-500 font-semibold italic">Dipotong Berdasarkan Laporan Kasbon Mitra</span>
                                    @else
                                        <span class="text-xs text-gray-500 font-semibold italic">Sesuai Input Penggajian</span>
                                    @endif
                                </td>

                                {{-- AKSI EDIT / DELETE BARIS DENGAN TOMBOL SIMPAN LANGSUNG DI SAMPING PENSIL --}}
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- JIKA BARIS INI SEDANG DI-EDIT --}}
                                        <template x-if="editingRow === '{{ $comp->id }}'">
                                            <div class="flex items-center gap-2">
                                                {{-- ICON PENSIL AKTIF --}}
                                                <div class="p-2 rounded-xl bg-blue-100 text-blue-700 shadow-sm border border-blue-200" title="Sedang Mengedit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                                </div>

                                                {{-- TOMBOL SIMPAN HIJAU TEPAT DI SAMPING PENSIL --}}
                                                <button type="submit" 
                                                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider shadow-md shadow-emerald-600/20 transition-all active:scale-95">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                    Simpan
                                                </button>

                                                {{-- TOMBOL BATAL --}}
                                                <button type="button" @click="editingRow = null"
                                                        class="p-2 rounded-xl bg-gray-100 text-gray-500 hover:bg-gray-200 transition-all border border-gray-200"
                                                        title="Batal Edit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                </button>
                                            </div>
                                        </template>

                                        {{-- JIKA BARIS INI TIDAK SEDANG DI-EDIT --}}
                                        <template x-if="editingRow !== '{{ $comp->id }}'">
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" 
                                                        @click="if (['MKG-00002','MKG-00003','MKG-00004','MKG-00005','MKG-00006','MKG-00009','MKG-00010','MKG-00013'].includes('{{ $comp->id }}')) { editingRow = '{{ $comp->id }}'; } else if ('{{ $comp->id }}' === 'MKG-00001') { editingRow = 'top'; window.scrollTo({top: 0, behavior: 'smooth'}); } else { openEditMasterModal('{{ $comp->id }}', '{{ addslashes($comp->nama_komponen) }}', '{{ $comp->tipe }}'); }"
                                                        class="p-2 rounded-xl bg-slate-50 text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition-all border border-slate-100 shadow-sm"
                                                        title="Edit Nilai Komponen">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                                </button>
                                                @if(!in_array($comp->id, $protectedIds))
                                                    <button type="button" @click="confirmDeleteMaster('{{ $comp->id }}', '{{ addslashes($comp->nama_komponen) }}')"
                                                            class="p-2 rounded-xl bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all border border-slate-100 shadow-sm"
                                                            title="Hapus Komponen">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    {{-- POPUP MODAL: TAMBAH KOMPONEN GAJI --}}
    <template x-teleport="body">
        <div x-show="masterView === 'create'" 
             x-cloak 
             x-transition.fade
             class="fixed inset-0 z-[3000] flex items-center justify-center bg-[#1E3A5F]/40 backdrop-blur-md p-4">
            <div class="bg-white rounded-[2.5rem] shadow-2xl p-8 w-full max-w-lg border border-gray-100 relative" @click.away="masterView = 'list'">
                
                {{-- Header Modal --}}
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-50 rounded-2xl flex items-center justify-center text-[#1E3A5F]">
                            <i data-lucide="plus-circle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-[#1E3A5F] uppercase tracking-widest">Tambah Komponen Gaji Baru</h3>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tight">Formulir Komponen Penggajian</p>
                        </div>
                    </div>
                    <button type="button" @click="masterView = 'list'" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form @submit.prevent="submitNewComponent()" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-widest ml-1 block">Nama Komponen</label>
                        <input type="text" x-model="newComponentName" placeholder="Contoh: Tunjangan Kebersihan, Potongan Zakat..."
                               class="w-full px-5 py-3.5 border border-slate-200 bg-slate-50/50 rounded-2xl text-sm font-semibold text-gray-700 outline-none focus:bg-white focus:border-[#1E3A5F] focus:ring-4 focus:ring-[#1E3A5F]/5 transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-widest ml-1 block mb-2">Tipe Komponen</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all"
                                   :class="newComponentTipe === 'pendapatan' ? 'border-emerald-500 bg-emerald-50/10' : 'border-slate-100 bg-slate-50/50'">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500"><i data-lucide="plus-circle" class="w-4 h-4"></i></div>
                                    <span class="text-xs font-black text-slate-700">Pendapatan</span>
                                </div>
                                <input type="radio" name="new_tipe" value="pendapatan" x-model="newComponentTipe" class="hidden">
                            </label>
                            <label class="flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all"
                                   :class="newComponentTipe === 'potongan' ? 'border-rose-500 bg-rose-50/10' : 'border-slate-100 bg-slate-50/50'">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-xl bg-rose-50 flex items-center justify-center text-rose-500"><i data-lucide="minus-circle" class="w-4 h-4"></i></div>
                                    <span class="text-xs font-black text-slate-700">Potongan</span>
                                </div>
                                <input type="radio" name="new_tipe" value="potongan" x-model="newComponentTipe" class="hidden">
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-gray-100 justify-end">
                        <button type="button" @click="masterView = 'list'" class="px-6 py-3 bg-slate-100 text-slate-500 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Batal</button>
                        <button type="submit" class="px-8 py-3 bg-[#1E3A5F] text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-900/10 hover:bg-blue-900 transition-all active:scale-95" :disabled="isMasterSaving">
                            <span x-show="!isMasterSaving">Simpan Komponen</span>
                            <span x-show="isMasterSaving" class="flex items-center gap-1"><i data-lucide="loader" class="w-3.5 h-3.5 animate-spin"></i> Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- POPUP MODAL: EDIT KOMPONEN GAJI --}}
    <template x-teleport="body">
        <div x-show="masterView === 'edit'" 
             x-cloak 
             x-transition.fade
             class="fixed inset-0 z-[3000] flex items-center justify-center bg-[#1E3A5F]/40 backdrop-blur-md p-4">
            <div class="bg-white rounded-[2.5rem] shadow-2xl p-8 w-full max-w-lg border border-gray-100 relative" @click.away="masterView = 'list'">
                
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i data-lucide="edit-3" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-[#1E3A5F] uppercase tracking-widest">Edit Komponen Gaji</h3>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tight">Perbarui nama dan tipe komponen</p>
                        </div>
                    </div>
                    <button type="button" @click="masterView = 'list'" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form @submit.prevent="submitUpdateComponent()" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-widest ml-1 block">Nama Komponen</label>
                        <input type="text" x-model="editComponentName" 
                               placeholder="Contoh: Tunjangan Jabatan..."
                               class="w-full px-5 py-3.5 border border-slate-200 bg-white rounded-2xl text-sm font-bold text-[#1E3A5F] outline-none focus:border-[#1E3A5F] focus:ring-4 focus:ring-[#1E3A5F]/5 transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-widest ml-1 block mb-2">Tipe Komponen</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all"
                                   :class="editComponentTipe === 'pendapatan' ? 'border-emerald-500 bg-emerald-50/10' : 'border-slate-100 bg-slate-50/50'">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500"><i data-lucide="plus-circle" class="w-4 h-4"></i></div>
                                    <span class="text-xs font-black text-slate-700">Pendapatan</span>
                                </div>
                                <input type="radio" name="edit_tipe" value="pendapatan" x-model="editComponentTipe" class="hidden">
                            </label>
                            <label class="flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all"
                                   :class="editComponentTipe === 'potongan' ? 'border-rose-500 bg-rose-50/10' : 'border-slate-100 bg-slate-50/50'">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-xl bg-rose-50 flex items-center justify-center text-rose-500"><i data-lucide="minus-circle" class="w-4 h-4"></i></div>
                                    <span class="text-xs font-black text-slate-700">Potongan</span>
                                </div>
                                <input type="radio" name="edit_tipe" value="potongan" x-model="editComponentTipe" class="hidden">
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="masterView = 'list'" 
                                class="px-6 py-3 bg-gray-100 text-gray-500 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-200 transition-all">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-8 py-3 bg-[#1E3A5F] text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-900/10 hover:bg-blue-900 transition-all active:scale-95"
                                :disabled="isMasterSaving">
                            <span x-show="!isMasterSaving">Simpan Perubahan</span>
                            <span x-show="isMasterSaving" class="flex items-center gap-1"><i data-lucide="loader" class="w-3.5 h-3.5 animate-spin"></i> Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
    function configManager() {
        return {
            masterView: 'list',
            isEditingModal: false,
            isMasterSaving: false,
            
            // New component state
            newComponentName: '',
            newComponentTipe: 'pendapatan',
            
            // Edit component state
            editComponentId: '',
            editComponentName: '',
            editComponentTipe: 'pendapatan',

            openEditMasterModal(id, nama, tipe) {
                this.editComponentId = id;
                this.editComponentName = nama;
                this.editComponentTipe = tipe;
                this.isEditingModal = true;
                this.masterView = 'edit';
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            },

            async submitNewComponent() {
                if (!this.newComponentName.trim()) {
                    Toast.fire({ icon: 'warning', title: 'Nama komponen harus diisi' });
                    return;
                }

                this.isMasterSaving = true;
                try {
                    const res = await fetch("{{ route('admin.komponen-gaji.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            nama_komponen: this.newComponentName,
                            tipe: this.newComponentTipe
                        })
                    });

                    const data = await res.json();
                    if (res.ok && data.success) {
                        Toast.fire({ icon: 'success', title: data.message });
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        Toast.fire({ icon: 'error', title: data.message || 'Gagal menyimpan' });
                    }
                } catch (e) {
                    Toast.fire({ icon: 'error', title: 'Terjadi kesalahan sistem' });
                } finally {
                    this.isMasterSaving = false;
                }
            },

            async submitUpdateComponent() {
                if (!this.editComponentName.trim()) {
                    Toast.fire({ icon: 'warning', title: 'Nama komponen harus diisi' });
                    return;
                }

                this.isMasterSaving = true;
                try {
                    const res = await fetch(`/admin/komponen-gaji/${this.editComponentId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            nama_komponen: this.editComponentName,
                            tipe: this.editComponentTipe
                        })
                    });

                    const data = await res.json();
                    if (res.ok && data.success) {
                        Toast.fire({ icon: 'success', title: data.message });
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        Toast.fire({ icon: 'error', title: data.message || 'Gagal memperbarui' });
                    }
                } catch (e) {
                    Toast.fire({ icon: 'error', title: 'Terjadi kesalahan sistem' });
                } finally {
                    this.isMasterSaving = false;
                }
            },

            confirmDeleteMaster(id, nama) {
                Swal.fire({
                    title: 'Hapus Komponen?',
                    text: `Apakah Anda yakin ingin menghapus "${nama}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#EF4444',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    customClass: { popup: 'rounded-3xl' }
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const res = await fetch(`/admin/komponen-gaji/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            });

                            const data = await res.json();
                            if (res.ok && data.success) {
                                Toast.fire({ icon: 'success', title: data.message });
                                setTimeout(() => window.location.reload(), 800);
                            } else {
                                Toast.fire({ icon: 'error', title: data.message || 'Gagal menghapus' });
                            }
                        } catch (e) {
                            Toast.fire({ icon: 'error', title: 'Terjadi kesalahan sistem' });
                        }
                    }
                });
            }
        }
    }
</script>
@endpush
@endsection
