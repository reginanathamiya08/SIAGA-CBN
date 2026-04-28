@extends('pimpinan.sidebar')
@section('title', 'Approval Pengajuan')

@section('content')

<header class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight uppercase">Approval Pengajuan</h1>
        <p class="text-gray-500 mt-1 text-sm">Perizinan, Lembur, dan Dinas Luar Kota</p>
    </div>
</header>

{{-- Tab navigasi --}}
<div class="flex gap-2 mb-6" x-data>
    @foreach ([
        ['perizinan', 'Perizinan', $jumlahMenunggu['perizinan']],
        ['lembur',    'Lembur',    $jumlahMenunggu['lembur']],
        ['dinas_luar','Dinas Luar',$jumlahMenunggu['dinas_luar']],
    ] as [$val, $label, $jumlah])
        <a href="{{ route('pimpinan.approval.index', ['tipe' => $val, 'status' => request('status','menunggu')]) }}"
           class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-black text-xs uppercase
                  italic transition-all
                  {{ $tipe === $val
                       ? 'bg-[#1E3A5F] text-white shadow-sm'
                       : 'bg-white border border-gray-200 text-gray-500 hover:border-[#1E3A5F] hover:text-[#1E3A5F]' }}">
            {{ $label }}
            @if ($jumlah > 0)
                <span class="{{ $tipe === $val ? 'bg-white text-[#1E3A5F]' : 'bg-red-500 text-white' }}
                              text-[8px] font-black px-1.5 py-0.5 rounded-full">
                    {{ $jumlah }}
                </span>
            @endif
        </a>
    @endforeach
</div>

