<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Services\UsernameGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    protected $usernameService;

    public function __construct(UsernameGeneratorService $usernameService)
    {
        $this->usernameService = $usernameService;
    }

    public function index()
    {
        // Ambil semua user dengan role admin dan pimpinan
        $users = User::whereHas('role', function($q) {
            $q->whereIn('slug', ['admin', 'pimpinan']);
        })->orderBy('created_at', 'desc')->get();

        return view('pimpinan.admin.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'      => 'required|string|max:255',
            'role_slug' => 'required|in:admin,pimpinan',
            'divisi'    => 'required|string|max:100',
            'jabatan'   => 'required|string|max:100',
            'no_hp'     => 'required|string|max:20',
            'password'  => 'required|string|min:8|confirmed',
        ]);

        $role = Role::where('slug', $request->role_slug)->first();
        if (!$role) {
            return back()->with('error', 'Role tidak ditemukan.');
        }

        // Generate NIP & Email Ringkas Otomatis (Format: namadepan.adm@cbn.com)
        $username  = $this->usernameService->generate($request->role_slug);
        
        // Ambil kata pertama dari nama depan (misal: "Gifra")
        $baseName = strtolower(explode(' ', trim(preg_replace('/[^a-zA-Z]/', ' ', $request->nama)))[0] ?? 'user');
        $roleTag  = $request->role_slug === 'admin' ? 'adm' : 'pm';
        $email    = $baseName . '.' . $roleTag . '@cbn.com';
        $counter  = 1;
        while (User::where('email', $email)->exists()) {
            $counter++;
            $email = $baseName . '.' . $roleTag . $counter . '@cbn.com';
        }

        User::create([
            'role_id'        => $role->id,
            'nip'            => $username,
            'email'          => $email,
            'password'       => Hash::make($request->password),
            'nama'           => trim($request->nama),
            'divisi'         => $request->divisi,
            'jabatan'        => $request->jabatan,
            'no_hp'          => $request->no_hp,
            'is_active'      => true,
            'tanggal_masuk'  => now(),
        ]);

        $label = $request->role_slug === 'admin' ? 'Admin' : 'Pimpinan';
        return back()->with('success_user', [
            'nama'  => trim($request->nama),
            'role'  => $label,
            'nip'   => $username,
            'email' => $email,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama'     => 'required|string|max:255',
            'jabatan'  => 'required|string|max:100',
            'divisi'   => 'nullable|string|max:100',
            'no_hp'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
        ]);

        $data = [
            'nama'    => trim($request->nama),
            'jabatan' => trim($request->jabatan),
            'divisi'  => $request->divisi ?? $user->divisi,
            'no_hp'   => $request->no_hp ?? $user->no_hp,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', "Data pengguna {$user->nama} berhasil diperbarui.");
    }

    public function toggleStatus(User $user)
    {
        // PROTEKSI: Tidak boleh menonaktifkan diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        // PROTEKSI: Jika Pimpinan, pastikan minimal ada 1 pimpinan lain yang aktif
        if ($user->role->slug === 'pimpinan' && $user->is_active) {
            $activePimpinanCount = User::whereHas('role', function($q) {
                $q->where('slug', 'pimpinan');
            })->where('is_active', true)->count();

            if ($activePimpinanCount <= 1) {
                return back()->with('error', 'Gagal! Harus ada minimal satu Pimpinan yang aktif di sistem.');
            }
        }

        $user->update([
            'is_active' => !$user->is_active
        ]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $roleLabel = $user->role->slug === 'admin' ? 'Admin' : 'Pimpinan';
        
        return back()->with('success', "Akun $roleLabel $user->nip berhasil $status.");
    }

    public function destroy(User $user)
    {
        // PROTEKSI: Tidak boleh hapus diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Cek apakah user ini punya riwayat transaksi (opsional, untuk keamanan 3NF)
        // Jika ada riwayat absensi atau gaji, sebaiknya jangan dihapus tapi dinonaktifkan
        if ($user->absensi()->exists() || $user->slipGaji()->exists()) {
            return back()->with('error', 'User ini memiliki riwayat transaksi dan tidak bisa dihapus. Silakan nonaktifkan saja.');
        }

        $user->delete();
        return back()->with('success', "Akun berhasil dihapus.");
    }
}
