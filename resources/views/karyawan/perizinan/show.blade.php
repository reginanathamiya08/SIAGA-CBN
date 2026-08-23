@extends('karyawan.sidebar')
@section('title', 'Detail Perizinan')

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('karyawan.perizinan.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] ">Detail Perizinan</h1>
        <p class="text-gray-500 mt-1 text-sm">{{ $perizinan->labelJenis() }}</p>
    </div>
</header>

<div class="max-w-2xl">
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Status Banner --}}
        @php
            $bannerClass = match($perizinan->status_approval) {
                'disetujui'           => 'bg-green-500',
                'ditolak'             => 'bg-red-500',
                'menunggu_rekan'      => 'bg-indigo-500',
                'menunggu_form_mitra' => 'bg-[#1E3A5F]',
                default               => 'bg-amber-500',
            };
            $bannerLabel = match($perizinan->status_approval) {
                'disetujui'           => '✅ Pengajuan Disetujui',
                'ditolak'             => '❌ Pengajuan Ditolak',
                'menunggu_rekan'      => '⏳ Menunggu Persetujuan Rekan Kerja',
                'menunggu_form_mitra' => '⏳ Belum Mengunggah Form Cuti Mitra',
                default               => '⏳ Menunggu Persetujuan Pimpinan',
            };
        @endphp
        <div class="{{ $bannerClass }} px-6 py-4 text-white">
            <p class="font-black text-sm ">{{ $bannerLabel }}</p>
            @if ($perizinan->approved_at)
                <p class="text-[10px] text-white/70 mt-0.5">
                    {{ $perizinan->approved_at->translatedFormat('d F Y, H:i') }} WIB
                </p>
            @endif
        </div>

        <div class="p-6 space-y-4">

            @if ($perizinan->status_approval === 'menunggu_form_mitra')
                <div class="p-4 bg-blue-50 border border-blue-100 rounded-2xl mb-4 space-y-2.5">
                    <h3 class="text-[10px] font-black text-blue-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        Langkah Selanjutnya:
                    </h3>
                    <ol class="list-decimal list-inside text-xs font-bold text-blue-700 space-y-1.5 leading-relaxed">
                        <li>Cetak berkas permohonan dengan menekan tombol <strong class="text-blue-900">"Cetak Surat Permohonan Cuti"</strong> di bawah.</li>
                        <li>Tanda tangani berkas tersebut secara manual/fisik bersama Pimpinan Mitra Penempatan Anda.</li>
                        <li>Scan atau foto lembaran berkas yang sudah ditandatangani dan dibubuhi cap/stempel Mitra.</li>
                        <li>Unggah file scan/foto tersebut pada form unggahan di bawah ini, lalu klik <strong class="text-blue-900">"Kirim ke Pimpinan CBN"</strong>.</li>
                    </ol>
                </div>
            @endif

            @foreach ([
                ['Jenis Izin',       $perizinan->labelJenis()],
                ['Tanggal Mulai',    $perizinan->tanggal_mulai->translatedFormat('d F Y')],
                ['Tanggal Selesai',  $perizinan->tanggal_selesai->translatedFormat('d F Y')],
                ['Jumlah Hari',      $perizinan->jumlah_hari . ' hari'],
                ['Keterangan',       $perizinan->keterangan ?? '-'],
                ['Diajukan Pada',    $perizinan->created_at->translatedFormat('d F Y, H:i')],
            ] as [$label, $value])
                <div class="flex justify-between items-start border-b border-gray-50 pb-3 last:border-0 last:pb-0">
                    <span class="text-[10px] font-black text-gray-400  tracking-widest w-36 shrink-0">
                        {{ $label }}
                    </span>
                    <span class="text-sm font-semibold text-gray-700 text-right">{{ $value }}</span>
                </div>
            @endforeach

            {{-- Diketahui Oleh Rekan Kerja (jika ada) --}}
            @if ($perizinan->rekan_kerja_id)
                <div class="flex justify-between items-start border-b border-gray-50 pb-3">
                    <span class="text-[10px] font-black text-gray-400 tracking-widest w-36 shrink-0">
                        Diketahui Oleh
                    </span>
                    <div class="text-sm font-semibold text-gray-700 text-right">
                        <p>{{ $perizinan->rekanKerja?->nama }}</p>
                        @if ($perizinan->status_rekan === 'menunggu')
                            <span class="px-2 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-700 mt-1 inline-block">⏳ Menunggu Konfirmasi Rekan</span>
                        @elseif ($perizinan->status_rekan === 'disetujui')
                            <span class="px-2 py-0.5 rounded text-[9px] font-black bg-green-100 text-green-700 mt-1 inline-block">✅ Disetujui Rekan</span>
                        @elseif ($perizinan->status_rekan === 'ditolak')
                            <span class="px-2 py-0.5 rounded text-[9px] font-black bg-red-100 text-red-700 mt-1 inline-block">❌ Ditolak Rekan</span>
                        @endif
                    </div>
                </div>
            @endif

            {{-- File Bukti --}}
            @if ($perizinan->file_bukti)
                <div class="flex justify-between items-center border-b border-gray-50 pb-3">
                    <span class="text-[10px] font-black text-gray-400  tracking-widest w-36 shrink-0">
                        {{ $perizinan->karyawan->isKaryawanKontrak() && $perizinan->jenisPerizinan?->slug === 'cuti' ? 'Form Cuti Kontrak' : 'Surat Dokter / Bukti' }}
                    </span>
                    <a href="{{ Storage::url($perizinan->file_bukti) }}" target="_blank"
                       class="flex items-center gap-2 text-blue-600 font-semibold text-sm hover:text-blue-800">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        Lihat File
                    </a>
                </div>
            @endif

            {{-- Alasan Tolak --}}
            @if ($perizinan->status_approval === 'ditolak' && $perizinan->alasan_tolak)
                <div class="p-4 bg-red-50 rounded-2xl border border-red-100">
                    <p class="text-[10px] font-black text-red-700  mb-1">Alasan Penolakan</p>
                    <p class="text-sm font-semibold text-red-600">{{ $perizinan->alasan_tolak }}</p>
                </div>
            @endif

            {{-- Cetak Surat Cuti jika jenisnya cuti --}}
            @if ($perizinan->jenisPerizinan?->slug === 'cuti')
                <div class="pt-2">
                    <a href="{{ route('karyawan.perizinan.print', $perizinan->id) }}" target="_blank"
                       class="w-full bg-[#1E3A5F] hover:bg-opacity-90 text-white font-black
                              text-xs uppercase tracking-wider py-3.5 rounded-2xl transition-all
                              flex items-center justify-center gap-2 shadow-md">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Cetak Surat Permohonan Cuti
                    </a>
                </div>
            @endif

            {{-- Form Unggah untuk status menunggu_form_mitra --}}
            @if ($perizinan->status_approval === 'menunggu_form_mitra')
                <form method="POST" action="{{ route('karyawan.perizinan.upload-mitra', $perizinan->id) }}" enctype="multipart/form-data" class="pt-4 border-t border-gray-100 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-[#1E3A5F] uppercase tracking-widest mb-2">
                            Unggah Scan/Foto Form Cuti Mitra <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="file_bukti" required accept=".pdf,.jpg,.jpeg,.png"
                               class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all border border-gray-100 rounded-2xl p-2 bg-gray-50">
                    </div>
                    <button type="submit"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black
                                   text-xs uppercase tracking-wider py-3.5 rounded-2xl transition-all
                                   flex items-center justify-center gap-2 shadow-md">
                        <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                        Kirim ke Pimpinan CBN
                    </button>
                </form>
            @endif

            {{-- Batalkan jika masih menunggu rekan, pimpinan, atau form mitra --}}
            @if (in_array($perizinan->status_approval, ['menunggu', 'menunggu_rekan', 'menunggu_form_mitra']))
                <form method="POST"
                      action="{{ route('karyawan.perizinan.destroy', $perizinan->id) }}"
                      onsubmit="return confirm('Batalkan pengajuan ini?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full mt-2 bg-red-50 hover:bg-red-100 text-red-600 font-black
                                   text-xs  italic py-3 rounded-2xl transition-all
                                   flex items-center justify-center gap-2">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                        Batalkan Pengajuan
                    </button>
                </form>
            @endif

        </div>
    </div>
</div>

@endsection
