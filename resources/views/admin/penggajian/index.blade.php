@extends('admin.sidebar')
@section('title','Penggajian')

@section('content')

<header class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F]">Proses Gaji</h1>
        <p class="text-gray-500 mt-1 text-sm">Riwayat penggajian bulanan PT CBN</p>
    </div>
    <a href="{{ route('admin.penggajian.create') }}"
       class="flex items-center gap-2 bg-[#1E3A5F] hover:bg-blue-900 text-white
              font-black text-xs px-5 py-3 rounded-xl transition-all shadow-lg shadow-blue-900/10 active:scale-95 uppercase">
        <i data-lucide="calculator" class="w-4 h-4"></i>
        Proses Gaji Baru
    </a>
</header>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse ($periode as $p)
        <a href="{{ route('admin.penggajian.show', $p->id) }}"
           class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6
                  hover:border-blue-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-[10px] font-black text-gray-400 mb-1 uppercase tracking-tight">
                        Periode
                    </p>
                    <p class="text-lg font-black text-[#1E3A5F]">
                        {{ $p->nama_periode }}
                    </p>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter mt-1">
                        {{ $p->tanggal_mulai->format('d M') }} —
                        {{ $p->tanggal_selesai->format('d M Y') }}
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase
                             {{ $p->status === 'final'  ? 'bg-emerald-50 text-emerald-600' : 
                                ($p->status === 'proses' ? 'bg-amber-50 text-amber-600' : 
                                                            'bg-gray-50 text-gray-400') }}">
                    {{ $p->labelStatus() }}
                </span>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-5 py-4 border-y border-gray-50">
                <div>
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-tight mb-1">
                        Karyawan
                    </p>
                    <p class="text-xl font-black text-[#1E3A5F]">{{ $p->slip_gaji_count }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-tight mb-1">
                        Finalisasi
                    </p>
                    <p class="text-[10px] font-black text-gray-500 uppercase">
                        {{ $p->finalisasi_at ? $p->finalisasi_at->format('d M Y') : '-' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 text-[10px] font-black text-blue-600
                        group-hover:text-blue-800 transition-colors uppercase tracking-tight">
                Lihat Detail
                <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
            </div>
        </a>
    @empty
        <div class="col-span-3 bg-white rounded-3xl border border-gray-100 shadow-sm
                    p-16 text-center">
            <i data-lucide="calculator" class="w-12 h-12 text-gray-200 mx-auto mb-3"></i>
            <p class="text-sm text-gray-400 font-semibold">Belum ada riwayat penggajian.</p>
            <a href="{{ route('admin.penggajian.create') }}"
               class="mt-3 inline-block text-[10px] font-black text-[#1E3A5F]
                      hover:text-blue-900 uppercase transition-colors tracking-tight">
                + Proses Gaji Pertama
            </a>
        </div>
    @endforelse
</div>

@if ($periode->hasPages())
    <div class="mt-6">{{ $periode->links() }}</div>
@endif

@endsection
