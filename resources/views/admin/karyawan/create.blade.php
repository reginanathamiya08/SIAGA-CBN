@extends('admin.sidebar')
@section('title', 'Tambah Karyawan')

@section('content')

{{-- Data PHP → JavaScript (embed sebagai JSON tersembunyi) --}}
<script id="data-php" type="application/json">
{
    "divisiTetap":    @json($divisiTetap),
    "divisiKontrak":  @json($divisiKontrak),
    "jabatanMap":     @json($jabatanMap),
    "dokumenWajib":   @json($dokumenWajib),
    "jabatanShift":   @json($jabatanShift),
    "jabatanAtasUmr": @json($jabatanAtasUmr),
    "oldJenis":       "{{ old('jenis_karyawan_id') }}",
    "oldDivisi":      "{{ old('divisi') }}",
    "oldJabatan":     "{{ old('jabatan') }}"
}
</script>

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('admin.karyawan.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight  ">
            Tambah Karyawan
        </h1>
        <p class="text-gray-500 mt-1 text-sm">ID Karyawan (NIP) akan di-generate otomatis oleh sistem</p>
    </div>
</header>

<form method="POST" action="{{ route('admin.karyawan.store') }}"
      enctype="multipart/form-data">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── KIRI ─────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Informasi Dasar --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-5
                           flex items-center gap-2">
                    <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                    Informasi Dasar
                </h3>

                <div class="space-y-5">

                    {{-- Nama --}}
                    <div>
                        <label class="block text-[11px] font-black text-gray-500
                                        mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama" value="{{ old('nama') }}"
                               placeholder="Masukkan nama lengkap karyawan"
                               class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                      text-gray-700 placeholder-gray-300 outline-none transition-all
                                      @error('nama') border-red-400 bg-red-50
                                      @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                      @enderror">
                        @error('nama')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-[11px] font-black text-gray-500
                                        mb-2">
                            Email <span class="text-red-500">*</span>
                            <span class="font-normal text-gray-400 normal-case ml-1">
                                (untuk menerima notifikasi approval)
                            </span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               placeholder="contoh@email.com"
                               class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                      text-gray-700 placeholder-gray-300 outline-none transition-all
                                      @error('email') border-red-400 bg-red-50
                                      @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                      @enderror">
                        @error('email')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Role / Jenis Karyawan --}}
                    <div>
                        <label class="block text-[11px] font-black text-gray-500
                                        mb-2 uppercase tracking-wider">
                            Jenis Karyawan <span class="text-red-500">*</span>
                        </label>
                        <select name="role_id" id="sel-jenis"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-sm font-semibold
                                       text-gray-700 outline-none focus:border-[#1E3A5F] transition-all cursor-pointer">
                            <option value="">-- Pilih Jenis Karyawan --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" data-slug="{{ $role->slug }}"
                                        {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->nama_role }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Divisi --}}
                    <div class="space-y-2">
                        <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider">
                            Divisi <span class="text-red-500">*</span>
                        </label>
                        <select name="divisi" id="sel-divisi"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-sm font-semibold
                                       text-gray-700 outline-none focus:border-[#1E3A5F] transition-all cursor-pointer">
                            <option value="">-- Pilih Divisi --</option>
                        </select>
                        <p class="text-[10px] text-gray-400 font-medium italic" id="teks-info-divisi"></p>
                        @error('divisi')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jabatan / Tenaga Kerja --}}
                    <div class="space-y-2">
                        <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider" id="lbl-jabatan">
                            Tenaga Kerja / Jabatan <span class="text-red-500">*</span>
                        </label>
                        <select name="jabatan" id="sel-jabatan"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-sm font-semibold
                                       text-gray-700 outline-none focus:border-[#1E3A5F] transition-all cursor-pointer">
                            <option value="">-- Pilih Tenaga Kerja / Jabatan --</option>
                        </select>
                        @error('jabatan')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tamatan / Pendidikan --}}
                    <div class="space-y-2">
                        <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider">
                            Tamatan (Pendidikan) <span class="text-red-500">*</span>
                        </label>
                        <select name="pendidikan" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-sm font-semibold
                                       text-gray-700 outline-none focus:border-[#1E3A5F] transition-all cursor-pointer">
                            <option value="">-- Pilih Tamatan --</option>
                            <option value="S3" {{ old('pendidikan')==='S3' ? 'selected' : '' }}>S3</option>
                            <option value="S2" {{ old('pendidikan')==='S2' ? 'selected' : '' }}>S2</option>
                            <option value="S1" {{ old('pendidikan')==='S1' ? 'selected' : '' }}>S1</option>
                            <option value="D3" {{ old('pendidikan')==='D3' ? 'selected' : '' }}>D3</option>
                            <option value="SMA/SMK" {{ old('pendidikan')==='SMA/SMK' ? 'selected' : '' }}>SMA/SMK</option>
                        </select>
                        @error('pendidikan')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Masuk & No HP --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-black text-gray-500
                                            mb-2">
                                Tanggal Masuk <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_masuk"
                                   value="{{ old('tanggal_masuk') }}"
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                          text-gray-700 outline-none transition-all
                                          @error('tanggal_masuk') border-red-400 bg-red-50
                                          @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                          @enderror">
                            @error('tanggal_masuk')
                                <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-gray-500
                                            mb-2">
                                No. HP
                            </label>
                            <input type="text" name="no_hp" value="{{ old('no_hp') }}"
                                   placeholder="Contoh: 08123456789"
                                   class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                          text-gray-700 placeholder-gray-300 outline-none transition-all
                                          border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white">
                        </div>
                    </div>

                </div>
            </div>

            {{-- Dokumen Wajib --}}
            <div id="wrap-dokumen"
                 class="hidden bg-amber-50 rounded-3xl border border-amber-200 shadow-sm p-6">
                <h3 class="font-black text-amber-700   italic text-[11px] mb-2
                           flex items-center gap-2">
                    <span class="w-1 h-4 bg-amber-500 rounded-full"></span>
                    Dokumen Wajib
                </h3>
                <p class="text-xs text-amber-600 font-semibold mb-4" id="teks-dokumen"></p>
                <input type="hidden" name="jenis_dokumen" id="input-jenis-dokumen">
                <div>
                    <label class="block text-[11px] font-black text-gray-500
                                    mb-2">
                        Upload File <span class="text-red-500">*</span>
                        <span class="font-normal text-gray-400 normal-case ml-1">
                            (PDF/JPG/PNG, maks 2MB)
                        </span>
                    </label>
                    <input type="file" name="file_dokumen"
                           accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full px-4 py-3 rounded-xl border border-amber-200
                                  bg-white text-sm font-semibold text-gray-700
                                  outline-none focus:border-amber-400">
                    @error('file_dokumen')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </div>

        {{-- ── KANAN ────────────────────────────────────────────── --}}
        <div class="space-y-5">

            {{-- Preview Username --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-4
                           flex items-center gap-2">
                    <span class="w-1 h-4 bg-purple-500 rounded-full"></span>
                    ID Karyawan Otomatis
                </h3>
                <div class="bg-gray-50 rounded-2xl p-4 text-center mb-4">
                    <p class="text-[9px] font-black text-gray-400   mb-2">
                        ID Karyawan yang akan diberikan
                    </p>
                    <p class="text-xl font-black text-[#1E3A5F] font-mono tracking-widest"
                       id="preview-username">—</p>
                    <p class="text-xs text-slate-400 mt-1">ID Karyawan akan dibuat otomatis berdasarkan jenis karyawan.</p>
                </div>
                <div class="space-y-2 border-t border-gray-100 pt-3">
                    <p class="text-[9px] font-black text-gray-400   mb-1">Format:</p>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-[10px] bg-blue-50 text-blue-700
                                     px-2 py-0.5 rounded font-black">KT-CBN-XXXX</span>
                        <span class="text-[9px] text-gray-400">Karyawan Tetap</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-[10px] bg-red-50 text-red-600
                                     px-2 py-0.5 rounded font-black">KK-HC-XXXX</span>
                        <span class="text-[9px] text-gray-400">Kontrak Div. HC</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-[10px] bg-red-50 text-red-600
                                     px-2 py-0.5 rounded font-black">KK-UM-XXXX</span>
                        <span class="text-[9px] text-gray-400">Kontrak Div. Umum</span>
                    </div>
                </div>
            </div>

            {{-- Info Flags --}}
            <div id="wrap-flags"
                 class="hidden bg-blue-50 rounded-2xl border border-blue-100 p-4">
                <p class="text-[10px] font-black text-blue-700   mb-2">Info Jabatan</p>
                <div id="isi-flags" class="space-y-1.5"></div>
            </div>

            {{-- Password --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-4
                           flex items-center gap-2">
                    <span class="w-1 h-4 bg-green-500 rounded-full"></span>
                    Password Awal
                </h3>
                <div>
                    <label class="block text-[11px] font-black text-gray-500
                                    mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="password" value="{{ old('password') }}"
                           placeholder="Min. 6 karakter"
                           class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                  text-gray-700 placeholder-gray-300 outline-none transition-all
                                  @error('password') border-red-400 bg-red-50
                                  @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                  @enderror">
                    @error('password')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-[9px] text-gray-400">
                        Berikan password ini ke karyawan. Bisa diubah setelah login.
                    </p>
                </div>
            </div>

            {{-- Tombol --}}
            <button type="submit"
                    class="w-full bg-[#1E3A5F] hover:bg-green-600 text-white font-black
                           text-sm   py-4 rounded-2xl transition-all
                           shadow-sm active:scale-95 italic flex items-center justify-center gap-2">
                <i data-lucide="save" class="w-5 h-5"></i>
                Simpan Karyawan
            </button>

            <a href="{{ route('admin.karyawan.index') }}"
               class="block text-center text-xs font-bold text-gray-400
                      hover:text-red-500 transition-colors">
                Batal
            </a>

        </div>
    </div>

</form>

{{-- PINDAHKAN SCRIPT KE SINI BIAR LANGSUNG JALAN --}}
<script>
try {
    // Baca data PHP
    var PHP_DATA = JSON.parse(document.getElementById('data-php').textContent);
    console.log('Sistem CBN: Form Karyawan Loaded', PHP_DATA);

    var DIV_TETAP    = PHP_DATA.divisiTetap || {};
    var DIV_KONTRAK  = PHP_DATA.divisiKontrak || {};
    var JAB_MAP      = PHP_DATA.jabatanMap || {};
    var DOK_WAJIB    = PHP_DATA.dokumenWajib || {};
    var SHIFT_JAB    = PHP_DATA.jabatanShift || [];
    var UMR_JAB      = PHP_DATA.jabatanAtasUmr || [];

    // Elemen
    var sJns = document.getElementById('sel-jenis');
    var sDiv = document.getElementById('sel-divisi');
    var sJab = document.getElementById('sel-jabatan');
    var pNip = document.getElementById('preview-username');
    var wDok = document.getElementById('wrap-dokumen');
    var tDok = document.getElementById('teks-dokumen');
    var iDok = document.getElementById('input-jenis-dokumen');
    var wFlg = document.getElementById('wrap-flags');
    var iFlg = document.getElementById('isi-flags');
    var tInf = document.getElementById('teks-info-divisi');

    function fillSel(el, data, placeholder) {
        el.innerHTML = '';
        var o = document.createElement('option');
        o.value = ''; o.textContent = placeholder;
        el.appendChild(o);
        if (Array.isArray(data)) {
            data.forEach(function(v) {
                var opt = document.createElement('option');
                opt.value = v; opt.textContent = v;
                el.appendChild(opt);
            });
        } else {
            Object.keys(data).forEach(function(k) {
                var opt = document.createElement('option');
                opt.value = k; opt.textContent = data[k];
                el.appendChild(opt);
            });
        }
    }

    sJns.addEventListener('change', function() {
        var opt  = this.options[this.selectedIndex];
        var slug = opt ? opt.getAttribute('data-slug') : '';
        fillSel(sDiv, [], '-- Pilih Divisi --');
        fillSel(sJab, [], '-- Pilih Jabatan --');
        wDok.classList.add('hidden'); wFlg.classList.add('hidden');
        pNip.textContent = '—'; tInf.textContent = '';
        if (!slug) return;
        if (slug === 'karyawan_tetap') {
            fillSel(sDiv, DIV_TETAP, '-- Pilih Divisi --');
            tInf.textContent = 'Divisi internal PT Citra Bangun Nagari';
        } else if (slug === 'karyawan_kontrak') {
            fillSel(sDiv, DIV_KONTRAK, '-- Pilih Divisi --');
            tInf.textContent = 'Divisi mitra alih daya (HC/Umum)';
        }
    });

    sDiv.addEventListener('change', function() {
        var d = this.value;
        fillSel(sJab, [], '-- Pilih Jabatan --');
        wDok.classList.add('hidden'); wFlg.classList.add('hidden');
        pNip.textContent = '—';
        if (!d) return;
        var list = JAB_MAP[d] || [];
        fillSel(sJab, list, '-- Pilih Jabatan --');
        
        var opt  = sJns.options[sJns.selectedIndex];
        var slug = opt ? opt.getAttribute('data-slug') : '';
        var prefix = '';
        if (slug === 'karyawan_tetap') prefix = 'KT-CBN';
        else if (d === 'HC') prefix = 'KK-HC';
        else if (d === 'umum') prefix = 'KK-UM';
        pNip.textContent = prefix ? prefix + '-XXXX' : '—';
    });

    sJab.addEventListener('change', function() {
        var j = this.value;
        wDok.classList.add('hidden'); wFlg.classList.add('hidden');
        if (!j) return;
        if (DOK_WAJIB[j]) {
            tDok.textContent = DOK_WAJIB[j].keterangan;
            iDok.value = DOK_WAJIB[j].jenis;
            wDok.classList.remove('hidden');
        }
        
        var f = [];
        if (SHIFT_JAB.indexOf(j) !== -1) f.push('⚡ Bersifat shift');
        if (UMR_JAB.indexOf(j) !== -1) f.push('💰 Gaji di atas UMR');
        if (f.length > 0) {
            iFlg.innerHTML = f.map(function(t){ return '<p class="text-[9px] text-blue-700 font-semibold">'+t+'</p>'; }).join('');
            wFlg.classList.remove('hidden');
        }
    });

    // Inisialisasi awal (jika ada old value / browser restore state)
    if (sJns.value) {
        sJns.dispatchEvent(new Event('change'));
        if (PHP_DATA.oldDivisi) {
            sDiv.value = PHP_DATA.oldDivisi;
            sDiv.dispatchEvent(new Event('change'));
            if (PHP_DATA.oldJabatan) {
                sJab.value = PHP_DATA.oldJabatan;
                sJab.dispatchEvent(new Event('change'));
            }
        }
    }

} catch (err) {
    console.error('Sistem CBN Error:', err);
}
</script>

@endsection

