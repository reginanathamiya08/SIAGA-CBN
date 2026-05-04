{{-- resources/views/pimpinan/dashboard.blade.php --}}
@extends('pimpinan.sidebar')

@section('title', 'Dashboard Pimpinan')

@section('content')
    <header class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
        <div>
            <h1 class="text-2xl font-black text-[#1E3A5F]">Dashboard Pimpinan</h1>
            <p class="text-gray-500 mt-1 text-sm">Monitoring & Persetujuan <span class="text-red-600 font-bold ">PT CBN</span></p>
        </div>
        <span class="hidden md:block text-[11px] font-black bg-[#1E3A5F] text-white px-5 py-2.5 rounded-xl italic shadow-md  tracking-widest">
            {{ now()->translatedFormat('d M Y') }}
        </span>
    </header>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-50 flex items-center gap-4 group hover:shadow-md transition-all">
            <div class="p-3 bg-blue-600 text-white rounded-xl shadow-lg shadow-blue-100 group-hover:scale-110 transition-transform">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400  tracking-widest mb-1">Total Karyawan</p>
                <p class="text-xl font-black text-[#1E3A5F]">{{ $totalKaryawan }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-50 flex items-center gap-4 group hover:shadow-md transition-all">
            <div class="p-3 bg-green-600 text-white rounded-xl shadow-lg shadow-green-100 group-hover:scale-110 transition-transform">
                <i data-lucide="user-check" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400  tracking-widest mb-1">Hadir Hari Ini</p>
                <p class="text-xl font-black text-green-700">{{ $hadirHariIni }} <span class="text-xs text-gray-400">({{ $persenHadir }}%)</span></p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-50 flex items-center gap-4 group hover:shadow-md transition-all">
            <div class="p-3 bg-yellow-500 text-white rounded-xl shadow-lg shadow-yellow-100 group-hover:scale-110 transition-transform">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400  tracking-widest mb-1">Pengajuan Masuk</p>
                <p class="text-xl font-black text-yellow-600">{{ $totalMenunggu }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-50 flex items-center gap-4 group hover:shadow-md transition-all">
            <div class="p-3 bg-[#1E3A5F] text-white rounded-xl shadow-lg shadow-blue-900/20 group-hover:scale-110 transition-transform">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400  tracking-widest mb-1">Periode Aktif</p>
                <p class="text-sm font-black text-[#1E3A5F]">{{ $periodeAktif?->nama_periode ?? 'Belum ada' }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Chart Tren Kehadiran -->
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xs font-black text-[#1E3A5F]  tracking-widest">Tren Kehadiran (7 Hari Terakhir)</h3>
                <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-md ">Live Data</span>
            </div>
            <div class="h-[250px]">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>

        <!-- Pengajuan Terbaru -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xs font-black text-[#1E3A5F]  tracking-widest">Persetujuan Menunggu</h3>
                <a href="{{ route('pimpinan.approval.index') }}" class="text-[10px] font-black text-red-600 hover:underline  tracking-tighter">Lihat Semua</a>
            </div>
            <div class="space-y-4">
                @forelse($pengajuanTerbaru as $p)
                <div class="flex items-center gap-4 p-3 rounded-2xl bg-gray-50 hover:bg-gray-100 transition-colors">
                    <div class="p-2.5 bg-{{ $p->color }}-100 text-{{ $p->color }}-600 rounded-xl">
                        <i data-lucide="{{ $p->icon }}" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-black text-[#1E3A5F] truncate">{{ $p->karyawan?->nama ?? 'Unknown' }}</p>
                        <p class="text-[9px] text-gray-400 font-bold  tracking-tighter">{{ $p->tipe }} • {{ $p->created_at->diffForHumans() }}</p>
                    </div>
                    <a href="{{ route('pimpinan.approval.index') }}" class="p-2 text-gray-300 hover:text-blue-600 transition-colors">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                </div>
                @empty
                <div class="text-center py-12">
                    <i data-lucide="check-circle" class="w-10 h-10 text-emerald-100 mx-auto mb-2"></i>
                    <p class="text-[10px] font-black text-gray-300  tracking-widest">Semua pengajuan selesai</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Absensi Terbaru -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xs font-black text-[#1E3A5F]  tracking-widest">Absensi Terbaru Hari Ini</h3>
            <a href="{{ route('pimpinan.monitoring.index') }}" class="text-[10px] font-black text-blue-600 hover:underline  tracking-tighter">Selengkapnya</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-black text-gray-400  tracking-widest">
                        <th class="pb-4 px-2">Karyawan</th>
                        <th class="pb-4 px-2">Jam Masuk</th>
                        <th class="pb-4 px-2">Mitra</th>
                        <th class="pb-4 px-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($absensiTerbaru as $abs)
                    <tr class="group hover:bg-gray-50/50 transition-colors">
                        <td class="py-4 px-2">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-blue-50 text-[#1E3A5F] rounded-lg flex items-center justify-center font-black text-[10px]">
                                    {{ strtoupper(substr($abs->karyawan?->nama, 0, 2)) }}
                                </div>
                                <span class="text-xs font-black text-[#1E3A5F]">{{ $abs->karyawan?->nama }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-2 text-xs font-bold text-gray-600">
                            {{ $abs->waktu_masuk?->format('H:i') ?? '-' }}
                        </td>
                        <td class="py-4 px-2 text-xs text-gray-500 italic">
                             {{ $abs->karyawan?->penempatanAktif?->mitra?->nama_mitra ?? '-' }}
                        </td>
                        <td class="py-4 px-2">
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black  tracking-tighter {{ $abs->is_telat ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600' }}">
                                {{ $abs->is_telat ? 'Telat' : 'Tepat Waktu' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-[10px] font-black text-gray-300  tracking-widest">Belum ada absensi masuk</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($labels) !!},
                datasets: [{
                    label: 'Kehadiran',
                    data: {!! json_encode($values) !!},
                    borderColor: '#2563eb',
                    borderWidth: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#2563eb',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    tension: 0.4,
                    fill: true,
                    backgroundColor: gradient
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#1e3a5f',
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 11 },
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, weight: '600' }, color: '#94a3b8' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { 
                            font: { size: 10, weight: '600' }, 
                            color: '#94a3b8',
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });
</script>
@endpush