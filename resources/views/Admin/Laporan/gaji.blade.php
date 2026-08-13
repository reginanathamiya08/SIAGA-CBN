{{-- resources/views/Admin/Laporan/gaji.blade.php --}}
@extends('Admin.sidebar')

@section('title', 'Laporan Gaji')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Laporan Gaji</h1>
            <p class="text-sm text-slate-500 mt-0.5">Rekapitulasi penggajian karyawan per mitra</p>
        </div>
    </div>

    {{-- Tabs Navigasi Jenis Karyawan --}}
    <div class="flex border-b border-slate-200 gap-2 pt-2">
        <a href="{{ route('admin.laporan.gaji.index', array_merge(request()->except(['jenis_karyawan_id']), ['jenis_karyawan_id' => 'tetap'])) }}"
           class="flex items-center gap-2 px-6 py-3 font-bold text-sm border-b-2 transition {{ ($jenisKaryawan === 'tetap' || $jenisKaryawan === 'JNS-00001') ? 'border-blue-600 text-blue-600 bg-white shadow-sm rounded-t-2xl border-t border-x border-slate-200' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
            <i data-lucide="user-check" class="w-4 h-4"></i>
            Laporan Gaji Karyawan Tetap
        </a>
        <a href="{{ route('admin.laporan.gaji.index', array_merge(request()->except(['jenis_karyawan_id']), ['jenis_karyawan_id' => 'kontrak'])) }}"
           class="flex items-center gap-2 px-6 py-3 font-bold text-sm border-b-2 transition {{ ($jenisKaryawan === 'kontrak' || $jenisKaryawan === 'JNS-00002') ? 'border-blue-600 text-blue-600 bg-white shadow-sm rounded-t-2xl border-t border-x border-slate-200' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
            <i data-lucide="briefcase" class="w-4 h-4"></i>
            Laporan Gaji Karyawan Kontrak
        </a>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white rounded-b-2xl rounded-tr-2xl shadow-sm border border-slate-100 p-5">
        <form method="GET" action="{{ route('admin.laporan.gaji.index') }}"
              class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">

            <input type="hidden" name="jenis_karyawan_id" value="{{ $jenisKaryawan }}">

            {{-- Periode --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wider">Periode Gaji</label>
                <select name="periode_id"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Semua Periode</option>
                    @foreach($semuaPeriode as $p)
                        <option value="{{ $p->id }}" @selected($p->id == $periodeId)>
                            {{ $p->nama_periode }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Mitra (Khusus Karyawan Kontrak) --}}
            @if($jenisKaryawan === 'JNS-00002')
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wider">Mitra / Cabang</label>
                <select name="mitra_id"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Semua Mitra</option>
                    @foreach($semuaMitra as $m)
                        <option value="{{ $m->id }}" @selected($m->id == $mitraId)>
                            {{ $m->nama_mitra }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Tombol Filter --}}
            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition shadow-sm shadow-blue-200">
                    Tampilkan
                </button>
                <a href="{{ route('admin.laporan.gaji.index', ['jenis_karyawan_id' => $jenisKaryawan]) }}"
                   class="flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-2.5 rounded-xl transition"
                   title="Reset">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>
            
            {{-- Tombol Export --}}
            @if($slipGaji->count() > 0)
            <div>
                <a href="{{ route('admin.laporan.gaji.export', ['periode_id' => $periodeId, 'mitra_id' => $mitraId, 'jenis_karyawan_id' => $jenisKaryawan]) }}"
                   target="_blank"
                   class="flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition shadow-sm shadow-emerald-200">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Export Excel
                </a>
            </div>
            @endif
        </form>
    </div>

    {{-- Data Table --}}
    @if($slipGaji->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto max-h-[480px] overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-slate-50 border-b border-slate-100 text-slate-600 text-xs font-bold uppercase tracking-wider shadow-sm">
                    <tr>
                        <th class="px-6 py-4 text-left">No.</th>
                        <th class="px-6 py-4 text-left">NIK / Nama</th>
                        <th class="px-6 py-4 text-left">Jabatan / Mitra</th>
                        <th class="px-3 py-4 text-center">Hdr</th>
                        <th class="px-3 py-4 text-center">Tlt</th>
                        <th class="px-3 py-4 text-center">Izn</th>
                        <th class="px-3 py-4 text-center">Alf</th>
                        <th class="px-3 py-4 text-center">Cut</th>
                        <th class="px-6 py-4 text-right">Gaji Pokok</th>
                        <th class="px-6 py-4 text-right">Uang Makan & Trans</th>
                        <th class="px-6 py-4 text-right text-red-500">Potongan</th>
                        <th class="px-6 py-4 text-right font-black text-blue-600">Gaji Bersih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($slipGaji as $i => $slip)
                    <tr class="hover:bg-blue-50/50 transition-colors">
                        <td class="px-6 py-4 text-slate-400">{{ $i + 1 }}</td>
                        <td class="px-6 py-4">
                            <p class="font-black text-slate-800">{{ $slip->karyawan?->nama ?? '-' }}</p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $slip->karyawan?->user?->nip ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-slate-700 font-medium">{{ $slip->karyawan?->jabatan ?? '-' }}</p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                {{ $slip->karyawan?->penempatanAktif?->mitra?->nama_mitra ?? ($slip->karyawan?->isTetap() ? 'Kantor CBN' : '-') }}
                            </p>
                        </td>
                        <td class="px-3 py-4 text-center font-bold text-emerald-600">{{ $slip->total_hadir }}</td>
                        <td class="px-3 py-4 text-center font-bold text-amber-500">{{ $slip->total_telat }}</td>
                        <td class="px-3 py-4 text-center font-bold text-purple-500">{{ $slip->total_izin }}</td>
                        <td class="px-3 py-4 text-center font-bold text-red-500">{{ $slip->total_alfa }}</td>
                        <td class="px-3 py-4 text-center font-bold text-sky-500">{{ $slip->total_cuti }}</td>
                        <td class="px-6 py-4 text-right font-mono text-slate-600">
                            {{ number_format($slip->getNominal('Gaji Pokok'), 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-slate-600">
                            {{ number_format($slip->getNominal('Uang Makan') + $slip->getNominal('Uang Transport'), 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-red-500">
                            {{ number_format($slip->total_potongan, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-mono font-black text-blue-600">
                                {{ number_format($slip->gaji_bersih, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="sticky bottom-0 z-10 bg-slate-50 shadow-sm border-t border-slate-200">
                    <tr class="font-black text-slate-800">
                        <td colspan="11" class="px-6 py-4 text-right uppercase tracking-widest text-xs">Total Keseluruhan</td>
                        <td class="px-6 py-4 text-right font-mono text-blue-600">
                            {{ number_format($slipGaji->sum('gaji_bersih'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @else
    {{-- Empty State --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-16 text-center">
        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i data-lucide="receipt-text" class="w-8 h-8 text-slate-300"></i>
        </div>
        <p class="font-black text-slate-500 uppercase tracking-widest text-sm">Tidak Ada Data</p>
        <p class="text-sm text-slate-400 mt-1">Silakan pilih periode dan mitra untuk menampilkan laporan gaji.</p>
    </div>
    @endif

</div>
@endsection
