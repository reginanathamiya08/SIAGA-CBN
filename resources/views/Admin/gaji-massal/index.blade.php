@extends('admin.sidebar')
@section('title', 'Auto-Fill Gaji')

@section('content')
<div x-data="{ mode: `{{ session('mode', 'choice') }}` }" class="w-full">
    
    {{-- Header --}}
    <header class="flex items-center justify-between mb-10">
        <div class="flex items-center gap-4">
            <div class="w-1.5 h-10 bg-blue-600 rounded-full"></div>
            <div>
                <h1 class="text-2xl font-black text-[#1E3A5F] ">Auto-Fill Gaji</h1>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1.5">Manajemen Konfigurasi Massal</p>
            </div>
        </div>
        <button @click="mode === `choice` ? window.location.href=`{{ route('admin.komponen-gaji.index') }}` : mode = `choice`"
                class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-black uppercase text-gray-400 hover:text-blue-600 hover:border-blue-200 transition-all shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </button>
    </header>

    {{-- 1. CHOICE SCREEN --}}
    <div x-show="mode === `choice`" x-transition.fade.duration.300ms class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl">
        <button @click="mode = `tetap`" class="group bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-2xl hover:border-blue-500/20 transition-all text-left relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-40 h-40 bg-blue-50 rounded-full opacity-40 group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10">
                <div class="w-16 h-16 bg-[#1E3A5F] rounded-2xl flex items-center justify-center text-white mb-8 shadow-xl">
                    <i data-lucide="user-check" class="w-8 h-8"></i>
                </div>
                <h3 class="text-2xl font-black text-[#1E3A5F] uppercase italic mb-3">Karyawan Tetap</h3>
                <p class="text-sm text-gray-400 font-medium mb-10 leading-relaxed">Kelola gaji personil tetap divisi Keuangan, Koordinator CS, dan Umum.</p>
                <div class="inline-flex items-center gap-2 text-blue-600 font-black text-xs uppercase tracking-widest">
                    Pilih Kategori <i data-lucide="chevron-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </button>

        <button @click="mode = `kontrak`" class="group bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-2xl hover:border-emerald-500/20 transition-all text-left relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-40 h-40 bg-emerald-50 rounded-full opacity-40 group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10">
                <div class="w-16 h-16 bg-emerald-500 rounded-2xl flex items-center justify-center text-white mb-8 shadow-xl">
                    <i data-lucide="file-text" class="w-8 h-8"></i>
                </div>
                <h3 class="text-2xl font-black text-[#1E3A5F] uppercase italic mb-3">Karyawan Kontrak</h3>
                <p class="text-sm text-gray-400 font-medium mb-10 leading-relaxed">Update massal gaji Divisi Umum (UMR) & Divisi HC (Above UMR).</p>
                <div class="inline-flex items-center gap-2 text-emerald-600 font-black text-xs uppercase tracking-widest">
                    Pilih Kategori <i data-lucide="chevron-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </button>
    </div>

    {{-- 2. FORM TETAP --}}
    <div x-show="mode === `tetap`" x-transition.fade.duration.300ms class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden max-w-6xl">
        <div class="p-8 border-b border-gray-50 bg-gray-50/50">
            <h2 class="text-base font-black text-[#1E3A5F] uppercase italic flex items-center gap-3">
                <div class="w-1.5 h-6 bg-blue-600 rounded-full"></div>
                Input Gaji Karyawan Tetap
            </h2>
        </div>

        <form action="{{ route('admin.gaji-massal.update-spesialis') }}" method="POST" class="p-10">
            @csrf
            <div class="space-y-12">
                @foreach($jabatanTetapByDivisi as $divisi => $jabatans)
                <div>
                    <div class="flex items-center gap-4 mb-6">
                        <h3 class="text-xs font-black text-blue-600 bg-blue-50 px-4 py-2 rounded-full uppercase tracking-widest">{{ str_replace(['_', '-'], ' ', strtoupper($divisi)) }}</h3>
                        <div class="h-[1px] flex-1 bg-gray-100"></div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($jabatans as $js)
                        @php $val = $currentSalaries[$js] ?? 0; @endphp
                        <div class="bg-gray-50/50 p-5 rounded-2xl border border-transparent hover:border-blue-100 hover:bg-white transition-all shadow-sm"
                             x-data="{ editing: {{ $val > 0 ? 'false' : 'true' }} }">
                            <div class="flex justify-between items-center mb-3">
                                <label class="text-xs font-bold text-gray-500 uppercase tracking-tight">{{ $js }}</label>
                                @if($val > 0)
                                <button type="button" @click="editing = !editing" class="text-blue-500 hover:text-blue-700 transition-colors">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                @endif
                            </div>
                            
                            <div x-show="!editing" class="flex items-center justify-between">
                                <span class="text-base font-black text-[#1E3A5F]">Rp {{ number_format($val, 0, ',', '.') }}</span>
                                <span class="text-[10px] px-2 py-0.5 bg-blue-100 text-blue-600 rounded-lg font-black uppercase tracking-tighter">TERSIMPAN</span>
                            </div>

                            <div x-show="editing" class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-blue-600 font-bold text-sm">Rp</span>
                                <input type="number" name="gaji[{{ $js }}]" value="{{ $val > 0 ? $val : '' }}" placeholder="---"
                                       class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm font-black text-[#1E3A5F] outline-none focus:border-blue-500 transition-all shadow-inner">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            <button type="submit" class="w-full mt-10 py-5 bg-[#1E3A5F] text-white rounded-2xl font-black text-sm uppercase italic tracking-widest shadow-xl hover:bg-blue-800 transition-all active:scale-95">Update Gaji Tetap Massal</button>
        </form>
    </div>

    {{-- 3. FORM KONTRAK --}}
    <div x-show="mode === `kontrak`" x-transition.fade.duration.300ms class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start max-w-7xl">
        {{-- Divisi HC --}}
        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden flex flex-col h-full">
            <div class="p-6 border-b border-gray-50 bg-blue-50/30">
                <h2 class="text-base font-black text-[#1E3A5F] uppercase italic">Divisi HC (Mitra)</h2>
            </div>
            <div class="p-8 flex-1 flex flex-col justify-between">
                <form action="{{ route('admin.gaji-massal.update-spesialis') }}" method="POST">
                    @csrf
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-5">HC Spesialis (Above UMR)</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
                        @foreach($jabatanHCSpesialisKontrak as $js)
                        @php $val = $currentSalaries[$js] ?? 0; @endphp
                        <div class="bg-gray-50 p-5 rounded-2xl shadow-sm border border-transparent hover:border-blue-100 transition-all" x-data="{ editing: {{ $val > 0 ? 'false' : 'true' }} }">
                            <div class="flex justify-between items-center mb-3">
                                <label class="text-xs font-bold text-gray-500 uppercase">{{ $js }}</label>
                                @if($val > 0)
                                <button type="button" @click="editing = !editing" class="text-blue-500 hover:scale-110 transition-transform">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                @endif
                            </div>
                            <div x-show="!editing" class="flex items-center justify-between">
                                <span class="text-base font-black text-[#1E3A5F]">Rp {{ number_format($val, 0, ',', '.') }}</span>
                            </div>
                            <div x-show="editing" class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-blue-600 font-bold text-sm">Rp</span>
                                <input type="number" name="gaji[{{ $js }}]" value="{{ $val > 0 ? $val : '' }}" placeholder="---" class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm font-black text-[#1E3A5F] outline-none shadow-inner">
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <button type="submit" class="w-full py-4 bg-[#1E3A5F] text-white rounded-xl font-black text-xs uppercase italic tracking-widest shadow-lg hover:bg-blue-800 transition-all">Simpan HC Spesialis</button>
                </form>

                <form action="{{ route('admin.gaji-massal.update-umr') }}" method="POST" class="pt-10 mt-10 border-t border-gray-100">
                    @csrf
                    <input type="hidden" name="target" value="hc_umr">
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">HC Standar (UMR)</p>
                    @php $umrVal = $currentSalaries['Satpam'] ?? 0; @endphp
                    <div class="bg-gray-50 p-8 rounded-[2.5rem] border border-emerald-100 space-y-6 text-center shadow-sm"
                         x-data="{ editing: {{ $umrVal > 0 ? 'false' : 'true' }} }">
                        
                        <div x-show="!editing" class="flex flex-col items-center gap-3">
                            <span class="text-3xl font-black text-emerald-600 italic tracking-tighter">Rp {{ number_format($umrVal, 0, ',', '.') }}</span>
                            <button type="button" @click="editing = true" class="text-xs font-black text-blue-500 uppercase tracking-widest flex items-center gap-1.5 hover:scale-105 transition-transform">
                                <i data-lucide="edit-3" class="w-4 h-4"></i> Ubah UMR HC
                            </button>
                        </div>

                        <div x-show="editing" class="space-y-5">
                            <div class="relative max-w-xs mx-auto">
                                <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-emerald-600 font-bold text-base">Rp</span>
                                <input type="number" name="nominal_umr" value="{{ $umrVal > 0 ? $umrVal : '' }}" required placeholder="UMR HC" class="w-full pl-12 pr-5 py-4 bg-white border border-gray-200 rounded-2xl text-base font-black text-[#1E3A5F] outline-none text-center shadow-inner focus:border-emerald-500 transition-all">
                            </div>
                            <button type="submit" class="w-full py-4 bg-emerald-500 text-white rounded-2xl font-black text-xs uppercase italic tracking-widest shadow-lg hover:bg-emerald-600 transition-all">Update UMR HC</button>
                            <button type="button" @click="editing = false" class="text-xs font-bold text-gray-400 uppercase">Batal</button>
                        </div>

                        <div class="flex flex-wrap gap-2 justify-center mt-6">
                            @foreach($jabatanHCUmrKontrak as $j)
                            <span class="px-3 py-1 bg-white border border-gray-100 text-[10px] font-black text-emerald-600 rounded-lg uppercase tracking-tight">{{ $j }}</span>
                            @endforeach
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Divisi Umum (CBN) --}}
        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden flex flex-col h-full">
            <div class="p-6 border-b border-gray-50 bg-amber-50/20">
                <h2 class="text-base font-black text-[#1E3A5F] uppercase italic">Divisi Umum (CBN)</h2>
            </div>
            <form action="{{ route('admin.gaji-massal.update-umr') }}" method="POST" class="p-8">
                @csrf
                <input type="hidden" name="target" value="umum_umr">
                @php $umumVal = $currentSalaries['CS'] ?? 0; @endphp
                <div class="bg-gray-50 p-8 rounded-[2.5rem] border border-amber-100 space-y-6 text-center shadow-sm"
                     x-data="{ editing: {{ $umumVal > 0 ? 'false' : 'true' }} }">
                    
                    <div x-show="!editing" class="flex flex-col items-center gap-3">
                        <span class="text-3xl font-black text-amber-600 italic tracking-tighter">Rp {{ number_format($umumVal, 0, ',', '.') }}</span>
                        <button type="button" @click="editing = true" class="text-xs font-black text-blue-500 uppercase tracking-widest flex items-center gap-1.5 hover:scale-105 transition-transform">
                            <i data-lucide="edit-3" class="w-4 h-4"></i> Ubah UMR Umum
                        </button>
                    </div>

                    <div x-show="editing" class="space-y-5">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Nominal UMR Umum</p>
                        <div class="relative max-w-xs mx-auto">
                            <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-amber-600 font-bold text-base">Rp</span>
                            <input type="number" name="nominal_umr" value="{{ $umumVal > 0 ? $umumVal : '' }}" required placeholder="0" class="w-full pl-12 pr-5 py-4 bg-white border border-gray-200 rounded-2xl text-base font-black text-[#1E3A5F] outline-none text-center shadow-inner focus:border-amber-500 transition-all">
                        </div>
                        <button type="submit" class="w-full py-4 bg-amber-500 text-white rounded-2xl font-black text-xs uppercase italic tracking-widest shadow-lg hover:bg-amber-600 transition-all">Update UMR Umum</button>
                        <button type="button" @click="editing = false" class="text-xs font-bold text-gray-400 uppercase">Batal</button>
                    </div>

                    <div class="flex flex-wrap gap-2 justify-center mt-6">
                        @foreach($jabatanUmumKontrak as $j)
                        <span class="px-3 py-1 bg-white border border-gray-100 text-[10px] font-black text-amber-600 rounded-lg uppercase tracking-tight">{{ $j }}</span>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
</style>
@endsection