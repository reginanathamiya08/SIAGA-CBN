@extends('admin.sidebar')
@section('title', 'Edit Komponen Gaji')

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('admin.komponen-gaji-karyawan.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight uppercase">
            Komponen Gaji: {{ $karyawan->nama }}
        </h1>
        <p class="text-gray-500 mt-1 text-sm">
            {{ $karyawan->jabatan }} • {{ $karyawan->labelDivisi() }} •
            <span class="font-mono text-[#1E3A5F] font-black text-xs">{{ $karyawan->nip }}</span>
        </p>
    </div>
</header>

@php $kg = $karyawan->komponenGaji @endphp

<form method="POST" action="{{ route('admin.komponen-gaji-karyawan.update', $karyawan->id) }}"
      id="form-gaji">
@csrf @method('PUT')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Kiri: Form Input ────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Info Karyawan --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center
                            font-black text-lg shrink-0
                            {{ $karyawan->isTetap() ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700' }}">
                    {{ strtoupper(substr($karyawan->nama, 0, 2)) }}
                </div>
                <div class="flex-1">
                    <p class="font-black text-[#1E3A5F] uppercase text-base">{{ $karyawan->nama }}</p>
                    <div class="flex flex-wrap gap-2 mt-1">
                        <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase
                                     {{ $karyawan->isTetap() ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700' }}">
                            {{ $karyawan->isTetap() ? 'Karyawan Tetap' : 'Karyawan Kontrak' }}
                        </span>
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded-lg text-[9px] font-black uppercase">
                            NIP: {{ $karyawan->nip }}
                        </span>
                        @if ($karyawan->gaji_atas_umr)
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-lg text-[9px] font-black uppercase">
                                Gaji di Atas UMR
                            </span>
                        @endif
                        @if ($karyawan->is_shift)
                            <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded-lg text-[9px] font-black uppercase">
                                Bersifat Shift
                            </span>
                        @endif
                    </div>
                </div>
                @if (!$karyawan->gaji_atas_umr)
                    <div class="text-right shrink-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">UMR</p>
                        <p class="text-sm font-black text-gray-600">Rp {{ number_format($umrTahunIni, 0, ',', '.') }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Komponen Gaji --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F] uppercase italic text-[11px] mb-5
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-green-500 rounded-full"></span>
                Komponen Pendapatan
            </h3>

            <div class="space-y-4">

                {{-- Gaji Pokok --}}
                <div>
                    <label class="block text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2">
                        Gaji Pokok <span class="text-red-500">*</span>
                        @if (!$karyawan->gaji_atas_umr)
                            <span class="font-normal text-blue-500 normal-case ml-1">
                                (minimal UMR Rp {{ number_format($umrTahunIni, 0, ',', '.') }})
                            </span>
                        @endif
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-gray-400">Rp</span>
                        <input type="number" name="gaji_pokok" id="inp-gaji-pokok"
                               value="{{ old('gaji_pokok', $kg->gaji_pokok ?? 0) }}"
                               min="0" step="1000"
                               placeholder="{{ number_format($umrTahunIni, 0) }}"
                               oninput="hitungPreview()"
                               class="w-full pl-10 pr-4 py-3 rounded-xl border text-sm font-semibold
                                      text-gray-700 outline-none transition-all
                                      @error('gaji_pokok') border-red-400 bg-red-50
                                      @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                      @enderror">
                    </div>
                    @error('gaji_pokok')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Uang Makan & Transport --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2">
                                Uang Makan / Hari <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-gray-400">Rp</span>
                                <input type="number" name="uang_makan" id="inp-makan"
                                       value="{{ old('uang_makan', $kg->uang_makan ?? 35000) }}"
                                       min="0" step="1000"
                                       oninput="hitungPreview()"
                                       class="w-full pl-10 pr-4 py-3 rounded-xl border text-sm font-semibold
                                              text-gray-700 outline-none transition-all
                                              @error('uang_makan') border-red-400 bg-red-50
                                              @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                              @enderror">
                            </div>
                            @error('uang_makan')
                                <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2">
                                Uang Transport / Hari <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-gray-400">Rp</span>
                                <input type="number" name="uang_transport" id="inp-transport"
                                       value="{{ old('uang_transport', $kg->uang_transport ?? 45000) }}"
                                       min="0" step="1000"
                                       oninput="hitungPreview()"
                                       class="w-full pl-10 pr-4 py-3 rounded-xl border text-sm font-semibold
                                              text-gray-700 outline-none transition-all
                                              @error('uang_transport') border-red-400 bg-red-50
                                              @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                              @enderror">
                            </div>
                            @error('uang_transport')
                                <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <p class="text-[9px] text-gray-400 font-semibold">
                        ⚠️ Jika karyawan <strong>telat/alfa</strong>: uang makan + transport dipotong
                        (total Rp {{ number_format(35000 + 45000, 0, ',', '.') }}/hari)
                    </p>

            </div>
        </div>



    </div>

    {{-- ── Kanan: Preview Slip Gaji ────────────────────────────── --}}
    <div class="space-y-5">

        {{-- Preview Slip --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-[#1E3A5F] text-white">
                <p class="text-[10px] font-black uppercase tracking-widest text-white/60">Preview</p>
                <p class="text-sm font-black uppercase mt-0.5">Simulasi Slip Gaji</p>
                <p class="text-[9px] text-white/40 mt-0.5">Asumsi: hadir penuh, tidak telat</p>
            </div>
            <div class="p-5 space-y-3">
                {{-- Pendapatan --}}
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Pendapatan</p>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 font-semibold">Gaji Pokok</span>
                    <span class="font-black text-gray-800" id="prev-gaji-pokok">Rp 0</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 font-semibold">Uang Makan (26hr)</span>
                    <span class="font-black text-gray-800" id="prev-uang-makan">Rp 0</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 font-semibold">Uang Transport (26hr)</span>
                    <span class="font-black text-gray-800" id="prev-uang-transport">Rp 0</span>
                </div>
                <div class="flex justify-between text-sm font-black border-t border-gray-100 pt-2">
                    <span class="text-gray-700">Total Pendapatan</span>
                    <span class="text-green-600" id="prev-total-pendapatan">Rp 0</span>
                </div>

                {{-- Potongan --}}
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest pt-2">Potongan</p>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 font-semibold">BPJS Kesehatan</span>
                    <span class="font-black text-red-500" id="prev-bpjs-kes">Rp 0</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 font-semibold">BPJS Ketenagakerjaan</span>
                    <span class="font-black text-red-500" id="prev-bpjs-tk">Rp 0</span>
                </div>
                <div class="flex justify-between text-sm font-black border-t border-gray-100 pt-2">
                    <span class="text-gray-700">Total Potongan</span>
                    <span class="text-red-500" id="prev-total-potongan">Rp 0</span>
                </div>

                {{-- Gaji Bersih --}}
                <div class="flex justify-between items-center bg-[#1E3A5F] rounded-2xl px-4 py-3 mt-2">
                    <span class="text-white font-black uppercase text-[10px] tracking-widest">Gaji Bersih</span>
                    <span class="text-white font-black text-base" id="prev-gaji-bersih">Rp 0</span>
                </div>

                @if (!$karyawan->gaji_atas_umr)
                    <div id="warn-umr" class="hidden p-3 bg-amber-50 rounded-xl border border-amber-200">
                        <p class="text-[9px] text-amber-600 font-semibold">
                            ⚠️ Gaji pokok di bawah UMR Rp {{ number_format($umrTahunIni, 0, ',', '.') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tombol --}}
        <button type="submit"
                class="w-full bg-[#1E3A5F] hover:bg-green-600 text-white font-black text-sm
                       uppercase tracking-widest py-4 rounded-2xl transition-all shadow-sm
                       active:scale-95 italic flex items-center justify-center gap-2">
            <i data-lucide="save" class="w-5 h-5"></i>
            Simpan Komponen Gaji
        </button>

        <a href="{{ route('admin.komponen-gaji-karyawan.index') }}"
           class="block text-center text-xs font-bold text-gray-400 hover:text-red-500 transition-colors">
            Batal
        </a>
    </div>

</div>

</form>

@endsection

@push('scripts')
<script>
var uangMakanByMitra = {{ $karyawan->uang_makan_by_mitra ? 'true' : 'false' }};
var umrTahunIni      = {{ $umrTahunIni }};

function rp(angka) {
    return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
}

function hitungPreview() {
    var gajiPokok   = parseFloat(document.getElementById('inp-gaji-pokok')?.value) || 0;
    var uangMakan   = uangMakanByMitra ? 0 : (parseFloat(document.getElementById('inp-makan')?.value) || 0);
    var transport   = uangMakanByMitra ? 0 : (parseFloat(document.getElementById('inp-transport')?.value) || 0);
    var persBpjsKes = {{ \App\Models\Configuration::getValue('persen_bpjs_kes', 9.24) }};
    var persBpjsTk  = {{ \App\Models\Configuration::getValue('persen_bpjs_tk', 5.00) }};

    // Asumsi 26 hari kerja per bulan
    var hariKerja     = 26;
    var totalMakan    = uangMakan * hariKerja;
    var totalTransport= transport * hariKerja;
    var totalPendapatan = gajiPokok + totalMakan + totalTransport;

    var bpjsKes       = gajiPokok * (persBpjsKes / 100);
    var bpjsTk        = gajiPokok * (persBpjsTk / 100);
    var totalPotongan  = bpjsKes + bpjsTk;
    var gajiBersih     = totalPendapatan - totalPotongan;

    // Update preview
    document.getElementById('prev-gaji-pokok').textContent    = rp(gajiPokok);
    document.getElementById('prev-total-pendapatan').textContent = rp(totalPendapatan);
    document.getElementById('prev-bpjs-kes').textContent      = rp(bpjsKes);
    document.getElementById('prev-bpjs-tk').textContent       = rp(bpjsTk);
    document.getElementById('prev-total-potongan').textContent = rp(totalPotongan);
    document.getElementById('prev-gaji-bersih').textContent   = rp(gajiBersih);

    document.getElementById('prev-uang-makan').textContent = rp(totalMakan);
    document.getElementById('prev-uang-transport').textContent = rp(totalTransport);

    // Warning UMR
    var warnEl = document.getElementById('warn-umr');
    if (warnEl) {
        warnEl.classList.toggle('hidden', gajiPokok === 0 || gajiPokok >= umrTahunIni);
    }
}

// Hitung saat halaman load
hitungPreview();
</script>
@endpush
