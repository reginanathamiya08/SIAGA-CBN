@extends('admin.sidebar')
@section('title','Penggajian')

@section('content')

<header class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F]">Proses Gaji</h1>
        <p class="text-gray-500 mt-1 text-sm">Riwayat penggajian bulanan PT CBN</p>
    </div>
    <a href="{{ route('admin.penggajian.create') }}"
       class="flex items-center gap-2 bg-[#1E3A5F] hover:bg-green-600 text-white
              font-black text-xs uppercase italic px-5 py-3 rounded-xl transition-all shadow-sm">
        <i data-lucide="calculator" class="w-4 h-4"></i>
        Proses Gaji Baru
    </a>
</header>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse ($periode as $p)
        <a href="{{ route('admin.penggajian.show', $p->id) }}"
           class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5
                  hover:border-blue-200 hover:shadow-md transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">
                        Periode
                    </p>
                    <p class="text-lg font-black text-[#1E3A5F] uppercase">
                        {{ $p->nama_periode }}
                    </p>
                    <p class="text-[10px] text-gray-400 font-medium mt-0.5">
                        {{ $p->tanggal_mulai->format('d M') }} —
                        {{ $p->tanggal_selesai->format('d M Y') }}
                    </p>
                </div>
                <span class="px-2.5 py-1 rounded-xl text-[9px] font-black uppercase
                             {{ $p->status === 'final'  ? 'bg-green-100 text-green-700' :
                                ($p->status === 'proses' ? 'bg-amber-100 text-amber-700' :
                                                            'bg-gray-100 text-gray-500') }}">
                    {{ $p->labelStatus() }}
                </span>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest mb-0.5">
                        Karyawan
                    </p>
                    <p class="text-xl font-black text-gray-700">{{ $p->slip_gaji_count }}</p>
                </div>
                <div>
                    <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest mb-0.5">
                        Finalisasi
                    </p>
                    <p class="text-[10px] font-semibold text-gray-500">
                        {{ $p->finalisasi_at ? $p->finalisasi_at->format('d M Y') : '-' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1 text-[10px] font-black text-blue-500
                        group-hover:text-blue-700 transition-colors">
                Lihat Detail
                <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </div>
        </a>
    @empty
        <div class="col-span-3 bg-white rounded-3xl border border-gray-100 shadow-sm
                    p-16 text-center">
            <i data-lucide="calculator" class="w-12 h-12 text-gray-200 mx-auto mb-3"></i>
            <p class="text-sm text-gray-400 font-semibold">Belum ada riwayat penggajian.</p>
            <a href="{{ route('admin.penggajian.create') }}"
               class="mt-3 inline-block text-xs font-black text-[#1E3A5F]
                      hover:text-red-600 uppercase italic transition-colors">
                + Proses Gaji Pertama
            </a>
        </div>
    @endforelse
</div>

@if ($periode->hasPages())
    <div class="mt-6">{{ $periode->links() }}</div>
@endif

@endsection