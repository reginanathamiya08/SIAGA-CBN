@extends('karyawan.sidebar')
@section('title', 'Delegasi Cuti')

@section('content')

<header class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight">Delegasi & Backup Cuti</h1>
        <p class="text-gray-500 mt-1 text-sm">Konfirmasi kesediaan Anda untuk mem-backup tugas rekan kerja yang sedang cuti</p>
    </div>
</header>

<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[10px] font-black text-[#1E3A5F] tracking-wider border-b border-gray-50 bg-gray-50/50">
                    <th class="px-6 py-4">Rekan Kerja (Pemohon)</th>
                    <th class="px-6 py-4">Jenis Izin</th>
                    <th class="px-6 py-4">Periode</th>
                    <th class="px-6 py-4 text-center">Jumlah Hari</th>
                    <th class="px-6 py-4">Keterangan Pekerjaan</th>
                    <th class="px-6 py-4 text-center">Status Konfirmasi</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($backupRequests as $req)
                    <tr class="hover:bg-gray-50/50 transition-all">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-blue-100 text-blue-700 rounded-xl flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($req->karyawan?->nama ?? 'U', 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-xs font-black text-gray-800">{{ $req->karyawan?->nama }}</p>
                                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">{{ $req->karyawan?->jabatan ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black bg-indigo-100 text-indigo-700">
                                {{ $req->jenisPerizinan?->nama_jenis ?? 'Cuti Tahunan' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-[11px] font-semibold text-gray-700">
                                {{ $req->tanggal_mulai->format('d M Y') }}
                            </p>
                            @if ($req->tanggal_mulai != $req->tanggal_selesai)
                                <p class="text-[9px] text-gray-400">
                                    s/d {{ $req->tanggal_selesai->format('d M Y') }}
                                </p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-black text-gray-700">{{ $req->jumlah_hari }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-[11px] text-gray-500 max-w-xs truncate" title="{{ $req->keterangan }}">
                                {{ $req->keterangan ?? '-' }}
                            </p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if ($req->status_rekan === 'menunggu')
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black bg-amber-100 text-amber-700">
                                    Menunggu Persetujuan Anda
                                </span>
                            @elseif ($req->status_rekan === 'disetujui')
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black bg-green-100 text-green-700">
                                    Disetujui
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black bg-red-100 text-red-700">
                                    Ditolak
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                @if ($req->status_rekan === 'menunggu')
                                    <form action="{{ route('karyawan.perizinan.backup.setuju', $req->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="flex items-center justify-center gap-1 bg-green-600 hover:bg-green-700 text-white font-bold text-[10px] px-3 py-1.5 rounded-lg transition-all shadow-sm">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            Setuju
                                        </button>
                                    </form>
                                    <form action="{{ route('karyawan.perizinan.backup.tolak', $req->id) }}" method="POST" class="inline btn-tolak-form">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="flex items-center justify-center gap-1 bg-red-600 hover:bg-red-700 text-white font-bold text-[10px] px-3 py-1.5 rounded-lg transition-all shadow-sm">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                            Tolak
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[10px] text-gray-400 font-bold uppercase italic">Selesai</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <i data-lucide="users" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                            <p class="text-[11px] font-black text-gray-300 uppercase tracking-widest italic">
                                Belum ada permintaan delegasi cuti
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($backupRequests->hasPages())
        <div class="px-6 py-4 border-t border-gray-50 bg-gray-50/50">
            {{ $backupRequests->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Confirmation on action
    document.querySelectorAll('.btn-tolak-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda akan menolak menjadi backup cuti untuk rekan kerja Anda.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#1E3A5F',
                confirmButtonText: 'Ya, Tolak',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush

@endsection
