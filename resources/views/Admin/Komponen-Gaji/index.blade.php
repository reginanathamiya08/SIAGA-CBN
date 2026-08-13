@extends('admin.sidebar')
@section('title', 'Komponen Gaji')

@section('content')

<style>
    [x-cloak] { display: none !important; }
</style>

<div x-data="gajiMassalManager()" class="w-full">
    
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#1E3A5F]">Komponen Gaji</h1>
            <p class="text-gray-400 mt-1 text-[10px] font-black uppercase tracking-tight opacity-70">Manajemen Referensi Penggajian & BPJS</p>
        </div>
        
        <div class="flex items-center gap-2">
            <button @click="openModal('choice')"
               class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white
                      font-black text-[10px] uppercase px-5 py-3 rounded-2xl transition-all shadow-lg shadow-emerald-500/20 active:scale-95">
                <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                AUTO-FILL GAJI
            </button>
        </div>
    </header>

    {{-- MODALS SECTION --}}
    {{-- 1. MODAL PILIHAN KATEGORI --}}
    <template x-teleport="body">
        <div x-show="showModal && currentStep === 'choice'" 
             x-cloak
             class="fixed inset-0 z-[60] flex items-center justify-center bg-[#1E3A5F]/50 p-4">
            <div class="bg-white rounded-[3rem] shadow-2xl p-10 w-full max-w-2xl border border-white/20 relative" @click.away="closeModal()">
                <button @click="closeModal()" class="absolute top-6 right-6 text-gray-400 hover:text-red-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                    </svg>
                </button>

                <div class="text-center mb-10">
                    <label class="text-[10px] font-black text-blue-600 uppercase tracking-tight mb-2 block">Pilih Kategori Karyawan</label>
                    <h3 class="text-2xl font-black text-[#1E3A5F] uppercase tracking-tighter">Auto-Fill Gaji Massal</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <button @click="currentStep = 'tetap'; $nextTick(() => { if (window.lucide) window.lucide.createIcons(); })" 
                            class="group bg-white p-8 rounded-[2.5rem] shadow-[0_10px_40px_rgba(30,58,95,0.04)] hover:-translate-y-2 hover:shadow-[0_20px_50px_rgba(30,58,95,0.1)] transition-all duration-500 text-center relative overflow-hidden border border-slate-50">
                        <div class="w-20 h-20 bg-[#1E3A5F] rounded-3xl flex items-center justify-center text-white mb-6 mx-auto shadow-xl group-hover:scale-110 transition-all duration-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 stroke-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/>
                                <path d="M22 10v6"/>
                                <path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>
                            </svg>
                        </div>
                        <h4 class="text-xl font-black text-[#1E3A5F] uppercase mb-2 group-hover:text-blue-600 transition-colors">Karyawan Tetap</h4>
                        <p class="text-[10px] text-slate-400 font-bold uppercase">Update Berdasarkan Tamatan</p>
                    </button>

                    <button @click="currentStep = 'kontrak'; $nextTick(() => { if (window.lucide) window.lucide.createIcons(); })" 
                            class="group bg-white p-8 rounded-[2.5rem] shadow-[0_10px_40px_rgba(16,185,129,0.04)] hover:-translate-y-2 hover:shadow-[0_20px_50px_rgba(16,185,129,0.1)] transition-all duration-500 text-center relative overflow-hidden border border-slate-50">
                        <div class="w-20 h-20 bg-emerald-500 rounded-3xl flex items-center justify-center text-white mb-6 mx-auto shadow-xl group-hover:scale-110 transition-all duration-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 stroke-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="14" x="2" y="7" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                        </div>
                        <h4 class="text-xl font-black text-[#1E3A5F] uppercase mb-2 group-hover:text-emerald-600 transition-colors">Karyawan Kontrak</h4>
                        <p class="text-[10px] text-slate-400 font-bold uppercase">Update Berdasarkan Jabatan/UMR</p>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- 2. MODAL FORM (TETAP & KONTRAK) --}}
    <template x-teleport="body">
        <div x-show="showModal && (currentStep === 'tetap' || currentStep === 'kontrak')" 
             x-cloak
             class="fixed inset-0 z-[70] flex items-center justify-center bg-[#1E3A5F]/50 p-4">
            <div class="bg-[#F8FAFC] rounded-[2.5rem] shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col relative border border-white/50" @click.away="closeModal()">
                
                {{-- Header --}}
                <div class="p-6 border-b border-slate-200 flex items-center justify-between sticky top-0 bg-white/90 backdrop-blur-md z-10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-white shadow-md"
                             :class="currentStep === 'tetap' ? 'bg-[#1E3A5F]' : 'bg-emerald-500'">
                            <i :data-lucide="currentStep === 'tetap' ? 'graduation-cap' : 'briefcase'" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-black text-[#1E3A5F] uppercase text-xs" x-text="currentStep === 'tetap' ? 'Gaji Tetap (Tamatan)' : 'Gaji Kontrak (Jabatan)'"></h3>
                                <span x-show="isEditingMode" class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded-md text-[8px] font-black uppercase">Mode Edit</span>
                            </div>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tight">Update Massal Komponen Gaji</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" x-show="isEditingMode" @click="isEditingMode = false" 
                                class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-[9px] font-black uppercase tracking-wider transition-all">
                            Batal Edit
                        </button>
                        <button @click="currentStep = 'choice'" class="px-3 py-2 bg-white text-slate-500 hover:bg-slate-100 rounded-xl text-[9px] font-black uppercase shadow-sm">KEMBALI</button>
                        <button @click="closeModal()" class="p-2 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-full transition-all"><i data-lucide="x" class="w-5 h-5"></i></button>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 pb-24 overflow-y-auto custom-scrollbar bg-gray-50/30 max-h-[70vh]">
                    
                    {{-- FORM TETAP (PENDIDIKAN) - 2 BARIS (3 COLS x 2 ROWS) --}}
                    <template x-if="currentStep === 'tetap'">
                        <form id="form-gaji-tetap" action="{{ route('admin.gaji-massal.update-tetap') }}" method="POST" @submit.prevent="submitForm($event)">
                            @csrf
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                @foreach($levels as $lv)
                                @php $val = $currentSalaries['tetap_'.$lv] ?? 0; @endphp
                                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 text-center transition-all"
                                     :class="isEditingMode ? 'border-blue-300 ring-2 ring-blue-500/5' : 'bg-slate-50/50'">
                                    <label class="text-[10px] font-black uppercase block mb-2"
                                           :class="isEditingMode ? 'text-blue-600' : 'text-slate-400'">Tamatan {{ $lv }}</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 font-bold text-xs" :class="isEditingMode ? 'text-blue-500' : 'text-gray-300'">Rp</span>
                                        <input type="text" name="gaji[{{ $lv }}]" 
                                               value="{{ $val > 0 ? number_format($val, 0, ',', '.') : '' }}" 
                                               :readonly="!isEditingMode"
                                               oninput="this.value = formatRupiah(this.value)"
                                               :class="isEditingMode ? 'bg-white border-blue-300 text-[#1E3A5F] focus:ring-4 focus:ring-blue-500/5' : 'bg-gray-50 border-gray-100 text-gray-500 cursor-not-allowed'"
                                               class="w-full pl-9 pr-3 py-2.5 border rounded-xl text-sm font-black outline-none transition-all">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </form>
                    </template>

                    {{-- FORM KONTRAK (JABATAN & UMR) --}}
                    <template x-if="currentStep === 'kontrak'">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                            {{-- HC Spesialis --}}
                            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 transition-all" :class="isEditingMode ? 'border-blue-200' : ''">
                                <form id="form-hc-spesialis" action="{{ route('admin.gaji-massal.update-spesialis') }}" method="POST">
                                    @csrf
                                    <h4 class="text-[10px] font-black text-blue-600 uppercase mb-3 tracking-widest opacity-70">KONTRAK: HC Spesialis</h4>
                                    <div class="space-y-3">
                                        @foreach($jabatanHCSpesialisKontrak as $js)
                                        @php $val = $currentSalaries['kontrak_'.$js] ?? 0; @endphp
                                        <div class="p-3 rounded-xl border border-slate-100 bg-white shadow-sm flex items-center justify-between gap-2">
                                            <label class="text-[9px] font-black text-slate-500 uppercase shrink-0">{{ $js }}</label>
                                            <div class="relative w-36">
                                                <span class="absolute left-2 top-1/2 -translate-y-1/2 font-bold text-[9px] text-blue-400">Rp</span>
                                                <input type="text" name="gaji[{{ $js }}]" 
                                                       value="{{ $val > 0 ? number_format($val, 0, ',', '.') : '' }}" 
                                                       :readonly="!isEditingMode"
                                                       oninput="this.value = formatRupiah(this.value)"
                                                       :class="isEditingMode ? 'bg-white border-blue-300 text-[#1E3A5F]' : 'bg-gray-50 border-gray-100 text-gray-500 cursor-not-allowed'"
                                                       class="w-full pl-6 pr-2 py-1 text-xs font-black outline-none border rounded-lg">
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </form>
                                <form id="form-hc-umr" action="{{ route('admin.gaji-massal.update-umr') }}" method="POST" class="mt-4 pt-4 border-t border-slate-100">
                                    @csrf
                                    <input type="hidden" name="target" value="hc_umr">
                                    <h4 class="text-[9px] font-black text-emerald-600 uppercase mb-2 tracking-widest text-center opacity-70">KONTRAK: HC UMR (Satpam)</h4>
                                    @php $umrVal = $currentSalaries['kontrak_Satpam'] ?? 0; @endphp
                                    <div class="bg-white p-4 rounded-xl border border-emerald-100 text-center shadow-sm">
                                        <div class="relative max-w-[160px] mx-auto">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 font-black text-xs text-emerald-500">Rp</span>
                                            <input type="text" name="nominal_umr" 
                                                   value="{{ $umrVal > 0 ? number_format($umrVal, 0, ',', '.') : '' }}" 
                                                   :readonly="!isEditingMode"
                                                   oninput="this.value = formatRupiah(this.value)"
                                                   :class="isEditingMode ? 'bg-white border-emerald-400 text-emerald-700' : 'bg-gray-50 border-gray-100 text-gray-500 cursor-not-allowed'"
                                                   class="w-full border-2 rounded-xl py-2 pl-8 pr-2 text-center text-sm font-black outline-none">
                                        </div>
                                    </div>
                                </form>
                            </div>

                            {{-- Umum --}}
                            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 transition-all" :class="isEditingMode ? 'border-amber-200' : ''">
                                <form id="form-umum-umr" action="{{ route('admin.gaji-massal.update-umr') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="target" value="umum_umr">
                                    <h4 class="text-[10px] font-black text-amber-600 uppercase mb-3 tracking-widest text-center opacity-70">KONTRAK: UMUM (CS ATM/Teknisi)</h4>
                                    @php $umumVal = $currentSalaries['kontrak_CS'] ?? 0; @endphp
                                    <div class="bg-white p-6 rounded-xl border border-amber-100 text-center shadow-sm">
                                        <div class="relative max-w-[170px] mx-auto">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 font-black text-xs text-amber-600">Rp</span>
                                            <input type="text" name="nominal_umr" 
                                                   value="{{ $umumVal > 0 ? number_format($umumVal, 0, ',', '.') : '' }}" 
                                                   :readonly="!isEditingMode"
                                                   oninput="this.value = formatRupiah(this.value)"
                                                   :class="isEditingMode ? 'bg-white border-amber-400 text-amber-700' : 'bg-gray-50 border-gray-100 text-gray-500 cursor-not-allowed'"
                                                   class="w-full border-2 rounded-xl py-2.5 pl-8 pr-2 text-center text-base font-black outline-none">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Footer Modal --}}
                <div class="absolute bottom-0 left-0 right-0 p-4 bg-white border-t border-slate-100 flex justify-center">
                    {{-- If !isEditingMode: show Edit hint --}}
                    <button type="button" x-show="!isEditingMode" @click="isEditingMode = true"
                            class="flex items-center gap-2 px-8 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-md shadow-amber-500/20 active:scale-95 transition-all">
                        <i data-lucide="edit-3" class="w-4 h-4"></i> 
                        KLIK UNTUK EDIT DATA
                    </button>

                    {{-- If isEditingMode: show Save button --}}
                    <button type="button" x-show="isEditingMode" 
                            @click="currentStep === 'tetap' ? document.getElementById('form-gaji-tetap').dispatchEvent(new Event('submit')) : submitGajiKontrak()"
                            class="flex items-center gap-2 px-12 py-3 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg active:scale-95 disabled:opacity-50 transition-all"
                            :class="currentStep === 'tetap' ? 'bg-[#1E3A5F] hover:bg-blue-900 shadow-blue-900/10' : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/10'" 
                            :disabled="isSaving">
                        <i data-lucide="save" class="w-4 h-4" :class="isSaving ? 'animate-spin' : ''"></i> 
                        <span x-show="!isSaving">SIMPAN PERUBAHAN <span x-text="currentStep === 'tetap' ? 'TETAP' : 'KONTRAK'"></span></span>
                        <span x-show="isSaving">MENYIMPAN...</span>
                    </button>
                </div>
            </div>
        </div>
    </template>



    {{-- Mini Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
        <div class="bg-white p-6 rounded-3xl shadow-sm flex items-center gap-5 border border-white/50 hover:-translate-y-1 transition-all duration-300 group">
            <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-500 shadow-sm shadow-blue-200/20 group-hover:scale-110 transition-transform"><i data-lucide="users" class="w-6 h-6"></i></div>
            <div><p class="text-[10px] font-black text-slate-300 uppercase tracking-tight mb-0.5">Total Karyawan</p><p class="text-xl font-black text-[#1E3A5F] tracking-tighter">{{ $stats['total'] }} <span class="text-[11px] text-slate-300 font-black ml-1 uppercase">Pax</span></p></div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm flex items-center gap-5 border border-white/50 hover:-translate-y-1 transition-all duration-300 group">
            <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-500 shadow-sm shadow-emerald-200/20 group-hover:scale-110 transition-transform"><i data-lucide="check-circle" class="w-6 h-6"></i></div>
            <div><p class="text-[10px] font-black text-slate-300 uppercase tracking-tight mb-0.5">Sudah Terisi</p><p class="text-xl font-black text-emerald-600 tracking-tighter">{{ $stats['sudah_diisi'] }}</p></div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm flex items-center gap-5 border border-white/50 hover:-translate-y-1 transition-all duration-300 group">
            <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500 shadow-sm shadow-amber-200/20 group-hover:scale-110 transition-transform"><i data-lucide="alert-triangle" class="w-6 h-6"></i></div>
            <div><p class="text-[10px] font-black text-slate-300 uppercase tracking-tight mb-0.5">Belum Terisi</p><p class="text-xl font-black text-amber-500 tracking-tighter">{{ $stats['belum_diisi'] }}</p></div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white/70 backdrop-blur-md rounded-3xl shadow-[0_8px_30px_rgba(0,0,0,0.03)] p-5 mb-8 border border-white/50">
        <form method="GET" action="{{ route('admin.komponen-gaji-karyawan.index') }}" id="filter-form" class="flex flex-wrap items-center gap-4 w-full">
            <div class="relative flex-1 min-w-[250px] group">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 group-focus-within:text-blue-500 transition-colors"></i>
                <input type="text" name="cari" value="{{ request('cari') }}" id="search-input" oninput="liveSearch(this)" placeholder="Cari nama atau jabatan..." class="w-full pl-11 pr-5 py-3 text-[13px] font-bold text-slate-600 bg-slate-50/50 rounded-2xl outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/5 border border-transparent focus:border-blue-100 transition-all">
            </div>
            <div class="w-fit">
                <select name="jenis" onchange="this.form.divisi.value=''; updateTable(this.form)" class="px-5 py-3 text-[12px] font-black text-slate-500 bg-slate-50/50 rounded-2xl outline-none focus:bg-white border border-transparent focus:border-blue-100 cursor-pointer transition-all uppercase tracking-wider">
                    <option value="">Semua Jenis</option><option value="JNS-00001" {{ request('jenis')==='tetap'?'selected':'' }}>Tetap</option><option value="JNS-00002" {{ request('jenis')==='kontrak'?'selected':'' }}>Kontrak</option>
                </select>
            </div>
            <div id="divisi-area" class="w-fit min-w-[180px]">
                <select name="divisi" onchange="updateTable(this.form)" class="w-full px-5 py-3 text-[12px] font-black text-slate-500 bg-slate-50/50 rounded-2xl outline-none focus:bg-white border border-transparent focus:border-blue-100 cursor-pointer transition-all uppercase tracking-wider">
                    <option value="">Semua Divisi</option>
                    @if(request('jenis') == '' || request('jenis') == 'tetap')<optgroup label="TETAP"><option value="keuangan" {{ request('divisi')==='keuangan'?'selected':'' }}>Keuangan</option><option value="koordinator_cs" {{ request('divisi')==='koordinator_cs'?'selected':'' }}>Koordinator CS</option><option value="adm_umum" {{ request('divisi')==='adm_umum'?'selected':'' }}>Adm & Umum</option></optgroup>@endif
                    @if(request('jenis') == '' || request('jenis') == 'kontrak')<optgroup label="KONTRAK (HC)"><option value="HC" {{ request('divisi')==='HC'?'selected':'' }}>HC - Semua</option><option value="Satpam" {{ request('divisi')==='Satpam'?'selected':'' }}>HC - Satpam</option><option value="Sopir" {{ request('divisi')==='Sopir'?'selected':'' }}>HC - Sopir</option></optgroup><optgroup label="KONTRAK (UMUM)"><option value="umum" {{ request('divisi')==='umum'?'selected':'' }}>Umum - Semua</option><option value="CS ATM" {{ request('divisi')==='CS ATM'?'selected':'' }}>Umum - CS ATM</option><option value="Teknisi" {{ request('divisi')==='Teknisi'?'selected':'' }}>Umum - Teknisi</option></optgroup>@endif
                </select>
            </div>
            <button type="submit" class="p-3 bg-[#1E3A5F] text-white rounded-2xl hover:bg-blue-900 transition-all active:scale-90 shadow-lg shadow-blue-900/10"><i data-lucide="filter" class="w-5 h-5"></i></button>
            <div id="reset-area" class="flex items-center">@if (request()->hasAny(['cari','jenis','divisi']))<a href="{{ route('admin.komponen-gaji-karyawan.index') }}" class="text-[10px] font-black text-red-500 uppercase tracking-tight hover:text-red-700 transition-colors ml-2">Reset</a>@endif</div>
        </form>
    </div>

    {{-- Main Table --}}
    <div id="table-container" class="bg-white rounded-[2rem] shadow-[0_10px_40px_rgba(0,0,0,0.03)] overflow-hidden border border-white/50">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100/50 uppercase">
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 tracking-tight" style="width: 35%">Info Karyawan</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 tracking-tight text-right" style="width: 20%">Gaji Pokok</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 tracking-tight text-right" style="width: 25%">Fasilitas</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 tracking-tight text-center" style="width: 20%">Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50/80">
                    @forelse ($karyawan as $kar)
                        @php $kg = $kar->komponenGaji @endphp
                        <tr class="group hover:bg-slate-50/50 transition-all duration-500">
                            <td class="px-8 py-4"><div class="flex items-center gap-4"><div class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-[11px] shrink-0 shadow-sm {{ $kar->isTetap() ? 'bg-blue-50 text-blue-500' : 'bg-orange-50 text-orange-500' }}">{{ strtoupper(substr($kar->nama, 0, 2)) }}</div><div><p class="text-[13px] font-black text-slate-700 group-hover:text-[#1E3A5F] transition-colors tracking-tight">{{ $kar->nama }}</p><p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter opacity-80">{{ $kar->jabatan }} <span class="mx-1 opacity-20">•</span> {{ $kar->labelDivisi() }} <span class="mx-1 opacity-20">•</span> {{ $kar->pendidikan ?? '-' }}</p></div></div></td>
                            <td class="px-8 py-4 text-right">@if ($kg && $kg->gaji_pokok > 0)<div class="text-[14px] font-black text-[#1E3A5F] tracking-tight"><span class="text-[10px] font-medium opacity-30 mr-1">Rp</span>{{ number_format($kg->gaji_pokok, 0, ',', '.') }}</div><span class="text-[8px] px-2 py-0.5 rounded-lg font-black uppercase tracking-tight {{ $kar->gaji_atas_umr ? 'bg-emerald-50 text-emerald-500 border border-emerald-100/50' : 'bg-blue-50 text-blue-500 border border-blue-100/50' }}">{{ $kar->gaji_atas_umr ? 'Expert' : 'UMR' }}</span>@else<span class="text-[10px] font-black text-amber-500 uppercase tracking-tight opacity-60">Belum Terdata</span>@endif</td>
                            <td class="px-8 py-4 text-right"><div class="text-[11px] font-bold text-slate-500 tracking-tight">Rp {{ number_format($kg->uang_makan ?? 35000, 0, ',', '.') }} <span class="text-[9px] font-black text-slate-300 uppercase ml-1 opacity-40">Makan</span></div><div class="text-[11px] font-bold text-slate-500 tracking-tight mt-0.5">Rp {{ number_format($kg->uang_transport ?? 45000, 0, ',', '.') }} <span class="text-[9px] font-black text-slate-300 uppercase ml-1 opacity-40">Trnsp</span></div></td>
                            <td class="px-8 py-4 text-center"><a href="{{ route('admin.komponen-gaji-karyawan.edit', $kar->id) }}" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl font-black text-[9px] uppercase tracking-tight transition-all {{ (!$kg || $kg->gaji_pokok == 0) ? 'bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white shadow-sm' : 'bg-slate-50 text-slate-400 hover:bg-[#1E3A5F] hover:text-white shadow-sm' }}"><i data-lucide="{{ (!$kg || $kg->gaji_pokok == 0) ? 'zap' : 'edit-3' }}" class="w-3.5 h-3.5"></i>{{ (!$kg || $kg->gaji_pokok == 0) ? 'Lengkapi' : 'Update' }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-24 text-center"><div class="flex flex-col items-center opacity-20"><i data-lucide="inbox" class="w-16 h-16 mb-4"></i><p class="text-sm font-black uppercase tracking-[0.3em]">Data Tidak Ditemukan</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($karyawan->hasPages())<div class="px-8 py-6 bg-slate-50/30">{{ $karyawan->links() }}</div>@endif
    </div>
</div>



<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #CBD5E1; }
</style>

@push('scripts')
<script>
function gajiMassalManager() {
    const urlParams = new URLSearchParams(window.location.search);
    const modeParam = urlParams.get('mode') || '{{ session("mode") }}';
    return {
        showModal: modeParam ? true : false,
        currentStep: modeParam ? (modeParam === 'tetap' ? 'tetap' : (modeParam === 'kontrak' ? 'kontrak' : 'choice')) : 'choice',
        isEditingMode: false,
        isSaving: false,

        openModal(step) { this.showModal = true; this.currentStep = step; this.isEditingMode = false; },
        closeModal() { 
            this.showModal = false; 
            this.isEditingMode = false; 
            if (window.location.search.includes('mode=')) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        },

        async submitForm(e) {
            if (this.isSaving) return;
            this.isSaving = true;
            const form = document.getElementById('form-gaji-tetap') || (e ? e.target : null);
            if (!form) { this.isSaving = false; return; }
            const formData = new FormData(form);
            const token = formData.get('_token') || document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            try {
                const response = await fetch(form.action, { 
                    method: 'POST', 
                    body: formData, 
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest', 
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    } 
                });
                if (response.ok) {
                    this.isEditingMode = false;
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Berhasil!', 
                        text: 'Gaji Berhasil Diperbarui!', 
                        confirmButtonColor: '#1E3A5F' 
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    const err = await response.json().catch(() => ({}));
                    Swal.fire({ icon: 'error', title: 'Gagal', text: err.message || 'Gagal menyimpan data.', confirmButtonColor: '#1E3A5F' });
                }
            } catch (error) {
                console.error('AJAX Error:', error);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memproses data.', confirmButtonColor: '#1E3A5F' });
            } finally { this.isSaving = false; }
        },

        async submitGajiKontrak() {
            if (this.isSaving) return;
            this.isSaving = true;
            try {
                const formHC = document.getElementById('form-hc-spesialis');
                const fdHC = new FormData(formHC);
                const formHCUmr = document.getElementById('form-hc-umr');
                const fdHCUmr = new FormData(formHCUmr);
                const formUmum = document.getElementById('form-umum-umr');
                const fdUmum = new FormData(formUmum);

                const getCsrf = (fd) => fd.get('_token') || document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const results = await Promise.all([
                    fetch(formHC.action, { method: 'POST', body: fdHC, headers: {'X-CSRF-TOKEN': getCsrf(fdHC), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'} }),
                    fetch(formHCUmr.action, { method: 'POST', body: fdHCUmr, headers: {'X-CSRF-TOKEN': getCsrf(fdHCUmr), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'} }),
                    fetch(formUmum.action, { method: 'POST', body: fdUmum, headers: {'X-CSRF-TOKEN': getCsrf(fdUmum), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'} })
                ]);
                if (results.every(r => r.ok)) {
                    this.isEditingMode = false;
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Berhasil!', 
                        text: 'Seluruh Gaji Kontrak Berhasil Disimpan!', 
                        confirmButtonColor: '#1E3A5F' 
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Sebagian Gagal', text: 'Beberapa data gagal disimpan.', confirmButtonColor: '#1E3A5F' });
                }
            } catch (error) {
                console.error('Batch Save Error:', error);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memproses data.', confirmButtonColor: '#1E3A5F' });
            } finally { this.isSaving = false; }
        }
    }
}

function updateTable(form) {
    try {
        const filterForm = form || document.getElementById('filter-form');
        let url;
        if (filterForm && filterForm.action) {
            url = new URL(filterForm.action);
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            url.search = params.toString();
        } else {
            url = new URL(window.location.href);
        }
        const container = document.getElementById('table-container');
        fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('table-container');
                if (newContent && container) container.innerHTML = newContent.innerHTML;
                if (window.lucide) window.lucide.createIcons();
            }).catch(e => console.error(e));
    } catch (e) {
        console.error('updateTable error:', e);
    }
}
function liveSearch(input) {
    clearTimeout(window.searchTimeout);
    window.searchTimeout = setTimeout(() => { updateTable(input.form); }, 400);
}

function formatRupiah(angka, prefix) {
    var number_string = angka.replace(/[^,\d]/g, '').toString(),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
}
</script>
@endpush

@endsection
