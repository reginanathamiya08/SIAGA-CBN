@extends('admin.sidebar')
@section('title', 'Komponen Gaji')

@section('content')

{{-- Header Ramping --}}
<header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] ">Komponen Gaji</h1>
        <p class="text-gray-500 mt-1 text-sm">Manajemen Penggajian dan BPJS</p>
    </div>
    
    <div class="flex items-center gap-2" x-data="{ 
        showChoice: false, 
        showTetap: false, 
        showKontrak: false 
    }">
        <button @click="showChoice = true"
           class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white
                  font-black text-[10px] uppercase tracking-wider px-4 py-2.5 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-95">
            <i data-lucide="zap" class="w-3.5 h-3.5"></i>
            Auto-Fill Gaji
        </button>

        <button onclick="document.getElementById('modal-bpjs').classList.remove('hidden')"
                class="flex items-center gap-2 bg-[#1E3A5F] hover:bg-blue-700 text-white
                       font-black text-[10px] uppercase tracking-wider px-4 py-2.5 rounded-xl transition-all shadow-lg shadow-blue-900/20 active:scale-95">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
            Update BPJS
        </button>

        {{-- 1. MODAL PILIHAN KATEGORI --}}
        <div x-show="showChoice" class="fixed inset-0 z-[60] flex items-center justify-center bg-[#1E3A5F]/40 backdrop-blur-md p-4" x-transition.fade>
            <div class="bg-white rounded-[3rem] shadow-2xl p-10 w-full max-w-2xl border border-white/20 relative" @click.away="showChoice = false">
                <button @click="showChoice = false" class="absolute top-6 right-6 text-gray-400 hover:text-red-500 transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>

                <div class="text-center mb-10">
                    <label class="text-[10px] font-black text-blue-600 uppercase tracking-[0.3em] mb-2 block">Pilih Kategori</label>
                    <h3 class="text-2xl font-black text-[#1E3A5F] uppercase italic">Auto-Fill Gaji Massal</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <button @click="showChoice = false; showTetap = true" class="group bg-slate-50 p-8 rounded-[2.5rem] border border-slate-100 hover:bg-white hover:shadow-xl hover:border-blue-500/20 transition-all text-center relative overflow-hidden active:scale-95">
                        <div class="w-16 h-16 bg-[#1E3A5F] rounded-2xl flex items-center justify-center text-white mb-6 mx-auto shadow-lg group-hover:rotate-6 transition-transform">
                            <i data-lucide="user-check" class="w-8 h-8"></i>
                        </div>
                        <h4 class="text-lg font-black text-[#1E3A5F] uppercase italic mb-2">Karyawan Tetap</h4>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-relaxed">Update Gaji Keuangan, Koordinator, & Umum</p>
                    </button>

                    <button @click="showChoice = false; showKontrak = true" class="group bg-slate-50 p-8 rounded-[2.5rem] border border-slate-100 hover:bg-white hover:shadow-xl hover:border-emerald-500/20 transition-all text-center relative overflow-hidden active:scale-95">
                        <div class="w-16 h-16 bg-emerald-500 rounded-2xl flex items-center justify-center text-white mb-6 mx-auto shadow-lg group-hover:-rotate-6 transition-transform">
                            <i data-lucide="file-text" class="w-8 h-8"></i>
                        </div>
                        <h4 class="text-lg font-black text-[#1E3A5F] uppercase italic mb-2">Karyawan Kontrak</h4>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-relaxed">Update Gaji UMR & HC Spesialis</p>
                    </button>
                </div>
            </div>
        </div>

        {{-- 2. MODAL FORM GAJI TETAP --}}
        <div x-show="showTetap" class="fixed inset-0 z-[70] flex items-center justify-center bg-[#1E3A5F]/60 backdrop-blur-xl p-4 overflow-y-auto" x-transition.fade>
            <div class="bg-white rounded-[3.5rem] shadow-2xl w-full max-w-4xl my-auto relative" @click.away="showTetap = false">
                <div class="p-8 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white/80 backdrop-blur-md rounded-t-[3.5rem] z-10">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-[#1E3A5F] rounded-xl flex items-center justify-center text-white">
                            <i data-lucide="user-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-[#1E3A5F] uppercase text-sm">Gaji Karyawan Tetap</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Update Massal Berdasarkan Jabatan</p>
                        </div>
                    </div>
                    <button @click="showTetap = false" class="p-2 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-full transition-all">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <div class="p-10 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <form action="{{ route('admin.gaji-massal.update-spesialis') }}" method="POST">
                        @csrf
                        <div class="space-y-12">
                            @foreach($jabatanTetapByDivisi as $divisi => $jabatans)
                            <div>
                                <div class="flex items-center gap-4 mb-6">
                                    <h3 class="text-[10px] font-black text-blue-600 bg-blue-50 px-4 py-2 rounded-full uppercase tracking-[0.2em]">{{ str_replace(['_', '-'], ' ', strtoupper($divisi)) }}</h3>
                                    <div class="h-[1px] flex-1 bg-gray-100"></div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($jabatans as $js)
                                    @php $val = $currentSalaries[$js] ?? 0; @endphp
                                    <div class="bg-slate-50 p-5 rounded-[2rem] border border-transparent hover:border-blue-100 hover:bg-white transition-all shadow-sm group/card"
                                         x-data="{ editing: {{ $val > 0 ? 'false' : 'true' }} }">
                                        <div class="text-center mb-3 relative">
                                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ $js }}</label>
                                            @if($val > 0)
                                            <button type="button" @click="editing = !editing" class="absolute -top-1 -right-1 p-1.5 text-slate-300 hover:text-blue-500">
                                                <i data-lucide="edit-3" class="w-3 h-3"></i>
                                            </button>
                                            @endif
                                        </div>
                                        <div x-show="!editing" @click="editing = true" class="text-center cursor-pointer">
                                            <p class="text-lg font-black text-[#1E3A5F] tracking-tight">
                                                <span class="text-[10px] opacity-30 mr-0.5">Rp</span>{{ number_format($val, 0, ',', '.') }}
                                            </p>
                                        </div>
                                        <div x-show="editing" class="relative">
                                            <input type="number" name="gaji[{{ $js }}]" value="{{ $val > 0 ? $val : '' }}" placeholder="0"
                                                   class="w-full py-1 bg-transparent text-center text-lg font-black text-[#1E3A5F] outline-none border-b-2 border-blue-100 focus:border-blue-500 transition-all">
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-12 flex items-center justify-center gap-4">
                            <button type="submit" class="bg-[#1E3A5F] text-white px-10 py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl hover:bg-blue-800 transition-all active:scale-95">Update Gaji Tetap</button>
                            <button type="button" @click="showTetap = false" class="px-8 py-4 bg-gray-100 text-gray-400 rounded-2xl font-black text-xs uppercase tracking-widest">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 3. MODAL FORM GAJI KONTRAK --}}
        <div x-show="showKontrak" class="fixed inset-0 z-[70] flex items-center justify-center bg-[#1E3A5F]/60 backdrop-blur-xl p-4 overflow-y-auto" x-transition.fade>
            <div class="bg-white rounded-[3.5rem] shadow-2xl w-full max-w-5xl my-auto relative" @click.away="showKontrak = false">
                <div class="p-8 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white/80 backdrop-blur-md rounded-t-[3.5rem] z-10">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center text-white">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-[#1E3A5F] uppercase text-sm">Gaji Karyawan Kontrak</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Update Massal (UMR & Above UMR)</p>
                        </div>
                    </div>
                    <button @click="showKontrak = false" class="p-2 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-full transition-all">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <div class="p-10 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                        {{-- Divisi HC --}}
                        <div class="bg-slate-50/50 rounded-[2.5rem] border border-slate-100 p-8">
                            <form action="{{ route('admin.gaji-massal.update-spesialis') }}" method="POST">
                                @csrf
                                <div class="flex items-center justify-between mb-8">
                                    <h4 class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em]">Divisi HC (Above UMR)</h4>
                                    <div class="h-[1px] flex-1 ml-4 bg-blue-100/50"></div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                                    @foreach($jabatanHCSpesialisKontrak as $js)
                                    @php $val = $currentSalaries[$js] ?? 0; @endphp
                                    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-transparent hover:border-blue-100 transition-all" 
                                         x-data="{ editing: {{ $val > 0 ? 'false' : 'true' }} }">
                                        <div class="text-center mb-4 relative">
                                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ $js }}</label>
                                            @if($val > 0)
                                            <button type="button" @click="editing = !editing" class="absolute -top-1 -right-1 p-1.5 text-slate-300 hover:text-blue-500">
                                                <i data-lucide="edit-3" class="w-3 h-3"></i>
                                            </button>
                                            @endif
                                        </div>
                                        <div x-show="!editing" @click="editing = true" class="text-center cursor-pointer">
                                            <p class="text-lg font-black text-[#1E3A5F] tracking-tight">
                                                <span class="text-[10px] opacity-30 mr-0.5">Rp</span>{{ number_format($val, 0, ',', '.') }}
                                            </p>
                                        </div>
                                        <div x-show="editing">
                                            <input type="number" name="gaji[{{ $js }}]" value="{{ $val > 0 ? $val : '' }}" placeholder="0" class="w-full py-1 bg-transparent text-center text-lg font-black text-[#1E3A5F] outline-none border-b-2 border-blue-100 focus:border-blue-500 transition-all">
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <button type="submit" class="w-full py-5 bg-[#1E3A5F] text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-xl hover:bg-blue-800 transition-all active:scale-95">Simpan HC Spesialis</button>
                            </form>

                            <form action="{{ route('admin.gaji-massal.update-umr') }}" method="POST" class="pt-10 mt-10 border-t border-slate-200">
                                @csrf
                                <input type="hidden" name="target" value="hc_umr">
                                <h4 class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em] mb-6 text-center">HC Standar (UMR)</h4>
                                @php $umrVal = $currentSalaries['Satpam'] ?? 0; @endphp
                                <div class="bg-white p-8 rounded-[2.5rem] border border-emerald-100 text-center shadow-sm" x-data="{ editing: {{ $umrVal > 0 ? 'false' : 'true' }} }">
                                    <div x-show="!editing" class="flex flex-col items-center gap-3">
                                        <span class="text-3xl font-black text-emerald-600 italic tracking-tighter">Rp {{ number_format($umrVal, 0, ',', '.') }}</span>
                                        <button type="button" @click="editing = true" class="text-[10px] font-black text-blue-500 uppercase tracking-widest hover:scale-105 transition-transform">Ubah UMR HC</button>
                                    </div>
                                    <div x-show="editing" class="space-y-4">
                                        <input type="number" name="nominal_umr" value="{{ $umrVal > 0 ? $umrVal : '' }}" required placeholder="UMR HC" class="w-full py-3 bg-slate-50 border-none rounded-2xl text-xl font-black text-[#1E3A5F] text-center outline-none focus:ring-2 focus:ring-emerald-500/20">
                                        <button type="submit" class="w-full py-4 bg-emerald-500 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg hover:bg-emerald-600 transition-all">Update UMR HC</button>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 justify-center mt-6">
                                        @foreach($jabatanHCUmrKontrak as $j)
                                        <span class="px-2.5 py-1 bg-slate-50 text-[8px] font-black text-emerald-600 rounded-lg uppercase border border-emerald-50">{{ $j }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- Divisi Umum --}}
                        <div class="bg-slate-50/50 rounded-[2.5rem] border border-slate-100 p-8 h-full">
                            <form action="{{ route('admin.gaji-massal.update-umr') }}" method="POST">
                                @csrf
                                <input type="hidden" name="target" value="umum_umr">
                                <h4 class="text-[10px] font-black text-amber-600 uppercase tracking-[0.2em] mb-8 text-center">Divisi Umum (CBN)</h4>
                                @php $umumVal = $currentSalaries['CS'] ?? 0; @endphp
                                <div class="bg-white p-10 rounded-[3rem] border border-amber-100 text-center shadow-sm" x-data="{ editing: {{ $umumVal > 0 ? 'false' : 'true' }} }">
                                    <div x-show="!editing" class="flex flex-col items-center gap-4">
                                        <span class="text-4xl font-black text-amber-600 italic tracking-tighter">Rp {{ number_format($umumVal, 0, ',', '.') }}</span>
                                        <button type="button" @click="editing = true" class="text-[10px] font-black text-blue-500 uppercase tracking-widest hover:scale-105 transition-transform">Ubah UMR Umum</button>
                                    </div>
                                    <div x-show="editing" class="space-y-4">
                                        <input type="number" name="nominal_umr" value="{{ $umumVal > 0 ? $umumVal : '' }}" required placeholder="0" class="w-full py-4 bg-slate-50 border-none rounded-2xl text-2xl font-black text-[#1E3A5F] text-center outline-none focus:ring-2 focus:ring-amber-500/20">
                                        <button type="submit" class="w-full py-5 bg-amber-500 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg hover:bg-amber-600 transition-all">Update UMR Umum</button>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 justify-center mt-10">
                                        @foreach($jabatanUmumKontrak as $j)
                                        <span class="px-3 py-1 bg-slate-50 text-[8px] font-black text-amber-600 rounded-lg uppercase border border-amber-50">{{ $j }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </form>
                            <div class="mt-10 p-6 bg-white rounded-3xl border border-slate-100">
                                <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest text-center">Catatan</p>
                                <p class="text-[10px] text-slate-500 text-center mt-2 leading-relaxed">Update UMR akan langsung merubah gaji pokok seluruh karyawan pada jabatan yang tertera di atas.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- Mini Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
            <i data-lucide="users" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Total</p>
            <p class="text-lg font-black text-[#1E3A5F]">{{ $stats['total'] }} <span class="text-[10px] text-gray-300">Pax</span></p>
        </div>
    </div>
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
        <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Sudah Diisi</p>
            <p class="text-lg font-black text-emerald-600">{{ $stats['sudah_diisi'] }}</p>
        </div>
    </div>
    <div class="bg-white p-4 rounded-2xl border border-amber-100 shadow-sm flex items-center gap-4 {{ $stats['belum_diisi'] > 0 ? 'bg-amber-50/50' : '' }}">
        <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Belum Diisi</p>
            <p class="text-lg font-black text-amber-600">{{ $stats['belum_diisi'] }}</p>
        </div>
    </div>

</div>

{{-- Modern Filter Bar --}}
<div id="filter-container" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('admin.komponen-gaji.index') }}" id="filter-form" class="flex flex-wrap items-center gap-2 w-full">
        <div class="relative flex-1 min-w-[200px]">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input type="text" name="cari" value="{{ request('cari') }}" id="search-input"
                   oninput="liveSearch(this)"
                   placeholder="Cari nama atau jabatan..."
                   class="w-full pl-10 pr-4 py-2 text-[12px] font-semibold text-gray-700 bg-gray-50 border-none rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
        </div>
        
        <select name="jenis" onchange="this.form.divisi.value=''; updateTable(this.form)"
                class="px-4 py-2 text-[12px] font-bold text-gray-600 bg-gray-50 border-none rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20">
            <option value="">Semua Jenis</option>
            <option value="tetap"   {{ request('jenis')==='tetap'   ?'selected':'' }}>Karyawan Tetap</option>
            <option value="kontrak" {{ request('jenis')==='kontrak' ?'selected':'' }}>Karyawan Kontrak</option>
        </select>

        <select name="divisi" onchange="updateTable(this.form)"
                class="px-4 py-2 text-[12px] font-bold text-gray-600 bg-gray-50 border-none rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20">
            <option value="">Semua Divisi/Bagian</option>
            @if(request('jenis') == '' || request('jenis') == 'tetap')
                <optgroup label="Karyawan Tetap">
                    <option value="keuangan"       {{ request('divisi')==='keuangan'       ?'selected':'' }}>Keuangan</option>
                    <option value="koordinator_cs" {{ request('divisi')==='koordinator_cs' ?'selected':'' }}>Koordinator CS</option>
                    <option value="adm_umum"       {{ request('divisi')==='adm_umum'       ?'selected':'' }}>Adm & Umum</option>
                </optgroup>
            @endif
            @if(request('jenis') == '' || request('jenis') == 'kontrak')
                <optgroup label="Kontrak (HC)">
                    <option value="HC"       {{ request('divisi')==='HC'      ?'selected':'' }}>HC - Semua</option>
                    <option value="Satpam"   {{ request('divisi')==='Satpam'  ?'selected':'' }}>HC - Satpam</option>
                    <option value="Sopir"    {{ request('divisi')==='Sopir'   ?'selected':'' }}>HC - Sopir</option>
                </optgroup>
                <optgroup label="Kontrak (Umum)">
                    <option value="umum"     {{ request('divisi')==='umum'    ?'selected':'' }}>Umum - Semua</option>
                    <option value="CS ATM"   {{ request('divisi')==='CS ATM'  ?'selected':'' }}>Umum - CS ATM</option>
                    <option value="Teknisi"  {{ request('divisi')==='Teknisi' ?'selected':'' }}>Umum - Teknisi</option>
                </optgroup>
            @endif
        </select>

        <button type="submit" class="p-2 bg-[#1E3A5F] text-white rounded-xl hover:bg-blue-700 transition-all active:scale-90">
            <i data-lucide="filter" class="w-4 h-4"></i>
        </button>

        @if (request()->hasAny(['cari','jenis','divisi']))
            <a href="{{ route('admin.komponen-gaji.index') }}" class="text-[10px] font-black text-red-400 uppercase tracking-widest ml-auto hover:text-red-600 transition-colors">Reset Filter</a>
        @endif
    </form>
