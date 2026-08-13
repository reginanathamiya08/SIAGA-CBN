@extends('admin.sidebar')
@section('title','Slip Gaji')

@push('styles')
<style>
@media print {
    aside, .no-print { display: none !important; }
    .ml-64 { margin-left: 0 !important; }
    body { background: white; }
}
</style>
@endpush

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100 no-print">
    <a href="{{ route('admin.penggajian.show', $slipGaji->periodeGaji->id) }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div class="flex-1">
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight uppercase">Slip Gaji</h1>
        <p class="text-gray-500 mt-1 text-sm">
            {{ $slipGaji->karyawan->nama }} — {{ $slipGaji->periodeGaji->nama_periode }}
        </p>
    </div>
    <a href="{{ route('admin.penggajian.slip-official', $slipGaji->id) }}"
       target="_blank"
       class="flex items-center gap-2 bg-green-600 text-white font-black text-xs
              uppercase italic px-5 py-3 rounded-xl hover:bg-green-700 transition-all no-print">
        <i data-lucide="file-text" class="w-4 h-4"></i>
        Cetak Official
    </a>
</header>

@include('admin.penggajian._slip-template', ['slipGaji' => $slipGaji])

@endsection
