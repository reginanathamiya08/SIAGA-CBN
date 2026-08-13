@extends('admin.sidebar')
@section('title', 'Edit Karyawan')

@section('content')

{{-- Data PHP → JavaScript --}}
<script id="data-php" type="application/json">
{
    "divisiList": @json($divisiList),
    "jabatanMap": @json($jabatanMap),
    "currentDivisi": "{{ old('divisi', $karyawan->divisi) }}",
    "currentJabatan": "{{ old('jabatan', $karyawan->jabatan) }}"
}
</script>

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('admin.karyawan.show', $karyawan->id) }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight">
            Edit: {{ $karyawan->nama }}
        </h1>
        <p class="text-gray-500 mt-1 text-sm">
            ID Karyawan:
            <strong class="font-mono text-[#1E3A5F]">{{ $karyawan->nip }}</strong>
            (tidak bisa diubah)
        </p>
    </div>
</header>

<form method="POST" action="{{ route('admin.karyawan.update', $karyawan->id) }}"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Kiri ─────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-black text-[#1E3A5F] italic text-[11px] mb-5
                           flex items-center gap-2">
                    <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                    Informasi Dasar
                </h3>

                {{-- Info Jenis Karyawan (tidak bisa diubah) --}}
                <div class="mb-5 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider mb-1">
                        Jenis Karyawan
                    </p>
                    <p class="text-sm font-black text-gray-700 flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-lg text-xs font-black uppercase
                                     {{ $karyawan->isTetap() ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700' }}">
                            {{ $karyawan->isTetap() ? 'Karyawan Tetap' : 'Karyawan Kontrak' }}
                        </span>
                        <span class="text-xs font-normal text-gray-400">(Jenis karyawan mempengaruhi NIP)</span>
                    </p>
                </div>

                <div class="space-y-4">

                    {{-- Divisi --}}
                    <div>
                        <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2">
                            Divisi <span class="text-red-500">*</span>
                        </label>
                        <select name="divisi" id="sel-divisi"
                                class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                       text-gray-700 outline-none transition-all cursor-pointer
                                       @error('divisi') border-red-400 bg-red-50
                                       @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                       @enderror">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach ($divisiList as $key => $label)
                                <option value="{{ $key }}"
                                        {{ old('divisi', $karyawan->divisi) === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('divisi')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jabatan / Tenaga Kerja --}}
                    <div>
                        <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2">
                            {{ $karyawan->isKontrak() ? 'Tenaga Kerja / Jabatan' : 'Jabatan' }} <span class="text-red-500">*</span>
                        </label>
                        <select name="jabatan" id="sel-jabatan"
                                class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                       text-gray-700 outline-none transition-all cursor-pointer
                                       @error('jabatan') border-red-400 bg-red-50
                                       @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                       @enderror">
                            <option value="">-- Pilih {{ $karyawan->isKontrak() ? 'Tenaga Kerja' : 'Jabatan' }} --</option>
                            @foreach ($daftarJabatan as $jab)
                                <option value="{{ $jab }}"
                                        {{ old('jabatan', $karyawan->jabatan) === $jab ? 'selected' : '' }}>
                                    {{ $jab }}
                                </option>
                            @endforeach
                        </select>
                        @error('jabatan')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[10px] text-gray-400 italic">
                            {{ $karyawan->isKontrak() ? 'Pilihan tenaga kerja disesuaikan dengan divisi (HC atau Umum) untuk karyawan kontrak.' : 'Pilihan jabatan disesuaikan dengan divisi karyawan tetap.' }}
                        </p>
                    </div>

                    {{-- Nama --}}
                    <div>
                        <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama"
                               value="{{ old('nama', $karyawan->nama) }}"
                               class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                      text-gray-700 outline-none transition-all
                                      @error('nama') border-red-400 bg-red-50
                                      @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                      @enderror">
                        @error('nama')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2">
                            Email <span class="text-red-500">*</span>
                            <span class="font-normal text-gray-400 normal-case ml-1">
                                (untuk notifikasi approval)
                            </span>
                        </label>
                        <input type="email" name="email"
                               value="{{ old('email', $karyawan->email) }}"
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

                    {{-- Tamatan / Pendidikan --}}
                    <div>
                        <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2">
                            Tamatan (Pendidikan) <span class="text-red-500">*</span>
                        </label>
                        <select name="pendidikan" required
                                class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                       text-gray-700 outline-none transition-all
                                       @error('pendidikan') border-red-400 bg-red-50
                                       @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                       @enderror">
                            <option value="">-- Pilih Tamatan --</option>
                            <option value="S3" {{ old('pendidikan', $karyawan->pendidikan)==='S3' ? 'selected' : '' }}>S3</option>
                            <option value="S2" {{ old('pendidikan', $karyawan->pendidikan)==='S2' ? 'selected' : '' }}>S2</option>
                            <option value="S1" {{ old('pendidikan', $karyawan->pendidikan)==='S1' ? 'selected' : '' }}>S1</option>
                            <option value="D3" {{ old('pendidikan', $karyawan->pendidikan)==='D3' ? 'selected' : '' }}>D3</option>
                            <option value="SMA/SMK" {{ old('pendidikan', $karyawan->pendidikan)==='SMA/SMK' ? 'selected' : '' }}>SMA/SMK</option>
                        </select>
                        @error('pendidikan')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Masuk & No HP --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2">
                                Tanggal Masuk <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_masuk"
                                   value="{{ old('tanggal_masuk', $karyawan->tanggal_masuk->format('Y-m-d')) }}"
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                          text-gray-700 outline-none transition-all
                                          @error('tanggal_masuk') border-red-400 bg-red-50
                                          @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                          @enderror">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2">
                                No. HP
                            </label>
                            <input type="text" name="no_hp"
                                   value="{{ old('no_hp', $karyawan->no_hp) }}"
                                   placeholder="08xxxxxxxxxx"
                                   class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                          text-gray-700 placeholder-gray-300 outline-none transition-all
                                          border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white">
                        </div>
                    </div>

                </div>
            </div>

            {{-- Upload Dokumen Tambahan --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-black text-[#1E3A5F] italic text-[11px] mb-4
                           flex items-center gap-2">
                    <span class="w-1 h-4 bg-amber-500 rounded-full"></span>
                    Upload Dokumen Tambahan
                    <span class="font-normal text-gray-400 normal-case text-[9px]">(Opsional)</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2">
                            Jenis Dokumen
                        </label>
                        <select name="jenis_dokumen"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50
                                       text-sm font-semibold text-gray-700 outline-none
                                       focus:border-[#1E3A5F] focus:bg-white">
                            <option value="">-- Pilih --</option>
                            <option value="KTA">KTA</option>
                            <option value="SIM">SIM</option>
                            <option value="ijazah">Ijazah</option>
                            <option value="sertifikat">Sertifikat</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2">
                            File <span class="font-normal text-gray-400 normal-case">(PDF/JPG/PNG, maks 2MB)</span>
                        </label>
                        <input type="file" name="file_dokumen"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50
                                      text-sm font-semibold text-gray-700 outline-none">
                        @error('file_dokumen')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Kanan ────────────────────────────────────────────── --}}
        <div class="space-y-5">
            <div class="bg-blue-50 rounded-2xl border border-blue-100 p-5">
                <p class="text-[10px] font-black text-blue-700 uppercase tracking-wider mb-2">ID Karyawan (Login)</p>
                <p class="text-xl font-black text-[#1E3A5F] font-mono tracking-wider">
                    {{ $karyawan->nip }}
                </p>
                <p class="text-[9px] text-blue-500 font-medium mt-2">
                    ID Karyawan tidak bisa diubah setelah dibuat.
                </p>
            </div>

            <button type="submit"
                    class="w-full bg-[#1E3A5F] hover:bg-green-600 text-white font-black text-sm
                             py-4 rounded-2xl transition-all shadow-sm
                           active:scale-95 italic flex items-center justify-center gap-2">
                <i data-lucide="save" class="w-5 h-5"></i>
                Simpan Perubahan
            </button>

            <a href="{{ route('admin.karyawan.show', $karyawan->id) }}"
               class="block text-center text-xs font-bold text-gray-400
                      hover:text-red-500 transition-colors">
                Batal
            </a>
        </div>

    </div>
</form>

<script>
try {
    var PHP_DATA = JSON.parse(document.getElementById('data-php').textContent);
    var JAB_MAP  = PHP_DATA.jabatanMap || {};
    var sDiv     = document.getElementById('sel-divisi');
    var sJab     = document.getElementById('sel-jabatan');

    if (sDiv && sJab) {
        sDiv.addEventListener('change', function() {
            var divVal = this.value;
            var currentJab = sJab.value;
            sJab.innerHTML = '';

            var optPlaceholder = document.createElement('option');
            optPlaceholder.value = '';
            optPlaceholder.textContent = '-- Pilih Position / Tenaga Kerja --';
            sJab.appendChild(optPlaceholder);

            var list = JAB_MAP[divVal] || [];
            list.forEach(function(jab) {
                var opt = document.createElement('option');
                opt.value = jab;
                opt.textContent = jab;
                if (jab === currentJab) {
                    opt.selected = true;
                }
                sJab.appendChild(opt);
            });
        });
    }
} catch(e) {
    console.error('Edit Karyawan JS error:', e);
}
</script>

@endsection
