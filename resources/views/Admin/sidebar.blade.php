<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Admin') — Sistem CBN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>tailwind.config={theme:{extend:{colors:{cbn:'#1E3A5F'}}}}</script>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen" x-data="{sidebarOpen:true}">

<aside class="fixed top-0 left-0 h-full bg-[#1E3A5F] text-white z-40
              transition-all duration-300 flex flex-col"
       :class="sidebarOpen?'w-64':'w-16'">

    <div class="flex items-center gap-3 px-4 py-5 border-b border-white/10">
        <div class="w-12 h-12 rounded-x1 overflow-hidden bg-white/10 flex items-center justify-center shrink-0">
            <img src="{{ asset('image/logo_cbn.jpg') }}" 
                alt="Logo PT CBN" 
                class="w-full h-full object-contain">
        </div>
        <div x-show="sidebarOpen" x-transition.opacity>
            <p class="text-[12px] font-black uppercase tracking-widest text-white/90 leading-none">PT Citra Bangun Nagari</p>
            <p class="text-[9px] text-white/50 font-medium mt-0.5">Admin Panel</p>
        </div>
    </div>

    <nav class="flex-1 py-4 overflow-y-auto">

        <div class="px-3 mb-2">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                      {{ request()->routeIs('admin.dashboard') ? 'bg-white/15 text-white' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-[11px] font-black uppercase tracking-wider">Dashboard</span>
            </a>
        </div>

        <div x-show="sidebarOpen" class="px-6 pt-4 pb-1">
            <p class="text-[9px] font-black text-white/30 uppercase tracking-widest">Master Data</p>
        </div>
        <div class="px-3 space-y-0.5">
            <a href="{{ route('admin.karyawan.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                      {{ request()->routeIs('admin.karyawan.*') ? 'bg-white/15 text-white' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="users" class="w-4 h-4 shrink-0"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-[11px] font-black uppercase tracking-wider">Karyawan</span>
            </a>
            <a href="{{ route('admin.mitra.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                      {{ request()->routeIs('admin.mitra.*') ? 'bg-white/15 text-white' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="building" class="w-4 h-4 shrink-0"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-[11px] font-black uppercase tracking-wider">Mitra</span>
            </a>
            <a href="{{ route('admin.penempatan.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                      {{ request()->routeIs('admin.penempatan.*') ? 'bg-white/15 text-white' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="map-pin" class="w-4 h-4 shrink-0"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-[11px] font-black uppercase tracking-wider">Penempatan</span>
            </a>
        </div>

        <div x-show="sidebarOpen" class="px-6 pt-5 pb-1">
            <p class="text-[9px] font-black text-white/30 uppercase tracking-widest">Payroll</p>
        </div>
        <div class="px-3 space-y-0.5">
            <a href="{{ route('admin.komponen-gaji.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                      {{ request()->routeIs('admin.komponen-gaji.*') ? 'bg-white/15 text-white' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="wallet" class="w-4 h-4 shrink-0"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-[11px] font-black uppercase tracking-wider">Komponen Gaji</span>
            </a>

            <a href="{{ route('admin.penggajian.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                      {{ request()->routeIs('admin.penggajian.*') ? 'bg-white/15 text-white' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="calculator" class="w-4 h-4 shrink-0"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-[11px] font-black uppercase tracking-wider">Proses Gaji</span>
            </a>
        </div>

        <div x-show="sidebarOpen" class="px-6 pt-5 pb-1">
            <p class="text-[9px] font-black text-white/30 uppercase tracking-widest">Laporan</p>
        </div>
        <div class="px-3 space-y-0.5">
            <a href="{{ route('admin.laporan.absensi.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                      {{ request()->routeIs('admin.laporan.absensi.*') ? 'bg-white/15 text-white' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="file-text" class="w-4 h-4 shrink-0"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-[11px] font-black uppercase tracking-wider">Laporan Absensi</span>
            </a>
            <a href="{{ route('admin.laporan.gaji.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                      {{ request()->routeIs('admin.laporan.gaji.*') ? 'bg-white/15 text-white' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="bar-chart-2" class="w-4 h-4 shrink-0"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-[11px] font-black uppercase tracking-wider">Laporan Gaji</span>
            </a>
        </div>

    </nav>

    <div class="border-t border-white/10 p-3">
        <div class="flex items-center gap-3 px-3 py-2">
            <div class="w-8 h-8 bg-red-500 rounded-xl flex items-center justify-center shrink-0 font-black text-xs text-white">
                {{ strtoupper(substr(Auth::user()->username,0,2)) }}
            </div>
            <div x-show="sidebarOpen" x-transition.opacity class="flex-1 min-w-0">
                <p class="text-[11px] font-black text-white truncate uppercase">{{ Auth::user()->username }}</p>
                <p class="text-[9px] text-white/40 font-medium">Administrator</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" x-show="sidebarOpen">
                @csrf
                <button type="submit" class="text-white/40 hover:text-red-400 transition-colors">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<div class="transition-all duration-300" :class="sidebarOpen?'ml-64':'ml-16'">
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-sm border-b border-gray-100
                px-6 py-3 flex items-center gap-4">
        <button @click="sidebarOpen=!sidebarOpen"
                class="text-gray-400 hover:text-[#1E3A5F] transition-colors">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>
        <div class="flex-1"></div>
        <span class="text-[11px] font-semibold text-gray-400">
            {{ now()->translatedFormat('l, d F Y') }}
        </span>
    </div>

    <div class="px-6 pt-5">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-2xl
                        text-green-700 text-sm font-semibold flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-2xl
                        text-red-700 text-sm font-semibold flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                {{ session('error') }}
            </div>
        @endif
    </div>

    <main class="px-6 pb-8">@yield('content')</main>
</div>

<script>lucide.createIcons();</script>
@stack('scripts')
</body>
</html>