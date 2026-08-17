@extends('pimpinan.sidebar')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="space-y-6" x-data="{ openAddModal: {{ $errors->any() ? 'true' : 'false' }} }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#1E3A5F] ">Manajemen Pengguna</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola akun Pimpinan dan Admin yang memiliki hak akses sistem.</p>
        </div>
        <button @click="openAddModal = true" 
                class="flex items-center justify-center gap-2 px-5 py-3 bg-[#1E3A5F] text-white rounded-2xl font-black text-xs hover:bg-blue-900 transition-all shadow-lg shadow-blue-900/20">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            Tambah Pengguna
        </button>
    </div>

    @if(session('success_user'))
        @php $u = session('success_user'); @endphp
        <div class="p-6 bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border border-emerald-200/80 rounded-3xl shadow-sm relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-500 text-white rounded-2xl flex items-center justify-center shadow-md shadow-emerald-500/20 shrink-0">
                        <i data-lucide="check" class="w-6 h-6 stroke-[3]"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-emerald-950">Akun {{ $u['role'] }} Baru Berhasil Dibuat!</h4>
                        <p class="text-xs font-semibold text-emerald-800/80 mt-0.5"><span class="font-black text-emerald-900">{{ $u['nama'] }}</span> telah terdaftar secara resmi di sistem.</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="px-4 py-2 bg-white/90 border border-emerald-200/80 rounded-2xl text-xs font-black text-[#1E3A5F] shadow-sm">
                        <span class="text-gray-400 font-bold text-[9px] uppercase tracking-wider block mb-0.5">NIP</span>
                        {{ $u['nip'] }}
                    </div>
                    <div class="px-4 py-2 bg-white/90 border border-emerald-200/80 rounded-2xl text-xs font-black text-emerald-700 shadow-sm">
                        <span class="text-gray-400 font-bold text-[9px] uppercase tracking-wider block mb-0.5">Email Login</span>
                        {{ $u['email'] }}
                    </div>
                </div>
            </div>
        </div>
    @elseif(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold space-y-1">
            @foreach($errors->all() as $err)
                <p class="flex items-center gap-2"><i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i> {{ $err }}</p>
            @endforeach
        </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $totalUsers = $users->count();
            $totalPimpinan = $users->where('role.slug', 'pimpinan')->count();
            $totalAdmin = $users->where('role.slug', 'admin')->count();
            $totalActive = $users->where('is_active', true)->count();
        @endphp
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm text-center hover:-translate-y-1 transition-all duration-300">
            <p class="text-[12px] font-black text-gray-400 mb-1">Total Pengguna</p>
            <p class="text-2xl font-black text-[#1E3A5F]">{{ $totalUsers }}</p>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm border-l-4 border-l-blue-500 text-center hover:-translate-y-1 transition-all duration-300">
            <p class="text-[12px] font-black text-gray-400 mb-1">Pimpinan</p>
            <p class="text-2xl font-black text-blue-600">{{ $totalPimpinan }}</p>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm border-l-4 border-l-teal-500 text-center hover:-translate-y-1 transition-all duration-300">
            <p class="text-[12px] font-black text-gray-400 mb-1">Admin</p>
            <p class="text-2xl font-black text-teal-600">{{ $totalAdmin }}</p>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm border-l-4 border-l-green-500 text-center hover:-translate-y-1 transition-all duration-300">
            <p class="text-[12px] font-black text-gray-400 mb-1">Aktif</p>
            <p class="text-2xl font-black text-green-600">{{ $totalActive }}</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] tracking-tight">Identitas</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] tracking-tight">Role</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] tracking-tight">Jabatan / Divisi</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] text-center">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50 transition-colors {{ $user->id === auth()->id() ? 'bg-blue-50/30' : '' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-[#1E3A5F] text-white rounded-xl flex items-center justify-center font-black text-xs shrink-0">
                                    {{ substr($user->nama, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-black text-gray-800 line-clamp-1">{{ $user->nama }}</p>
                                    <p class="text-[10px] font-bold text-[#1E3A5F]">{{ $user->nip }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-black
                                       {{ $user->role->slug === 'pimpinan' ? 'bg-blue-100 text-blue-700' : 'bg-teal-100 text-teal-700' }}">
                                {{ $user->role->slug }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-xs font-bold text-gray-700">{{ $user->jabatan ?? '-' }}</p>
                            <p class="text-[10px] font-medium text-gray-400">{{ $user->divisi ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($user->is_active)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-50 text-green-600 text-[9px] font-black">
                                    <span class="w-1 h-1 rounded-full bg-green-600 animate-pulse"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-50 text-red-600 text-[9px] font-black">
                                    <span class="w-1 h-1 rounded-full bg-red-600"></span>
                                    Non-Aktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('pimpinan.admin.toggle', $user->id) }}" method="POST" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin mengubah status akun ini?')">
                                        @csrf @method('PATCH')
                                        <button type="submit" 
                                                class="p-2 {{ $user->is_active ? 'text-orange-500 hover:bg-orange-50' : 'text-green-500 hover:bg-green-50' }} rounded-xl transition-all"
                                                title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i data-lucide="{{ $user->is_active ? 'user-minus' : 'user-check' }}" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[9px] font-black text-blue-400">Akun Anda</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <p class="text-sm font-medium text-gray-400 italic">Belum ada data pengguna.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah -->
    <template x-teleport="body">
        <div x-show="openAddModal" 
             class="fixed inset-0 z-[3000] overflow-y-auto"
             style="display: none;"
             x-cloak>
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" 
                 x-show="openAddModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="openAddModal = false"></div>

            <!-- Wrapper to center the modal content -->
            <div class="flex min-h-full items-center justify-center p-4 md:p-6 text-center">
                
                <!-- Modal Card -->
                <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-xl text-left overflow-hidden z-[2200] transform transition-all my-8 inline-block align-middle"
                     x-show="openAddModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    
                    <div class="p-8 md:p-10">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-black text-[#1E3A5F]">Daftarkan Pengguna</h3>
                                <p class="text-[10px] text-gray-500 mt-0.5 font-bold">Akses Admin & Pimpinan</p>
                            </div>
                            <button @click="openAddModal = false" class="p-2 bg-gray-50 rounded-xl text-gray-400 hover:text-gray-600 transition-all">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        @if($errors->any() || session('error'))
                            <div class="mb-5 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold space-y-1">
                                @if(session('error'))
                                    <p class="flex items-center gap-2"><i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i> {{ session('error') }}</p>
                                @endif
                                @foreach($errors->all() as $err)
                                    <p class="flex items-center gap-2"><i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i> {{ $err }}</p>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('pimpinan.admin.store') }}" method="POST" class="space-y-4">
                            @csrf
                            
                            <!-- Dummy inputs to trick browser autofill -->
                            <input type="text" style="display:none" autocomplete="off" />
                            <input type="password" style="display:none" autocomplete="new-password" />

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="col-span-1 md:col-span-2">
                                    <label class="block text-[10px] font-black text-gray-400 mb-1.5 ml-1">Nama Lengkap</label>
                                    <input type="text" name="nama" value="{{ old('nama') }}" required autocomplete="off"
                                           class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-[#1E3A5F] transition-all text-sm font-bold text-gray-700"
                                           placeholder="Nama Sesuai KTP">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 mb-1.5 ml-1">Hak Akses (Role)</label>
                                    <select name="role_slug" required autocomplete="off"
                                            class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-[#1E3A5F] transition-all text-sm font-bold text-gray-700">
                                        <option value="admin" {{ old('role_slug') === 'admin' ? 'selected' : '' }}>ADMIN (Operasional)</option>
                                        <option value="pimpinan" {{ old('role_slug') === 'pimpinan' ? 'selected' : '' }}>PIMPINAN (Monitoring)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 mb-1.5 ml-1">Nomor HP</label>
                                    <input type="text" name="no_hp" value="{{ old('no_hp') }}" required autocomplete="off"
                                           class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-[#1E3A5F] transition-all text-sm font-bold text-gray-700"
                                           placeholder="0812xxxx">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 mb-1.5 ml-1">Divisi</label>
                                    <select name="divisi" required autocomplete="off"
                                            class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-[#1E3A5F] transition-all text-sm font-bold text-gray-700">
                                        <option value="Administrasi & Umum" {{ old('divisi') === 'Administrasi & Umum' ? 'selected' : '' }}>Administrasi & Umum</option>
                                        <option value="Keuangan" {{ old('divisi') === 'Keuangan' ? 'selected' : '' }}>Keuangan</option>
                                        <option value="HC" {{ old('divisi') === 'HC' ? 'selected' : '' }}>Human Capital (HC)</option>
                                        <option value="Umum" {{ old('divisi') === 'Umum' ? 'selected' : '' }}>Umum</option>
                                        <option value="Manajemen" {{ old('divisi') === 'Manajemen' ? 'selected' : '' }}>Manajemen</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 mb-1.5 ml-1">Jabatan</label>
                                    <input type="text" name="jabatan" value="{{ old('jabatan') }}" required autocomplete="off"
                                           class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-[#1E3A5F] transition-all text-sm font-bold text-gray-700"
                                           placeholder="Contoh: Staff Admin">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 mb-1.5 ml-1">Password Baru</label>
                                    <input type="password" name="password" required autocomplete="new-password"
                                           class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-[#1E3A5F] transition-all text-sm font-bold text-gray-700"
                                           placeholder="Min. 8 Karakter">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 mb-1.5 ml-1">Ulangi Password</label>
                                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                                           class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-[#1E3A5F] transition-all text-sm font-bold text-gray-700"
                                           placeholder="Konfirmasi Password">
                                </div>
                            </div>

                            <div class="pt-6 flex gap-4">
                                <button type="button" @click="openAddModal = false"
                                        class="flex-1 px-5 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-xs hover:bg-gray-200 transition-all">
                                    Batal
                                </button>
                                <button type="submit"
                                        class="flex-1 px-5 py-4 bg-[#1E3A5F] text-white rounded-2xl font-black text-xs hover:bg-blue-900 transition-all shadow-xl shadow-blue-900/20">
                                    Simpan Pengguna
                                </button>
                            </div>
                            <p class="text-[9px] text-gray-400 text-center font-bold">
                                <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
                                Username akan dibuat otomatis oleh sistem.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
