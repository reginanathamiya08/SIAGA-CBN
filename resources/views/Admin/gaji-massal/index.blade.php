@extends('admin.sidebar')
@section('title', 'Auto-Fill Gaji (Hybrid)')

@section('content')
<div x-data="{ tab: 'tetap' }" class="w-full">
    
    {{-- Header --}}
    <header class="flex items-center justify-between mb-10">
        <div class="flex items-center gap-4">
            <div class="w-1.5 h-10 bg-blue-600 rounded-full"></div>
            <div>
                <h1 class="text-2xl font-black text-[#1E3A5F] ">Auto-Fill Gaji</h1>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1.5">Manajemen Penggajian Massal (Tetap & Kontrak)</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button @click="tab = 'tetap'" :class="tab === 'tetap' ? 'bg-[#1E3A5F] text-white shadow-lg' : 'bg-white text-gray-400 border border-gray-100'"
                    class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                Karyawan Tetap
            </button>
            <button @click="tab = 'kontrak'" :class="tab === 'kontrak' ? 'bg-emerald-600 text-white shadow-lg' : 'bg-white text-gray-400 border border-gray-100'"
                    class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                Karyawan Kontrak
            </button>
        </div>
    </header>

    {{-- TAB KARYAWAN TETAP (PENDIDIKAN) --}}
    <div x-show="tab === 'tetap'" x-transition>
        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden max-w-6xl">
            <div class="p-8 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                <div>
                    <h2 class="text-base font-black text-[#1E3A5F] uppercase italic flex items-center gap-3">
                        <i data-lucide="graduation-cap" class="w-5 h-5 text-blue-600"></i>
                        Gaji Karyawan Tetap Berdasarkan Tamatan
                    </h2>
                    <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tight">Update berdasarkan jenjang pendidikan (Ijazah).</p>
                </div>
            </div>

            <form action="{{ route('admin.gaji-massal.update-tetap') }}" method="POST" class="p-10">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach($levels as $lv)
                    @php $val = $currentSalaries[$lv] ?? 0; @endphp
                    <div class="bg-white p-6 rounded-3xl border border-blue-100 transition-all shadow-sm group">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-xl font-black text-[#1E3A5F] tracking-tighter">{{ $lv }}</h3>
                                <p class="text-[9px] font-black text-gray-400 uppercase">{{ $statsTetap[$lv] }} Orang</p>
                            </div>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center font-bold text-xs text-blue-600">Rp</span>
                            <input type="text" name="gaji[{{ $lv }}]" 
                                   value="{{ $val > 0 ? number_format($val, 0, ',', '.') : '' }}" 
                                   oninput="this.value = formatRupiah(this.value)"
                                   class="w-full pl-10 pr-4 py-3 border border-blue-500 ring-4 ring-blue-500/5 rounded-xl text-sm font-black text-[#1E3A5F] outline-none transition-all shadow-inner">
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-10">
                    <button type="submit" class="w-full py-5 bg-[#1E3A5F] text-white rounded-2xl font-black text-sm uppercase italic tracking-widest shadow-xl hover:bg-blue-800 transition-all active:scale-95 flex items-center justify-center gap-3">
                        <i data-lucide="save" class="w-5 h-5"></i>
                        Simpan Gaji Karyawan Tetap
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- TAB KARYAWAN KONTRAK (DIVISI HC & DIVISI UMUM) --}}
    <div x-show="tab === 'kontrak'" x-transition x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 max-w-6xl">
            {{-- Divisi HC --}}
            <div class="space-y-6">
                <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/50">
                        <h2 class="text-sm font-black text-[#1E3A5F] uppercase flex items-center gap-3">
                            <i data-lucide="award" class="w-5 h-5 text-indigo-600"></i>
                            KONTRAK: DIVISI HC (DI ATAS UMR)
                        </h2>
                        <p class="text-[9px] text-gray-400 font-bold mt-1 uppercase">Gaji ditentukan berdasarkan Tamatan Pendidikan (Ijazah).</p>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <form action="{{ route('admin.gaji-massal.update-kontrak-hc') }}" method="POST">
                            @csrf
                            <div class="space-y-5">
                                {{-- Gaji SMA / SMK --}}
                                <div class="bg-indigo-50/50 p-5 rounded-2xl border border-indigo-100">
                                    <div class="flex justify-between items-center mb-2">
                                        <label class="text-[10px] font-black text-indigo-700 uppercase tracking-wider">🎓 Tamatan SMA / SMK</label>
                                        <span class="text-[9px] font-bold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">{{ $countKontrakHcSma ?? 0 }} Karyawan</span>
                                    </div>
                                    <div class="relative mt-2">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-xs text-indigo-500">Rp</span>
                                        <input type="text" name="gaji_sma" 
                                               value="{{ isset($currentSalaries['kontrak_hc_sma']) && $currentSalaries['kontrak_hc_sma'] > 0 ? number_format($currentSalaries['kontrak_hc_sma'], 0, ',', '.') : '3.200.000' }}" 
                                               oninput="this.value = formatRupiah(this.value)"
                                               class="w-full pl-10 pr-4 py-3 border border-indigo-200 rounded-xl text-sm font-black text-[#1E3A5F] outline-none bg-white focus:border-indigo-500 shadow-sm">
                                    </div>
                                </div>

                                {{-- Gaji D3 / S1 --}}
                                <div class="bg-blue-50/50 p-5 rounded-2xl border border-blue-100">
                                    <div class="flex justify-between items-center mb-2">
                                        <label class="text-[10px] font-black text-blue-700 uppercase tracking-wider">🎓 Tamatan D3 / S1 / S2</label>
                                        <span class="text-[9px] font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{{ $countKontrakHcD3S1 ?? 0 }} Karyawan</span>
                                    </div>
                                    <div class="relative mt-2">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-xs text-blue-500">Rp</span>
                                        <input type="text" name="gaji_d3_s1" 
                                               value="{{ isset($currentSalaries['kontrak_hc_d3_s1']) && $currentSalaries['kontrak_hc_d3_s1'] > 0 ? number_format($currentSalaries['kontrak_hc_d3_s1'], 0, ',', '.') : '3.800.000' }}" 
                                               oninput="this.value = formatRupiah(this.value)"
                                               class="w-full pl-10 pr-4 py-3 border border-blue-200 rounded-xl text-sm font-black text-[#1E3A5F] outline-none bg-white focus:border-blue-500 shadow-sm">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-gray-100">
                                <p class="text-[8px] font-black text-gray-400 uppercase mb-2">Jabatan Divisi HC:</p>
                                <div class="flex flex-wrap gap-1.5 mb-6">
                                    @foreach($jabatanHCList as $j)
                                        <span class="px-2 py-0.5 bg-gray-100 text-[8px] font-bold text-gray-600 rounded-md uppercase">{{ $j }}</span>
                                    @endforeach
                                </div>
                                <button type="submit" class="w-full py-4 bg-[#1E3A5F] text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg hover:bg-blue-800 transition-all flex items-center justify-center gap-2">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                    Simpan Gaji Divisi HC
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Divisi Umum --}}
            <div class="space-y-6">
                <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/50">
                        <h2 class="text-sm font-black text-[#1E3A5F] uppercase flex items-center gap-3">
                            <i data-lucide="building-2" class="w-5 h-5 text-emerald-600"></i>
                            KONTRAK: DIVISI UMUM (UMR BERJALAN)
                        </h2>
                        <p class="text-[9px] text-gray-400 font-bold mt-1 uppercase">Gaji Pokok mengikuti standar UMR berlaku.</p>
                    </div>
                    <div class="p-8">
                        <form action="{{ route('admin.gaji-massal.update-umr') }}" method="POST">
                            @csrf
                            <div class="bg-emerald-50/50 p-6 rounded-[2rem] border border-emerald-200 shadow-sm text-center">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">Nominal UMR Berjalan</span>
                                    <span class="text-[9px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">{{ $countKontrakUmum ?? 0 }} Karyawan</span>
                                </div>
                                <div class="relative max-w-[240px] mx-auto mb-6">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-base text-emerald-600">Rp</span>
                                    <input type="text" name="nominal_umr" 
                                           value="{{ isset($currentSalaries['umr_tahun_ini']) && $currentSalaries['umr_tahun_ini'] > 0 ? number_format($currentSalaries['umr_tahun_ini'], 0, ',', '.') : '2.994.031' }}" 
                                           oninput="this.value = formatRupiah(this.value)"
                                           class="w-full pl-11 pr-4 py-4 border-2 border-emerald-400 rounded-2xl text-center text-xl font-black text-emerald-700 outline-none bg-white focus:border-emerald-600 shadow-inner">
                                </div>
                                <div class="pt-3 border-t border-emerald-100 text-left">
                                    <p class="text-[8px] font-black text-emerald-600 uppercase mb-2">Jabatan Divisi Umum (UMR):</p>
                                    <div class="flex flex-wrap gap-1.5 mb-6">
                                        @foreach($jabatanUmumList as $j)
                                            <span class="px-2 py-0.5 bg-white text-[8px] font-bold text-emerald-700 border border-emerald-200 rounded-md uppercase">{{ $j }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <button type="submit" class="w-full py-4 bg-emerald-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg hover:bg-emerald-700 transition-all flex items-center justify-center gap-2">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                    Simpan Gaji UMR Umum
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
