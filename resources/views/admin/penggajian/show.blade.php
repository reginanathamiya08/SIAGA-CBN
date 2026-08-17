@extends('admin.sidebar')
@section('title','Detail Penggajian')

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('admin.penggajian.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div class="flex-1">
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight uppercase">
            {{ $periodeGaji->nama_periode }}
        </h1>
        <p class="text-gray-500 mt-1 text-sm">
            {{ $periodeGaji->tanggal_mulai->translatedFormat('d F') }} —
            {{ $periodeGaji->tanggal_selesai->translatedFormat('d F Y') }}
            @if ($periodeGaji->finalisasi_at)
                · Diproses {{ $periodeGaji->finalisasi_at->translatedFormat('d F Y, H:i') }}
            @endif
        </p>
    </div>
    <div class="flex items-center gap-3">
        @if ($periodeGaji->isDraft())
            <form method="POST" action="{{ route('admin.penggajian.destroy', $periodeGaji->id) }}" 
                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus periode draft ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="flex items-center gap-2 bg-red-50 hover:bg-red-100 text-red-600 font-black text-xs px-4 py-2.5 rounded-xl transition-all">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Hapus Draft
                </button>
            </form>
        @endif
        <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase
                     {{ $periodeGaji->status === 'final'  ? 'bg-green-100 text-green-700' :
                        ($periodeGaji->status === 'proses' ? 'bg-amber-100 text-amber-700' :
                                                             'bg-gray-100 text-gray-500') }}">
            {{ $periodeGaji->labelStatus() }}
        </span>
    </div>
</header>

@if ($periodeGaji->isDraft())
    @php
        $periodeBulanTahun = $periodeGaji->tanggal_mulai->format('Y-m');
        $sekarangBulanTahun = now()->format('Y-m');
        $batasTanggal = (int)\App\Models\Configuration::getValue('batas_tanggal_gaji', 25);
        $belumBisaProses = ($periodeBulanTahun >= $sekarangBulanTahun && now()->day < $batasTanggal);
    @endphp

    {{-- TAMPILAN JIKA PERIODE DIKEMBALIKAN KARENA REVISI --}}
    @if (isset($slipDitolak) && $slipDitolak->isNotEmpty())
        <div class="mb-6 p-5 bg-red-50 border border-red-200 rounded-3xl">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-red-100 text-red-700 rounded-2xl flex items-center justify-center shrink-0">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-sm font-black text-red-900 uppercase">Revisi Gaji Diperlukan ({{ $slipDitolak->count() }} Karyawan Ditolak)</h4>
                    <p class="text-xs text-red-700 font-semibold mt-1">
                        Pimpinan menolak rekap gaji karyawan berikut. Silakan periksa catatan revisi di bawah, perbaiki data di komponen gaji atau absensi, lalu klik "Proses & Kirim Ke Pimpinan" kembali.
                    </p>
                </div>
            </div>
            <div class="mt-4 overflow-hidden border border-red-100 rounded-2xl bg-white shadow-sm">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-red-50/30 text-[#1E3A5F] border-b border-red-100 font-black uppercase text-[10px]">
                            <th class="px-5 py-3 w-[25%]">Karyawan</th>
                            <th class="px-5 py-3 w-[50%]">Alasan Penolakan dari Pimpinan</th>
                            <th class="px-5 py-3 w-[25%] text-center">Tindakan Revisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-red-50 text-gray-700">
                        @foreach($slipDitolak as $sd)
                            <tr>
                                <td class="px-5 py-3.5 font-bold">
                                    <div class="flex items-center gap-2">
                                        <button type="button" 
                                                onclick="focusAndSearchKaryawan('{{ $sd->karyawan?->nama }}')"
                                                class="text-red-900 hover:text-blue-900 hover:underline block text-left font-black transition-all"
                                                title="Klik untuk mencari karyawan ini di tabel bawah">
                                            {{ $sd->karyawan?->nama }}
                                        </button>
                                        @if ($sd->status === 'direvisi')
                                            <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 text-[8px] font-black rounded-md border border-emerald-200 uppercase tracking-wider shrink-0">Sudah Direvisi</span>
                                        @else
                                            <span class="px-1.5 py-0.5 bg-red-100 text-red-800 text-[8px] font-black rounded-md border border-red-200 uppercase tracking-wider shrink-0">Belum Direvisi</span>
                                        @endif
                                    </div>
                                    <span class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter block mt-0.5">NIP: {{ $sd->karyawan?->nip ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-red-600 font-semibold italic text-xs leading-relaxed">
                                    "{{ $sd->alasan_tolak }}"
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="flex items-center justify-center">
                                        <button type="button" 
                                                onclick="openEditSlipAbsenModal(this)"
                                                data-id="{{ $sd->id }}"
                                                data-nama="{{ $sd->karyawan?->nama }}"
                                                data-hadir="{{ $sd->total_hadir }}"
                                                data-telat="{{ $sd->total_telat }}"
                                                data-alfa="{{ $sd->total_alfa }}"
                                                data-izin="{{ $sd->total_izin }}"
                                                data-cuti="{{ $sd->total_cuti }}"
                                                data-gaji-pokok="{{ $sd->karyawan?->gaji_pokok ?? 0 }}"
                                                data-uang-makan="{{ $sd->karyawan?->uang_makan ?? 0 }}"
                                                data-uang-transport="{{ $sd->karyawan?->uang_transport ?? 0 }}"
                                                data-uang-makan-by-mitra="{{ $sd->karyawan?->uang_makan_by_mitra ? 1 : 0 }}"
                                                class="inline-flex items-center gap-1 px-3 py-2 {{ $sd->status === 'direvisi' ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border-emerald-200 shadow-emerald-500/5' : 'bg-rose-50 hover:bg-rose-100 text-rose-800 border-rose-200' }} rounded-xl text-[11px] font-black transition-all border shadow-sm"
                                                title="Revisi Gaji dan Absensi Karyawan">
                                            <i data-lucide="{{ $sd->status === 'direvisi' ? 'check' : 'edit-3' }}" class="w-3.5 h-3.5"></i>
                                            {{ $sd->status === 'direvisi' ? 'Revisi Lagi' : 'Revisi Gaji & Absen' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- TAMPILAN JIKA PERIODE MASIH DRAFT --}}
    <div class="mb-6 p-5 bg-blue-50 border border-blue-200 rounded-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <i data-lucide="info" class="w-5 h-5 text-blue-600 shrink-0 mt-0.5"></i>
            <div>
                <p class="text-sm font-black text-blue-800">
                    Periode ini masih berupa Draft.
                </p>
                <p class="text-xs text-blue-600 mt-1 max-w-xl">
                    Silakan review daftar karyawan aktif yang akan diproses di bawah. Setelah siap, klik tombol untuk memproses perhitungan awal gaji dan mengirimkannya ke Pimpinan untuk disetujui.
                </p>
                @if ($belumBisaProses)
                    <p class="text-xs text-red-600 mt-2 font-black flex items-center gap-1.5 bg-red-50 border border-red-100 p-2.5 rounded-xl">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                        Tombol dinonaktifkan: Proses penggajian untuk periode berjalan baru dapat dilakukan mulai tanggal {{ $batasTanggal }}.
                    </p>
                @endif
            </div>
        </div>
        <div class="shrink-0">
            @if ($belumBisaProses)
                <button type="button" disabled
                        class="w-full md:w-auto flex items-center justify-center gap-2 bg-gray-300 text-gray-500 font-black text-xs px-5 py-3.5 rounded-xl cursor-not-allowed uppercase"
                        title="Hanya dapat diproses mulai tanggal {{ $batasTanggal }}">
                    <i data-lucide="calculator" class="w-4 h-4"></i>
                    Proses & Kirim Ke Pimpinan
                </button>
            @else
                <form method="POST" action="{{ route('admin.penggajian.hitung', $periodeGaji->id) }}" 
                      onsubmit="return confirm('Apakah Anda yakin ingin memproses penggajian untuk periode ini? Data hasil perhitungan akan dikirimkan ke Pimpinan untuk disetujui.')">
                    @csrf
                    <button type="submit" 
                            class="w-full md:w-auto flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-black text-xs px-5 py-3.5 rounded-xl transition-all shadow-lg shadow-green-600/10 active:scale-95 uppercase">
                        <i data-lucide="calculator" class="w-4 h-4"></i>
                        Proses & Kirim Ke Pimpinan
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if ($karyawanBelumAda > 0)
        <div class="mb-6 p-5 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
            <div>
                <p class="text-sm font-black text-amber-700">
                    {{ $karyawanBelumAda }} karyawan belum memiliki gaji pokok.
                </p>
                <p class="text-xs text-amber-600 mt-1">
                    Karyawan tersebut tidak akan diikutkan dalam kalkulasi penggajian. 
                    <a href="{{ route('admin.komponen-gaji-karyawan.index') }}" class="font-black underline">Atur komponen gaji sekarang →</a>
                </p>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h3 class="font-black text-[#1E3A5F] uppercase italic text-[11px]">
                Daftar Karyawan yang Akan Diproses (Total: {{ $karyawan->count() }} Orang)
            </h3>
            <div class="relative w-64 shrink-0">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                </span>
                <input type="text" 
                       id="search-karyawan-proses" 
                       onkeyup="filterKaryawanProses('search-karyawan-proses', 'table-karyawan-proses')"
                       placeholder="Cari nama atau NIP..."
                       class="pl-9 pr-4 py-2 w-full bg-gray-50 border border-gray-100 rounded-xl text-xs font-bold text-[#1E3A5F] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left" id="table-karyawan-proses">
                <thead>
                    <tr class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-wider border-b border-gray-50 bg-gray-50/50">
                        <th class="px-6 py-4">Karyawan</th>
                        <th class="px-6 py-4">Jabatan</th>
                        <th class="px-6 py-4">Divisi</th>
                        <th class="px-6 py-4">Jenis</th>
                        <th class="px-6 py-4 text-right">Gaji Pokok Basis</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($karyawan as $kar)
                        <tr class="hover:bg-gray-50 transition-all">
                            <td class="px-6 py-4">
                                <p class="text-[11px] font-black text-[#1E3A5F] uppercase emp-name">
                                    {{ $kar->nama }}
                                </p>
                                <p class="text-[9px] text-gray-400 emp-nip">NIP: {{ $kar->nip }}</p>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-gray-700">
                                {{ $kar->jabatan }}
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-gray-700">
                                {{ $kar->labelDivisi() }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase {{ $kar->isTetap() ? 'bg-blue-50 text-blue-600' : 'bg-red-50 text-red-600' }}">
                                    {{ $kar->isTetap() ? 'Tetap' : 'Kontrak' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-black text-[#1E3A5F]">
                                Rp {{ number_format($kar->komponenGaji->gaji_pokok, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-400">
                                Tidak ada karyawan aktif yang memiliki komponen gaji lengkap.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    {{-- TAMPILAN JIKA PERIODE SUDAH FINAL ATAU PROSES --}}
    @if ($periodeGaji->isProses())
        <div class="mb-6 p-5 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
            <div>
                <p class="text-sm font-black text-amber-800">
                    Menunggu Persetujuan Pimpinan
                </p>
                <p class="text-xs text-amber-600 mt-1 max-w-xl">
                    Periode penggajian ini telah dihitung dan saat ini sedang menunggu persetujuan (approval) dari Pimpinan sebelum slip resmi diterbitkan ke Karyawan.
                </p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['Karyawan',       $ringkasan['total_karyawan'],    'text-[#1E3A5F]', ''],
            ['Total Pendapatan',number_format($ringkasan['total_pendapatan'],0,',','.'),'text-green-600','Rp '],
            ['Total Potongan',  number_format($ringkasan['total_potongan'],0,',','.'),  'text-red-500',  'Rp '],
            ['Total Dibayar',   number_format($ringkasan['total_gaji_bersih'],0,',','.'), 'text-[#1E3A5F]', 'Rp '],
        ] as [$label, $val, $color, $prefix])
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ $label }}</p>
                <p class="text-sm font-black {{ $color }}">{{ $prefix }}{{ $val }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h3 class="font-black text-[#1E3A5F] uppercase italic text-[11px]">
                Rincian Slip Gaji Karyawan (Total: {{ $ringkasan['total_karyawan'] ?? 0 }} Orang)
            </h3>
            <div class="relative w-64 shrink-0">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                </span>
                <input type="text" 
                       id="search-karyawan-rekap" 
                       onkeyup="filterKaryawanProses('search-karyawan-rekap', 'table-karyawan-rekap')"
                       placeholder="Cari nama atau NIP..."
                       class="pl-9 pr-4 py-2 w-full bg-gray-50 border border-gray-100 rounded-xl text-xs font-bold text-[#1E3A5F] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left" id="table-karyawan-rekap">
                <thead>
                    <tr class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-wider
                               border-b border-gray-50 bg-gray-50/50">
                        <th class="px-6 py-4">Karyawan</th>
                        <th class="px-6 py-4 text-center">Hadir</th>
                        <th class="px-6 py-4 text-center">Telat</th>
                        <th class="px-6 py-4 text-center">Alfa</th>
                        <th class="px-6 py-4 text-center">Cuti</th>
                        <th class="px-6 py-4 text-right">Gaji Pokok</th>
                        <th class="px-6 py-4 text-right">Potongan</th>
                        <th class="px-6 py-4 text-right">Gaji Bersih</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($slipGaji as $slip)
                        <tr class="hover:bg-gray-50 transition-all">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <p class="text-[11px] font-black text-[#1E3A5F] uppercase emp-name">
                                        {{ $slip->karyawan->nama }}
                                    </p>
                                    @if ($slip->status === 'direvisi')
                                        <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 text-[8px] font-black rounded-md border border-emerald-200 uppercase tracking-wider shrink-0">Revised</span>
                                    @elseif ($slip->status === 'ditolak')
                                        <span class="px-1.5 py-0.5 bg-red-100 text-red-800 text-[8px] font-black rounded-md border border-red-200 uppercase tracking-wider shrink-0">Rejected</span>
                                    @endif
                                </div>
                                <p class="text-[9px] text-gray-400 emp-nip">NIP: {{ $slip->karyawan->nip }} | {{ $slip->karyawan->jabatan }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-black text-green-600">{{ $slip->total_hadir }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-black {{ $slip->total_telat > 0 ? 'text-amber-600' : 'text-gray-300' }}">
                                    {{ $slip->total_telat }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-black {{ $slip->total_alfa > 0 ? 'text-red-500' : 'text-gray-300' }}">
                                    {{ $slip->total_alfa }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-black {{ $slip->total_cuti > 0 ? 'text-indigo-600' : 'text-gray-300' }}">
                                    {{ $slip->total_cuti }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-semibold text-gray-700">
                                Rp {{ number_format($slip->getNominal('Gaji Pokok'), 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-semibold text-red-500">
                                Rp {{ number_format($slip->total_potongan, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-black text-[#1E3A5F]">
                                    Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.penggajian.slip', $slip->id) }}"
                                       class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all inline-flex"
                                       title="Lihat Slip">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                    </a>
                                    @if ($periodeGaji->status !== 'diterbitkan')
                                        <button type="button"
                                                onclick="openEditSlipAbsenModal(this)"
                                                data-id="{{ $slip->id }}"
                                                data-nama="{{ $slip->karyawan?->nama }}"
                                                data-hadir="{{ $slip->total_hadir }}"
                                                data-telat="{{ $slip->total_telat }}"
                                                data-alfa="{{ $slip->total_alfa }}"
                                                data-izin="{{ $slip->total_izin }}"
                                                data-cuti="{{ $slip->total_cuti }}"
                                                data-gaji-pokok="{{ $slip->karyawan?->gaji_pokok ?? 0 }}"
                                                data-uang-makan="{{ $slip->karyawan?->uang_makan ?? 0 }}"
                                                data-uang-transport="{{ $slip->karyawan?->uang_transport ?? 0 }}"
                                                data-uang-makan-by-mitra="{{ $slip->karyawan?->uang_makan_by_mitra ? 1 : 0 }}"
                                                data-potongan-pinjaman="{{ $slip->getNominal('Potongan Pinjaman') }}"
                                                class="p-2 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-all inline-flex"
                                                title="Revisi Gaji &amp; Absen">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-sm text-gray-400">
                                Tidak ada data slip gaji.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($slipGaji->hasPages())
            <div class="px-6 py-4 border-t border-gray-50">{{ $slipGaji->links() }}</div>
        @endif
    </div>
@endif

@push('scripts')
<script>
    function filterKaryawanProses(inputId, tableId) {
        const input = document.getElementById(inputId).value.toLowerCase();
        const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
        rows.forEach(row => {
            const nameEl = row.querySelector('.emp-name');
            const nipEl = row.querySelector('.emp-nip');
            if (!nameEl) return;
            const name = nameEl.textContent.toLowerCase();
            const nip = nipEl ? nipEl.textContent.toLowerCase() : '';
            if (name.includes(input) || nip.includes(input)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function focusAndSearchKaryawan(name) {
        const inputDraft = document.getElementById('search-karyawan-proses');
        const inputRekap = document.getElementById('search-karyawan-rekap');
        
        if (inputDraft) {
            inputDraft.value = name;
            filterKaryawanProses('search-karyawan-proses', 'table-karyawan-proses');
            inputDraft.scrollIntoView({ behavior: 'smooth', block: 'center' });
            inputDraft.focus();
        } else if (inputRekap) {
            inputRekap.value = name;
            filterKaryawanProses('search-karyawan-rekap', 'table-karyawan-rekap');
            inputRekap.scrollIntoView({ behavior: 'smooth', block: 'center' });
            inputRekap.focus();
        }
    }
    function openEditSlipAbsenModal(button) {
        const id = button.getAttribute('data-id');
        const nama = button.getAttribute('data-nama');
        const hadir = button.getAttribute('data-hadir');
        const telat = button.getAttribute('data-telat');
        const alfa = button.getAttribute('data-alfa');
        const izin = button.getAttribute('data-izin');
        const cuti = button.getAttribute('data-cuti');
        const gajiPokok = button.getAttribute('data-gaji-pokok');
        const uangMakan = button.getAttribute('data-uang-makan');
        const uangTransport = button.getAttribute('data-uang-transport');
        const uangMakanByMitra = button.getAttribute('data-uang-makan-by-mitra');

        const potonganPinjaman = button.getAttribute('data-potongan-pinjaman') || 0;

        const modal = document.getElementById('modal-edit-slip-absen');
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        document.getElementById('edit-slip-nama').textContent = '— ' + nama;
        const form = document.getElementById('form-edit-slip-absen');
        form.action = `/admin/penggajian/slip/${id}/update-absensi`;

        document.getElementById('edit-slip-hadir').value = hadir;
        document.getElementById('edit-slip-telat').value = telat;
        document.getElementById('edit-slip-alfa').value = alfa;
        document.getElementById('edit-slip-izin').value = izin;
        document.getElementById('edit-slip-cuti').value = cuti;

        // Salary components
        document.getElementById('edit-slip-gaji-pokok').value = gajiPokok;
        document.getElementById('edit-slip-uang-makan').value = uangMakan;
        document.getElementById('edit-slip-uang-transport').value = uangTransport;
        document.getElementById('edit-slip-potongan-pinjaman').value = potonganPinjaman;

        if (uangMakanByMitra == 1) {
            document.getElementById('edit-slip-allowances-container').classList.add('hidden');
            document.getElementById('edit-slip-mitra-badge').classList.remove('hidden');
            document.getElementById('edit-slip-uang-makan').required = false;
            document.getElementById('edit-slip-uang-transport').required = false;
        } else {
            document.getElementById('edit-slip-allowances-container').classList.remove('hidden');
            document.getElementById('edit-slip-mitra-badge').classList.add('hidden');
            document.getElementById('edit-slip-uang-makan').required = true;
            document.getElementById('edit-slip-uang-transport').required = true;
        }

        modal.classList.remove('hidden');
        modal.style.display = 'flex';
    }

    function closeSlipAbsenModal() {
        const modal = document.getElementById('modal-edit-slip-absen');
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }
</script>
@endpush

{{-- MODAL EDIT SLIP ABSENSI & GAJI --}}
<div id="modal-edit-slip-absen" class="fixed inset-0 w-screen h-screen z-[9999] hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 max-w-2xl w-full overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-black text-slate-800 uppercase tracking-tight text-sm">Revisi Gaji &amp; Absen Karyawan <span id="edit-slip-nama"></span></h3>
            <button onclick="closeSlipAbsenModal()" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" id="form-edit-slip-absen" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Column 1: Absensi -->
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-1.5 flex items-center gap-1.5">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-blue-600"></i> Detail Absensi Bulanan
                    </h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Total Hadir (Hari)</label>
                            <input type="number" name="total_hadir" id="edit-slip-hadir" min="0" required
                                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Total Telat (Hari)</label>
                            <input type="number" name="total_telat" id="edit-slip-telat" min="0" required
                                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Total Alfa (Hari)</label>
                            <input type="number" name="total_alfa" id="edit-slip-alfa" min="0" required
                                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Total Izin/Sakit</label>
                            <input type="number" name="total_izin" id="edit-slip-izin" min="0" required
                                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Total Cuti (Hari)</label>
                        <input type="number" name="total_cuti" id="edit-slip-cuti" min="0" required
                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                </div>

                <!-- Column 2: Gaji Karyawan -->
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-1.5 flex items-center gap-1.5">
                        <i data-lucide="credit-card" class="w-3.5 h-3.5 text-emerald-600"></i> Komponen Gaji Karyawan
                    </h4>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Gaji Pokok (Rp)</label>
                        <input type="number" name="gaji_pokok" id="edit-slip-gaji-pokok" min="0" required
                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    </div>
                    
                    <div id="edit-slip-allowances-container" class="space-y-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Uang Makan Harian (Rp)</label>
                            <input type="number" name="uang_makan" id="edit-slip-uang-makan" min="0"
                                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Uang Transport Harian (Rp)</label>
                            <input type="number" name="uang_transport" id="edit-slip-uang-transport" min="0"
                                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Potongan Pinjaman Lainnya (Rp)</label>
                        <input type="number" name="potongan_pinjaman" id="edit-slip-potongan-pinjaman" min="0" value="0"
                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-400">
                        <p class="text-[9px] text-gray-400 mt-1">* Potongan pinjaman kasbon berdasarkan informasi dari Mitra</p>
                    </div>
                    
                    <div id="edit-slip-mitra-badge" class="hidden p-3 bg-slate-50 border border-slate-100 rounded-xl text-center">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Uang Makan &amp; Transport</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Dibayarkan langsung oleh Mitra (bukan CBN)</p>
                    </div>
                </div>
            </div>

            <p class="text-[10px] text-gray-400 font-medium pt-2">
                * Catatan: Menyimpan perubahan akan memperbarui data absensi bulanan dan profil komponen gaji master karyawan, lalu menghitung ulang nominal slip gaji secara otomatis.
            </p>

            <div class="flex gap-3 pt-3 border-t border-slate-50">
                <button type="button" onclick="closeSlipAbsenModal()"
                        class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold px-4 py-2.5 rounded-xl transition text-sm">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2.5 rounded-xl transition text-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
