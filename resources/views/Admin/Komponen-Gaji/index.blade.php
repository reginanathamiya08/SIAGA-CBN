@extends('admin.sidebar')
@section('title', 'Komponen Gaji')

@section('content')

<header class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight uppercase">Komponen Gaji</h1>
        <p class="text-gray-500 mt-1 text-sm">Atur gaji pokok, tunjangan, dan potongan BPJS tiap karyawan</p>
    </div>
    {{-- Update BPJS Massal --}}
    <button onclick="document.getElementById('modal-bpjs').classList.remove('hidden')"
            class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white
                   font-black text-xs uppercase italic px-5 py-3 rounded-xl transition-all shadow-sm">
        <i data-lucide="settings" class="w-4 h-4"></i>
        Update BPJS Massal
    </button>
</header>

{{-- Modal Update BPJS Massal --}}
<div id="modal-bpjs" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-3xl shadow-xl p-6 w-96">
        <h3 class="font-black text-[#1E3A5F] uppercase text-sm mb-1">Update BPJS Semua Karyawan</h3>
        <p class="text-xs text-gray-400 mb-5">
            Perubahan ini akan diterapkan ke <strong>semua karyawan</strong> sekaligus.
        </p>
        <form method="POST" action="{{ route('admin.komponen-gaji.bulk-bpjs') }}">
            @csrf
            <div class="space-y-4 mb-5">
                <div>
                    <label class="block text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2">
                        BPJS Kesehatan (%)
                    </label>
                    <input type="number" name="persen_bpjs_kes"
                           value="{{ config('cbn.default_bpjs_kes', 9.24) }}"
                           step="0.01" min="0" max="100"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50
                                  text-sm font-semibold text-gray-700 outline-none focus:border-[#1E3A5F]">
                </div>
                <div>
                    <label class="block text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2">
                        BPJS Ketenagakerjaan (%)
                    </label>
                    <input type="number" name="persen_bpjs_tk"
                           value="{{ config('cbn.default_bpjs_tk', 5.00) }}"
                           step="0.01" min="0" max="100"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50
                                  text-sm font-semibold text-gray-700 outline-none focus:border-[#1E3A5F]">
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-black
                               text-xs uppercase italic py-3 rounded-xl transition-all">
                    Terapkan Semua
                </button>
                <button type="button"
                        onclick="document.getElementById('modal-bpjs').classList.add('hidden')"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-600 font-black
                               text-xs uppercase italic py-3 rounded-xl transition-all">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Statistik --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Karyawan</p>
        <p class="text-2xl font-black text-[#1E3A5F]">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Sudah Diisi</p>
        <p class="text-2xl font-black text-green-600">{{ $stats['sudah_diisi'] }}</p>
    </div>
    <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-sm text-center
                {{ $stats['belum_diisi'] > 0 ? 'bg-amber-50' : 'bg-white' }}">
        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Belum Diisi</p>
        <p class="text-2xl font-black {{ $stats['belum_diisi'] > 0 ? 'text-amber-600' : 'text-gray-300' }}">
            {{ $stats['belum_diisi'] }}
        </p>
    </div>
</div>

{{-- Filter --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('admin.komponen-gaji.index') }}"
          class="flex flex-wrap items-center gap-3">
        <input type="text" name="cari" value="{{ request('cari') }}"
               placeholder="Cari nama karyawan..."
               class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold
                      text-gray-700 outline-none focus:border-[#1E3A5F] bg-gray-50 w-52">
        <select name="jenis" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold
                       text-gray-700 outline-none bg-gray-50 focus:border-[#1E3A5F]">
            <option value="">Semua Jenis</option>
            <option value="tetap"   {{ request('jenis')==='tetap'   ?'selected':'' }}>Karyawan Tetap</option>
            <option value="kontrak" {{ request('jenis')==='kontrak' ?'selected':'' }}>Karyawan Kontrak</option>
        </select>
        <select name="divisi" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold
                       text-gray-700 outline-none bg-gray-50 focus:border-[#1E3A5F]">
            <option value="">Semua Divisi</option>
            <optgroup label="Karyawan Tetap">
                <option value="keuangan"       {{ request('divisi')==='keuangan'       ?'selected':'' }}>Keuangan</option>
                <option value="koordinator_cs" {{ request('divisi')==='koordinator_cs' ?'selected':'' }}>Koordinator CS</option>
                <option value="adm_umum"       {{ request('divisi')==='adm_umum'       ?'selected':'' }}>Adm & Umum</option>
            </optgroup>
            <optgroup label="Karyawan Kontrak">
                <option value="HC"   {{ request('divisi')==='HC'   ?'selected':'' }}>HC</option>
                <option value="umum" {{ request('divisi')==='umum' ?'selected':'' }}>Umum</option>
            </optgroup>
        </select>
        <button type="submit"
                class="bg-[#1E3A5F] text-white px-5 py-2.5 rounded-xl font-black
                       text-xs uppercase italic hover:bg-red-600 transition-all">
            Cari
        </button>
        @if (request()->hasAny(['cari','jenis','divisi']))
            <a href="{{ route('admin.komponen-gaji.index') }}"
               class="text-xs font-bold text-gray-400 hover:text-red-500 transition-colors">Reset</a>
        @endif
    </form>
</div>

{{-- Tabel --}}
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-wider
                           border-b border-gray-50 bg-gray-50/50">
                    <th class="px-6 py-4">Karyawan</th>
                    <th class="px-6 py-4 text-right">Gaji Pokok</th>
                    <th class="px-6 py-4 text-right">Uang Makan</th>
                    <th class="px-6 py-4 text-right">Transport</th>
                    <th class="px-6 py-4 text-center">BPJS Kes</th>
                    <th class="px-6 py-4 text-center">BPJS TK</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($karyawan as $kar)
                    @php $kg = $kar->komponenGaji @endphp
                    <tr class="hover:bg-gray-50 transition-all">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center
                                            font-black text-xs shrink-0
                                            {{ $kar->isTetap() ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700' }}">
                                    {{ strtoupper(substr($kar->nama, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-[11px] font-black text-[#1E3A5F] uppercase">{{ $kar->nama }}</p>
                                    <p class="text-[9px] text-gray-400">{{ $kar->jabatan }} • {{ $kar->labelDivisi() }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if ($kg && $kg->gaji_pokok > 0)
                                <span class="text-sm font-black text-gray-700">
                                    Rp {{ number_format($kg->gaji_pokok, 0, ',', '.') }}
                                </span>
                                @if ($kar->gaji_atas_umr)
                                    <span class="block text-[8px] text-green-600 font-black uppercase">Di atas UMR</span>
                                @else
                                    <span class="block text-[8px] text-blue-500 font-black uppercase">UMR</span>
                                @endif
                            @else
                                <span class="text-xs font-black text-amber-500 uppercase">Belum diisi</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if ($kar->uang_makan_by_mitra)
                                <span class="text-[10px] font-semibold text-gray-400 italic">by Mitra</span>
                            @else
                                <span class="text-sm font-semibold text-gray-600">
                                    Rp {{ number_format($kg->uang_makan ?? 35000, 0, ',', '.') }}/hr
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if ($kar->uang_makan_by_mitra)
                                <span class="text-[10px] font-semibold text-gray-400 italic">by Mitra</span>
                            @else
                                <span class="text-sm font-semibold text-gray-600">
                                    Rp {{ number_format($kg->uang_transport ?? 45000, 0, ',', '.') }}/hr
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-black text-gray-700">{{ $kg->persen_bpjs_kes ?? 9.24 }}%</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-black text-gray-700">{{ $kg->persen_bpjs_tk ?? 5.00 }}%</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.komponen-gaji.edit', $kar->id) }}"
                               class="flex items-center gap-1.5 px-3 py-2 rounded-xl
                                      {{ (!$kg || $kg->gaji_pokok == 0)
                                           ? 'bg-amber-100 text-amber-700 hover:bg-amber-200'
                                           : 'bg-blue-50 text-blue-600 hover:bg-blue-100' }}
                                      font-black text-[10px] uppercase italic transition-all mx-auto w-fit">
                                <i data-lucide="{{ (!$kg || $kg->gaji_pokok == 0) ? 'alert-circle' : 'pencil' }}"
                                   class="w-3.5 h-3.5"></i>
                                {{ (!$kg || $kg->gaji_pokok == 0) ? 'Isi Sekarang' : 'Edit' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <i data-lucide="wallet" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                            <p class="text-sm text-gray-400 font-semibold">Tidak ada data karyawan.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($karyawan->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">
            {{ $karyawan->links() }}
        </div>
    @endif
</div>

@endsection