</div>

{{-- Modern Compact Table --}}
<div id="table-container" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="px-5 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest" style="width: 30%">Karyawan</th>
                    <th class="px-5 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right" style="width: 15%">Gaji Pokok</th>
                    <th class="px-5 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right" style="width: 20%">Makan & Transport</th>
                    <th class="px-5 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center" style="width: 15%">BPJS (%)</th>
                    <th class="px-5 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center" style="width: 20%">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($karyawan as $kar)
                    @php $kg = $kar->komponenGaji @endphp
                    <tr class="group hover:bg-blue-50/30 transition-all duration-300">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-black text-[10px] shrink-0 {{ $kar->isTetap() ? 'bg-blue-100 text-blue-600' : 'bg-orange-100 text-orange-600' }}">
                                    {{ strtoupper(substr($kar->nama, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-[12px] font-bold text-[#1E3A5F] group-hover:text-blue-600 transition-colors">{{ $kar->nama }}</p>
                                    <p class="text-[9px] text-gray-400 font-medium uppercase tracking-tighter">{{ $kar->jabatan }} <span class="mx-1 opacity-30">•</span> {{ $kar->labelDivisi() }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @if ($kg && $kg->gaji_pokok > 0)
                                <div class="text-[13px] font-black text-gray-700">Rp {{ number_format($kg->gaji_pokok, 0, ',', '.') }}</div>
                                <span class="text-[8px] px-1.5 py-0.5 rounded-full font-black uppercase {{ $kar->gaji_atas_umr ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600' }}">
                                    {{ $kar->gaji_atas_umr ? 'Expert' : 'UMR' }}
                                </span>
                            @else
                                <span class="text-[9px] font-black text-amber-500 uppercase tracking-widest italic">Belum Diisi</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="text-[11px] font-bold text-gray-600">Rp {{ number_format($kg->uang_makan ?? 35000, 0, ',', '.') }} <span class="text-[9px] opacity-40">Mkn</span></div>
                            <div class="text-[11px] font-bold text-gray-600">Rp {{ number_format($kg->uang_transport ?? 45000, 0, ',', '.') }} <span class="text-[9px] opacity-40">Trp</span></div>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <div class="inline-flex gap-1">
                                <span class="px-2 py-1 bg-gray-50 rounded-md text-[10px] font-black text-blue-600 border border-blue-50">{{ $kg->persen_bpjs_kes ?? 9.24 }}%</span>
                                <span class="px-2 py-1 bg-gray-50 rounded-md text-[10px] font-black text-teal-600 border border-teal-50">{{ $kg->persen_bpjs_tk ?? 5.00 }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <a href="{{ route('admin.komponen-gaji.edit', $kar->id) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-black text-[9px] uppercase italic transition-all
                                      {{ (!$kg || $kg->gaji_pokok == 0) ? 'bg-amber-100 text-amber-600 hover:bg-amber-500 hover:text-white' : 'bg-gray-100 text-gray-400 hover:bg-[#1E3A5F] hover:text-white' }}">
                                <i data-lucide="{{ (!$kg || $kg->gaji_pokok == 0) ? 'alert-circle' : 'edit-3' }}" class="w-3 h-3"></i>
                                {{ (!$kg || $kg->gaji_pokok == 0) ? 'Isi' : 'Edit' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-20 text-center text-gray-300 font-bold italic">Data tidak ditemukan...</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if ($karyawan->hasPages())
        <div class="px-6 py-4 bg-gray-50/50">{{ $karyawan->links() }}</div>
    @endif
</div>

{{-- Modal BPJS Ramping --}}
<div id="modal-bpjs" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-[#1E3A5F]/20 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md border border-white/20 animate-in fade-in zoom-in duration-300">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="font-black text-[#1E3A5F] uppercase text-sm tracking-tight">Update BPJS Massal</h3>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Terapkan ke seluruh karyawan</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.komponen-gaji.bulk-bpjs') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2 text-center">Kesehatan (%)</label>
                    <input type="number" name="persen_bpjs_kes" value="9.24" step="0.01" class="w-full bg-transparent text-center text-xl font-black text-[#1E3A5F] outline-none">
                </div>
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2 text-center">Ketenaga (%)</label>
                    <input type="number" name="persen_bpjs_tk" value="5.00" step="0.01" class="w-full bg-transparent text-center text-xl font-black text-[#1E3A5F] outline-none">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-[#1E3A5F] text-white font-black text-[10px] uppercase tracking-widest py-4 rounded-2xl shadow-lg shadow-blue-900/20">Terapkan</button>
                <button type="button" onclick="document.getElementById('modal-bpjs').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-400 font-black text-[10px] uppercase tracking-widest py-4 rounded-2xl">Batal</button>
            </div>
        </form>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
</style>

@push('scripts')
<script>
let searchTimeout = null;

function updateTable(form) {
    const url = new URL(form.action || window.location.href);
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    url.search = params.toString();

    const container = document.getElementById('table-container');
    const filterContainer = document.getElementById('filter-container');
    
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update Tabel
            const newContent = doc.getElementById('table-container');
            if (newContent && container) {
                container.innerHTML = newContent.innerHTML;
            }

            // Update Filter (untuk sinkronisasi dropdown)
            const newFilter = doc.getElementById('filter-container');
            if (newFilter && filterContainer) {
                // Simpan nilai input cari agar tidak hilang saat mengetik
                const currentSearch = document.getElementById('search-input').value;
                filterContainer.innerHTML = newFilter.innerHTML;
                document.getElementById('search-input').value = currentSearch;
                document.getElementById('search-input').focus();
                // Pindahkan kursor ke akhir
                const val = document.getElementById('search-input').value;
                document.getElementById('search-input').value = '';
                document.getElementById('search-input').value = val;
            }

            if (window.lucide) window.lucide.createIcons();
            window.history.pushState({}, '', url);
        });
}

function liveSearch(input) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        updateTable(input.form);
    }, 400);
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    if (searchInput && searchInput.value !== '') {
        searchInput.focus();
        const val = searchInput.value;
        searchInput.value = '';
        searchInput.value = val;
    }

    // Intercept pagination clicks (only links inside nav)
    document.addEventListener('click', e => {
        const link = e.target.closest('#table-container nav a');
        if (link && link.href) {
            e.preventDefault();
            const container = document.getElementById('table-container');
            
            fetch(link.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('table-container');
                    if (newContent && container) {
                        container.innerHTML = newContent.innerHTML;
                        if (window.lucide) window.lucide.createIcons();
                        window.history.pushState({}, '', link.href);
                    }
                });
        }
    });
});
</script>
@endpush

@endsection