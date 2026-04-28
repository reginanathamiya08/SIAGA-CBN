@extends('admin.sidebar')
@section('title', 'Edit Karyawan')

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('admin.karyawan.show', $karyawan->id) }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight  ">
            Edit: {{ $karyawan->nama }}
        </h1>
        <p class="text-gray-500 mt-1 text-sm">
            Username:
            <strong class="font-mono text-[#1E3A5F]">{{ $karyawan->user->username }}</strong>
            (tidak bisa diubah)
        </p>
    </div>
</header>

<form method="POST" action="{{ route('admin.karyawan.update', $karyawan->id) }}"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="hidden" name="divisi" value="{{ $karyawan->divisi }}">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Kiri ─────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-5
                           flex items-center gap-2">
                    <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                    Informasi Dasar
                </h3>

                {{-- Info jenis & divisi (tidak bisa diubah) --}}
                <div class="grid grid-cols-2 gap-4 mb-5 p-4 bg-gray-50
                            rounded-2xl border border-gray-100">
                    <div>
                        <p class="text-[9px] font-black text-gray-400   mb-1">
                            Jenis Karyawan
                        </p>
                        <p class="text-sm font-black text-gray-700">
                            {{ $karyawan->isTetap() ? 'Karyawan Tetap' : 'Karyawan Kontrak' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-gray-400   mb-1">
                            Divisi
                        </p>
                        <p class="text-sm font-black text-gray-700">{{ $karyawan->labelDivisi() }}</p>
                    </div>
                </div>

                <div class="space-y-4">

                    {{-- Nama --}}
                    <div>
                        <label class="block text-[11px] font-black text-gray-500   mb-2">
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
                        <label class="block text-[11px] font-black text-gray-500   mb-2">
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

                    {{-- Jabatan --}}
                    <div>
                        <label class="block text-[11px] font-black text-gray-500   mb-2">
                            Jabatan <span class="text-red-500">*</span>
                        </label>
                        <select name="jabatan"
                                class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                       text-gray-700 outline-none transition-all
                                       @error('jabatan') border-red-400 bg-red-50
                                       @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                       @enderror">
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
                        <p class="mt-1 text-[9px] text-gray-400">
                            Jenis & divisi tidak bisa diubah. Nonaktifkan & buat akun baru jika diperlukan.
                        </p>
                    </div>

                    {{-- Tanggal Masuk & No HP --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-black text-gray-500   mb-2">
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
                            <label class="block text-[11px] font-black text-gray-500   mb-2">
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
                <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-4
                           flex items-center gap-2">
                    <span class="w-1 h-4 bg-amber-500 rounded-full"></span>
                    Upload Dokumen Tambahan
                    <span class="font-normal text-gray-400 normal-case text-[9px]">(Opsional)</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-black text-gray-500   mb-2">
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
                        <label class="block text-[11px] font-black text-gray-500   mb-2">
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
                <p class="text-[10px] font-black text-blue-700   mb-2">Username Login</p>
                <p class="text-xl font-black text-[#1E3A5F] font-mono tracking-wider">
                    {{ $karyawan->user->username }}
                </p>
                <p class="text-[9px] text-blue-500 font-medium mt-2">
                    Username tidak bisa diubah setelah dibuat.
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

@endsection