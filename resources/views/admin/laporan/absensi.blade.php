{{-- resources/views/Admin/Laporan/absensi.blade.php --}}
@extends('admin.sidebar')

@section('title', 'Laporan Absensi')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Laporan Absensi</h1>
            <p class="text-sm text-slate-500 mt-0.5">Rekap kehadiran karyawan per periode</p>
        </div>
    </div>

    {{-- Tabs Navigasi Jenis Karyawan --}}
    <div class="flex border-b border-slate-200 gap-2 pt-1">
        <a href="{{ route('admin.laporan.absensi.index', array_merge(request()->except(['jenis_karyawan_id', 'user_id']), ['jenis_karyawan_id' => 'tetap'])) }}"
           class="flex items-center gap-2 px-6 py-3 font-bold text-sm border-b-2 transition {{ ($jenisKaryawan === 'tetap' || $jenisKaryawan === 'JNS-00001') ? 'border-blue-600 text-blue-600 bg-white shadow-sm rounded-t-2xl border-t border-x border-slate-200' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
            <i data-lucide="user-check" class="w-4 h-4"></i>
            Laporan Karyawan Tetap
        </a>
        <a href="{{ route('admin.laporan.absensi.index', array_merge(request()->except(['jenis_karyawan_id', 'user_id']), ['jenis_karyawan_id' => 'kontrak'])) }}"
           class="flex items-center gap-2 px-6 py-3 font-bold text-sm border-b-2 transition {{ ($jenisKaryawan === 'kontrak' || $jenisKaryawan === 'JNS-00002') ? 'border-blue-600 text-blue-600 bg-white shadow-sm rounded-t-2xl border-t border-x border-slate-200' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
            <i data-lucide="briefcase" class="w-4 h-4"></i>
            Laporan Karyawan Kontrak
        </a>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white rounded-b-2xl rounded-tr-2xl shadow-sm border border-slate-100 p-5">
        <form method="GET" action="{{ route('admin.laporan.absensi.index') }}"
              class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 items-end">
            
            <input type="hidden" name="jenis_karyawan_id" value="{{ $jenisKaryawan }}">

            {{-- Bulan --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Bulan</label>
                <select name="bulan"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" @selected($m == $bulan)>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tahun --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Tahun</label>
                <select name="tahun"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    @foreach(range(now()->year - 2, now()->year) as $y)
                        <option value="{{ $y }}" @selected($y == $tahun)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Mitra (Khusus Karyawan Kontrak) --}}
            @if($jenisKaryawan === 'JNS-00002')
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Mitra / Cabang</label>
                <select name="mitra_id"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Semua Mitra</option>
                    @foreach($semuaMitra as $m)
                        <option value="{{ $m->id }}" @selected($m->id == $mitraId)>{{ $m->nama_mitra }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Divisi --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Divisi</label>
                <select name="divisi"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Semua Divisi</option>
                    @foreach($semuaDivisi as $key => $label)
                        <option value="{{ $key }}" @selected($divisi == $key || $divisi == $label)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol --}}
            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition">
                    Tampilkan
                </button>
                <a href="{{ route('admin.laporan.absensi.index', ['jenis_karyawan_id' => $jenisKaryawan]) }}"
                   class="flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-2 rounded-xl transition"
                   title="Reset">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Info & Export --}}
    @if($absensiList->count() > 0)

    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">
            Menampilkan <span class="font-bold text-slate-700">{{ $absensiList->count() }}</span> record
        </p>

        {{-- Tombol Export Excel --}}
        <a href="{{ route('admin.laporan.absensi.export', [
                'bulan'          => $bulan,
                'tahun'          => $tahun,
                'mitra_id'       => $mitraId,
                'divisi'         => $divisi,
                'jenis_karyawan' => $jenisKaryawan,
                'user_id'        => $karyawanId,
            ]) }}"
           target="_blank"
           class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition">
            <i data-lucide="download" class="w-4 h-4"></i>
            Export Excel
        </a>
    </div>

    {{-- Rekap Per Karyawan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-black text-slate-700 text-sm uppercase tracking-wider">Rekap Per Karyawan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-blue-600 text-white text-xs font-bold uppercase tracking-wider">
                        <th class="px-4 py-3 text-left">No.</th>
                        <th class="px-4 py-3 text-left">Nama Karyawan</th>
                        <th class="px-4 py-3 text-left">Jabatan</th>
                        <th class="px-4 py-3 text-left">Mitra / Cabang</th>
                        <th class="px-4 py-3 text-center">Hadir</th>
                        <th class="px-4 py-3 text-center">Telat</th>
                        <th class="px-4 py-3 text-center">Alfa</th>
                        <th class="px-4 py-3 text-center">Izin</th>
                        <th class="px-4 py-3 text-center">Sakit</th>
                        <th class="px-4 py-3 text-center">Cuti</th>
                        <th class="px-4 py-3 text-center">Dinas</th>
                        <th class="px-4 py-3 text-center">% Hadir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rekap as $i => $r)
                        @php
                            $persen = $totalHariKerja > 0 ? round(($r['hadir'] / $totalHariKerja) * 100, 1) : 0;
                            $merah  = $persen < 80;
                        @endphp
                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50' }} hover:bg-blue-50 transition">
                            <td class="px-4 py-3 text-slate-500">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-semibold {{ $merah ? 'text-red-600' : 'text-slate-800' }}">
                                {{ $r['nama'] }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $r['jabatan'] }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $r['mitra'] }}</td>
                            <td class="px-4 py-3 text-center font-bold text-emerald-600">{{ $r['hadir'] }}</td>
                            <td class="px-4 py-3 text-center font-bold text-amber-500">{{ $r['telat'] }}</td>
                            <td class="px-4 py-3 text-center font-bold text-red-500">{{ $r['alfa'] }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $r['izin'] }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $r['sakit'] }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $r['cuti'] }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $r['dinas'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                    {{ $merah ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $persen }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Detail Harian --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden" x-data="{ searchDetail: '' }">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-black text-slate-700 text-sm uppercase tracking-wider">Detail Absensi Harian</h2>
                <p class="text-[10px] text-slate-400 font-bold uppercase mt-0.5">Menampilkan {{ $absensiList->count() }} data absensi harian</p>
            </div>

            {{-- Quick Search Input --}}
            <div class="relative w-full sm:w-64">
                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" x-model="searchDetail" placeholder="Cari nama, status, atau tanggal..."
                       class="w-full pl-9 pr-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
            </div>
        </div>
        <div class="max-h-[400px] overflow-y-auto overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-700 text-white text-xs font-bold uppercase tracking-wider sticky top-0 z-10 shadow-sm">
                        <th class="px-4 py-3 text-left bg-slate-700">No.</th>
                        <th class="px-4 py-3 text-left bg-slate-700">Nama Karyawan</th>
                        <th class="px-4 py-3 text-left bg-slate-700">Mitra / Cabang</th>
                        <th class="px-4 py-3 text-left bg-slate-700">Tanggal</th>
                        <th class="px-4 py-3 text-center bg-slate-700">Jam Masuk</th>
                        <th class="px-4 py-3 text-center bg-slate-700">Jam Pulang</th>
                        <th class="px-4 py-3 text-center bg-slate-700">Status</th>
                        <th class="px-4 py-3 text-center bg-slate-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($absensiList as $i => $abs)
                        @php
                            $statusColor = match($abs->status) {
                                'hadir'      => $abs->is_telat ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700',
                                'telat'      => 'bg-amber-100 text-amber-700',
                                'alfa'       => 'bg-red-100 text-red-700',
                                'izin'       => 'bg-purple-100 text-purple-700',
                                'sakit'      => 'bg-blue-100 text-blue-700',
                                'cuti'       => 'bg-sky-100 text-sky-700',
                                'dinas_luar' => 'bg-indigo-100 text-indigo-700',
                                default      => 'bg-slate-100 text-slate-600',
                            };
                            $statusLabel = match($abs->status) {
                                'hadir'      => $abs->is_telat ? 'Telat' : 'Tepat Waktu',
                                'telat'      => 'Telat',
                                'alfa'       => 'Alfa',
                                'izin'       => 'Izin Pribadi',
                                'sakit'      => 'Sakit',
                                'cuti'       => 'Cuti',
                                'dinas_luar' => 'Dinas Luar Kota',
                                default      => ucfirst($abs->status),
                            };
                            $searchKey = strtolower(($abs->karyawan?->nama ?? '') . ' ' . ($abs->mitra?->nama_mitra ?? '') . ' ' . $statusLabel . ' ' . ($abs->tanggal?->translatedFormat('d M Y') ?? ''));
                        @endphp
                        <tr x-show="!searchDetail || '{{ $searchKey }}'.includes(searchDetail.toLowerCase())" class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50' }} hover:bg-blue-50 transition">
                            <td class="px-4 py-3 text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">
                                {{ $abs->karyawan?->nama ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $abs->mitra?->nama_mitra ?? ($abs->karyawan?->isTetap() ? 'Kantor CBN' : '-') }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $abs->tanggal?->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono text-slate-700">
                                {{ $abs->waktu_masuk?->format('H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono text-slate-700">
                                {{ $abs->waktu_pulang?->format('H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" 
                                        onclick="openEditAbsenModal(
                                            '{{ $abs->id }}', 
                                            '{{ $abs->tanggal?->format('Y-m-d') }}',
                                            '{{ $abs->tanggal?->translatedFormat('d M Y') }}',
                                            '{{ $abs->status }}',
                                            '{{ $abs->waktu_masuk ? $abs->waktu_masuk->format('H:i') : '' }}',
                                            '{{ $abs->waktu_pulang ? $abs->waktu_pulang->format('H:i') : '' }}',
                                            '{{ $abs->mitra_id }}',
                                            '{{ $abs->karyawan?->nama }}'
                                        )"
                                        class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-xl text-xs font-black transition">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @else
    {{-- Empty State --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-16 text-center">
        <i data-lucide="calendar-x" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
        <p class="font-bold text-slate-500">Tidak ada data absensi untuk filter yang dipilih.</p>
        <p class="text-sm text-slate-400 mt-1">Coba ubah filter periode atau mitra.</p>
    </div>
    @endif

</div>

{{-- MODAL EDIT ABSENSI --}}
<div id="modal-edit-absen" class="fixed inset-0 z-[9999] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-md w-full overflow-hidden my-auto relative">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-black text-slate-800 uppercase tracking-tight text-sm">Edit Absensi <span id="edit-nama-karyawan"></span></h3>
            <button onclick="closeModal('modal-edit-absen')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" id="form-edit-absen" class="p-5 space-y-3">
            @csrf
            @method('PUT')
            
            {{-- Info Tanggal --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-0.5">Tanggal</label>
                <input type="text" id="edit-tanggal" disabled
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-500">
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-0.5">Status Kehadiran</label>
                <select name="status" id="edit-status" required onchange="toggleTimeFields('edit-status', 'edit-times')"
                        class="w-full rounded-xl border border-slate-200 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="hadir">Tepat Waktu</option>
                    <option value="telat">Telat</option>
                    <option value="alfa">Alfa</option>
                </select>
            </div>

            {{-- Jam Masuk & Pulang --}}
            <div id="edit-times" class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-0.5">Waktu Masuk</label>
                    <input type="time" name="waktu_masuk" id="edit-waktu-masuk"
                           class="w-full rounded-xl border border-slate-200 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-0.5">Waktu Pulang</label>
                    <input type="time" name="waktu_pulang" id="edit-waktu-pulang"
                           class="w-full rounded-xl border border-slate-200 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modal-edit-absen')"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-xs transition">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
    function toggleTimeFields(statusId, containerId) {
        const statusVal = document.getElementById(statusId).value;
        const container = document.getElementById(containerId);
        if (statusVal === 'alfa') {
            container.classList.add('hidden');
        } else {
            container.classList.remove('hidden');
        }
    }
    function openEditAbsenModal(id, tanggalRaw, tanggalFormat, status, waktuMasuk, waktuPulang, mitraId, namaKaryawan) {
        const form = document.getElementById('form-edit-absen');
        form.action = `/admin/laporan/absensi/${id}`;
        document.getElementById('edit-nama-karyawan').innerText = `(${namaKaryawan})`;
        document.getElementById('edit-tanggal').value = tanggalFormat;
        document.getElementById('edit-status').value = status;
        document.getElementById('edit-waktu-masuk').value = waktuMasuk || '';
        document.getElementById('edit-waktu-pulang').value = waktuPulang || '';
        toggleTimeFields('edit-status', 'edit-times');
        openModal('modal-edit-absen');
    }
</script>
@endsection