{{-- Filter status --}}
<div class="flex gap-2 mb-5">
    @foreach (['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'semua' => 'Semua'] as $val => $label)
        <a href="{{ route('pimpinan.approval.index', ['tipe' => $tipe, 'status' => $val]) }}"
           class="px-4 py-2 rounded-xl text-[10px] font-black uppercase italic transition-all
                  {{ request('status', 'menunggu') === $val
                       ? 'bg-gray-800 text-white'
                       : 'bg-white border border-gray-200 text-gray-500 hover:border-gray-400' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- ── PERIZINAN ──────────────────────────────────────────────────── --}}
@if ($tipe === 'perizinan')
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-wider
                               border-b border-gray-50 bg-gray-50/50">
                        <th class="px-6 py-4">Karyawan</th>
                        <th class="px-6 py-4">Jenis</th>
                        <th class="px-6 py-4">Periode</th>
                        <th class="px-6 py-4 text-center">Hari</th>
                        <th class="px-6 py-4 text-center">Bukti</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($perizinan as $p)
                        <tr class="hover:bg-gray-50 transition-all" x-data="{ showTolak: false }">
                            <td class="px-6 py-4">
                                <p class="text-[11px] font-black text-[#1E3A5F] uppercase">{{ $p->karyawan->nama }}</p>
                                <p class="text-[9px] text-gray-400">{{ $p->karyawan->jabatan }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $badge = match($p->jenis_izin) {
                                        'cuti'           => 'bg-indigo-100 text-indigo-700',
                                        'izin_pribadi'   => 'bg-blue-100 text-blue-700',
                                        'sakit_surat'    => 'bg-purple-100 text-purple-700',
                                        'sakit_no_surat' => 'bg-orange-100 text-orange-700',
                                        default          => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase {{ $badge }}">
                                    {{ $p->labelJenis() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-[11px] font-semibold text-gray-700">
                                    {{ $p->tanggal_mulai->format('d M Y') }}
                                </p>
                                @if ($p->tanggal_mulai != $p->tanggal_selesai)
                                    <p class="text-[9px] text-gray-400">s/d {{ $p->tanggal_selesai->format('d M Y') }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-black text-gray-700">{{ $p->jumlah_hari }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($p->file_bukti)
                                    <a href="{{ Storage::url($p->file_bukti) }}" target="_blank"
                                       class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100
                                              transition-all inline-flex" title="Lihat surat">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                    </a>
                                @else
                                    <span class="text-[9px] text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusBadge = match($p->status_approval) {
                                        'menunggu'  => 'bg-yellow-100 text-yellow-700',
                                        'disetujui' => 'bg-green-100 text-green-700',
                                        'ditolak'   => 'bg-red-100 text-red-700',
                                        default     => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase {{ $statusBadge }}">
                                    {{ ucfirst($p->status_approval) }}
                                </span>
                                @if ($p->status_approval === 'ditolak' && $p->alasan_tolak)
                                    <p class="text-[8px] text-red-400 mt-0.5 max-w-24 mx-auto truncate"
                                       title="{{ $p->alasan_tolak }}">
                                        {{ $p->alasan_tolak }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($p->status_approval === 'menunggu')
                                    <div class="flex flex-col gap-1.5">
                                        {{-- Setuju --}}
                                        <form method="POST"
                                              action="{{ route('pimpinan.approval.perizinan.setuju', $p->id) }}"
                                              onsubmit="return confirm('Setujui pengajuan ini?')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="w-full flex items-center justify-center gap-1 px-3 py-1.5
                                                           rounded-lg bg-green-100 text-green-700 hover:bg-green-200
                                                           font-black text-[9px] uppercase italic transition-all">
                                                <i data-lucide="check" class="w-3 h-3"></i> Setuju
                                            </button>
                                        </form>

                                        {{-- Tolak --}}
                                        <button @click="showTolak = !showTolak" type="button"
                                                class="w-full flex items-center justify-center gap-1 px-3 py-1.5
                                                       rounded-lg bg-red-100 text-red-600 hover:bg-red-200
                                                       font-black text-[9px] uppercase italic transition-all">
                                            <i data-lucide="x" class="w-3 h-3"></i> Tolak
                                        </button>
                                    </div>

                                    {{-- Form alasan tolak --}}
                                    <div x-show="showTolak" x-transition @click.away="showTolak = false"
                                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                                        <div class="bg-white rounded-3xl shadow-xl p-6 w-96" @click.stop>
                                            <h3 class="font-black text-[#1E3A5F] text-sm uppercase mb-1">
                                                Tolak Pengajuan
                                            </h3>
                                            <p class="text-xs text-gray-400 mb-4">
                                                {{ $p->karyawan->nama }} — {{ $p->labelJenis() }}
                                            </p>
                                            <form method="POST"
                                                  action="{{ route('pimpinan.approval.perizinan.tolak', $p->id) }}">
                                                @csrf @method('PATCH')
                                                <div class="mb-4">
                                                    <label class="block text-[11px] font-black text-gray-500
                                                                  uppercase tracking-widest mb-2">
                                                        Alasan Penolakan <span class="text-red-500">*</span>
                                                    </label>
                                                    <textarea name="alasan_tolak" rows="3"
                                                              placeholder="Tuliskan alasan penolakan (minimal 10 karakter)..."
                                                              class="w-full px-4 py-3 rounded-xl border border-gray-200
                                                                     bg-gray-50 text-sm font-semibold text-gray-700
                                                                     placeholder-gray-300 outline-none resize-none
                                                                     focus:border-red-400 focus:bg-white"></textarea>
                                                    @error('alasan_tolak')
                                                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                                <div class="flex gap-3">
                                                    <button type="submit"
                                                            class="flex-1 bg-red-600 hover:bg-red-700 text-white
                                                                   font-black text-xs uppercase italic py-3
                                                                   rounded-xl transition-all">
                                                        Tolak Pengajuan
                                                    </button>
                                                    <button @click="showTolak = false" type="button"
                                                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-600
                                                                   font-black text-xs uppercase italic py-3
                                                                   rounded-xl transition-all">
                                                        Batal
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-[9px] text-gray-300 font-semibold">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <i data-lucide="check-circle" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                                <p class="text-sm text-gray-400 font-semibold">
                                    Tidak ada pengajuan perizinan
                                    {{ request('status','menunggu') === 'menunggu' ? 'yang menunggu' : '' }}.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($perizinan->hasPages())
            <div class="px-6 py-4 border-t border-gray-50">
                {{ $perizinan->links() }}
            </div>
        @endif
    </div>
@endif

{{-- ── LEMBUR ──────────────────────────────────────────────────────── --}}
@if ($tipe === 'lembur')
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-wider border-b border-gray-50 bg-gray-50/50">
                        <th class="px-6 py-4">Karyawan</th>
                        <th class="px-6 py-4">Tanggal & Jam</th>
                        <th class="px-6 py-4 text-center">Durasi</th>
                        <th class="px-6 py-4">Keterangan</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($lembur as $l)
                        <tr class="hover:bg-gray-50 transition-all" x-data="{ showTolak: false }">
                            <td class="px-6 py-4">
                                <p class="text-[11px] font-black text-[#1E3A5F] uppercase">{{ $l->karyawan->nama }}</p>
                                <p class="text-[9px] text-gray-400">{{ $l->karyawan->jabatan }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-[11px] font-semibold text-gray-700">
                                    {{ $l->tanggal->translatedFormat('d M Y') }}
                                </p>
                                <p class="text-[9px] text-gray-400">{{ $l->jam_mulai }} — {{ $l->jam_selesai }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-black text-gray-700">{{ $l->formatDurasi() }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-[11px] text-gray-500 max-w-48 truncate">{{ $l->keterangan ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $sb = match($l->status_approval) {
                                        'menunggu'  => 'bg-yellow-100 text-yellow-700',
                                        'disetujui' => 'bg-green-100 text-green-700',
                                        'ditolak'   => 'bg-red-100 text-red-700',
                                        default     => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase {{ $sb }}">
                                    {{ ucfirst($l->status_approval) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($l->status_approval === 'menunggu')
                                    <div class="flex flex-col gap-1.5">
                                        <form method="POST"
                                              action="{{ route('pimpinan.approval.lembur.setuju', $l->id) }}"
                                              onsubmit="return confirm('Setujui lembur ini?')">
                                            @csrf @method('PATCH')
                                            <button class="w-full flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg bg-green-100 text-green-700 hover:bg-green-200 font-black text-[9px] uppercase italic transition-all">
                                                <i data-lucide="check" class="w-3 h-3"></i> Setuju
                                            </button>
                                        </form>
                                        <button @click="showTolak = true" type="button"
                                                class="w-full flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 font-black text-[9px] uppercase italic transition-all">
                                            <i data-lucide="x" class="w-3 h-3"></i> Tolak
                                        </button>
                                    </div>
                                    <div x-show="showTolak" x-transition @click.away="showTolak = false"
                                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                                        <div class="bg-white rounded-3xl shadow-xl p-6 w-96" @click.stop>
                                            <h3 class="font-black text-[#1E3A5F] text-sm uppercase mb-1">Tolak Lembur</h3>
                                            <p class="text-xs text-gray-400 mb-4">{{ $l->karyawan->nama }} — {{ $l->tanggal->format('d M Y') }}</p>
                                            <form method="POST" action="{{ route('pimpinan.approval.lembur.tolak', $l->id) }}">
                                                @csrf @method('PATCH')
                                                <textarea name="alasan_tolak" rows="3" placeholder="Alasan penolakan (min. 10 karakter)..."
                                                          class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-gray-700 placeholder-gray-300 outline-none resize-none focus:border-red-400 mb-4"></textarea>
                                                <div class="flex gap-3">
                                                    <button type="submit" class="flex-1 bg-red-600 text-white font-black text-xs uppercase italic py-3 rounded-xl hover:bg-red-700 transition-all">Tolak</button>
                                                    <button @click="showTolak = false" type="button" class="flex-1 bg-gray-100 text-gray-600 font-black text-xs uppercase italic py-3 rounded-xl hover:bg-gray-200 transition-all">Batal</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-[9px] text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <i data-lucide="clock" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                                <p class="text-sm text-gray-400 font-semibold">Tidak ada pengajuan lembur.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($lembur->hasPages())
            <div class="px-6 py-4 border-t border-gray-50">{{ $lembur->links() }}</div>
        @endif
    </div>
@endif

{{-- ── DINAS LUAR ──────────────────────────────────────────────────── --}}
@if ($tipe === 'dinas_luar')
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-wider border-b border-gray-50 bg-gray-50/50">
                        <th class="px-6 py-4">Karyawan</th>
                        <th class="px-6 py-4">Tujuan</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4 text-center">Surat Tugas</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($dinasLuar as $d)
                        <tr class="hover:bg-gray-50 transition-all" x-data="{ showTolak: false }">
                            <td class="px-6 py-4">
                                <p class="text-[11px] font-black text-[#1E3A5F] uppercase">{{ $d->karyawan->nama }}</p>
                                <p class="text-[9px] text-gray-400">{{ $d->karyawan->jabatan }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-[11px] font-semibold text-gray-700">{{ $d->tujuan }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-[11px] text-gray-700">{{ $d->tanggal_berangkat->format('d M') }}</p>
                                <p class="text-[9px] text-gray-400">s/d {{ $d->tanggal_kembali->format('d M Y') }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($d->file_surat_tugas)
                                    <a href="{{ Storage::url($d->file_surat_tugas) }}" target="_blank"
                                       class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all inline-flex">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                    </a>
                                @else
                                    <span class="text-[9px] text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $sb = match($d->status_approval) {
                                        'menunggu'  => 'bg-yellow-100 text-yellow-700',
                                        'disetujui' => 'bg-green-100 text-green-700',
                                        'ditolak'   => 'bg-red-100 text-red-700',
                                        default     => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase {{ $sb }}">
                                    {{ ucfirst($d->status_approval) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($d->status_approval === 'menunggu')
                                    <div class="flex flex-col gap-1.5">
                                        <form method="POST"
                                              action="{{ route('pimpinan.approval.dinas.setuju', $d->id) }}"
                                              onsubmit="return confirm('Setujui dinas luar kota ini?')">
                                            @csrf @method('PATCH')
                                            <button class="w-full flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg bg-green-100 text-green-700 hover:bg-green-200 font-black text-[9px] uppercase italic transition-all">
                                                <i data-lucide="check" class="w-3 h-3"></i> Setuju
                                            </button>
                                        </form>
                                        <button @click="showTolak = true" type="button"
                                                class="w-full flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 font-black text-[9px] uppercase italic transition-all">
                                            <i data-lucide="x" class="w-3 h-3"></i> Tolak
                                        </button>
                                    </div>
                                    <div x-show="showTolak" x-transition @click.away="showTolak = false"
                                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                                        <div class="bg-white rounded-3xl shadow-xl p-6 w-96" @click.stop>
                                            <h3 class="font-black text-[#1E3A5F] text-sm uppercase mb-1">Tolak Dinas Luar</h3>
                                            <p class="text-xs text-gray-400 mb-4">{{ $d->karyawan->nama }} — {{ $d->tujuan }}</p>
                                            <form method="POST" action="{{ route('pimpinan.approval.dinas.tolak', $d->id) }}">
                                                @csrf @method('PATCH')
                                                <textarea name="alasan_tolak" rows="3" placeholder="Alasan penolakan (min. 10 karakter)..."
                                                          class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-gray-700 placeholder-gray-300 outline-none resize-none focus:border-red-400 mb-4"></textarea>
                                                <div class="flex gap-3">
                                                    <button type="submit" class="flex-1 bg-red-600 text-white font-black text-xs uppercase italic py-3 rounded-xl hover:bg-red-700 transition-all">Tolak</button>
                                                    <button @click="showTolak = false" type="button" class="flex-1 bg-gray-100 text-gray-600 font-black text-xs uppercase italic py-3 rounded-xl hover:bg-gray-200 transition-all">Batal</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-[9px] text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <i data-lucide="map-pin" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                                <p class="text-sm text-gray-400 font-semibold">Tidak ada pengajuan dinas luar kota.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($dinasLuar->hasPages())
            <div class="px-6 py-4 border-t border-gray-50">{{ $dinasLuar->links() }}</div>
        @endif
    </div>
@endif

@endsection