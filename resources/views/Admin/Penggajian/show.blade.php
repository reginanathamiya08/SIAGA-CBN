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
    <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase
                 {{ $periodeGaji->status === 'final'  ? 'bg-green-100 text-green-700' :
                    ($periodeGaji->status === 'proses' ? 'bg-amber-100 text-amber-700' :
                                                         'bg-gray-100 text-gray-500') }}">
        {{ $periodeGaji->labelStatus() }}
    </span>
</header>

{{-- Ringkasan --}}
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

{{-- Tabel --}}
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
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
                            <p class="text-[11px] font-black text-[#1E3A5F] uppercase">
                                {{ $slip->karyawan->nama }}
                            </p>
                            <p class="text-[9px] text-gray-400">{{ $slip->karyawan->jabatan }}</p>
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
                            Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}
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
                            <a href="{{ route('admin.penggajian.slip', $slip->id) }}"
                               class="p-2 rounded-lg bg-blue-50 text-blue-600
                                      hover:bg-blue-100 transition-all inline-flex"
                               title="Lihat Slip">
                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                            </a>
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

@endsection