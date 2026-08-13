<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\Mitra;
use App\Models\Absensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private function seedBaseData(): void
    {
        Role::create(['nama_role' => 'Administrator',     'slug' => 'admin']);
        Role::create(['nama_role' => 'Pimpinan',          'slug' => 'pimpinan']);
        Role::create(['nama_role' => 'Karyawan Tetap',    'slug' => 'karyawan_tetap']);
        Role::create(['nama_role' => 'Karyawan Kontrak',  'slug' => 'karyawan_kontrak']);
    }

    private function buatAdmin(): User
    {
        $role = Role::where('slug', 'admin')->first();
        return User::create([
            'role_id'        => $role->id,
            'nip'            => 'ADM-CBN-0001',
            'password'       => Hash::make('admin123'),
            'nama'           => 'Administrator',
            'email'          => 'admin@cbn.test',
            'divisi'         => 'adm_umum',
            'jabatan'        => 'Staff Administrasi & Umum',
            'tanggal_masuk'  => now(),
            'is_active'      => true,
        ]);
    }

    private function buatKaryawan(string $roleSlug = 'karyawan_tetap', string $nip = 'KT-CBN-0001'): User
    {
        $role = Role::where('slug', $roleSlug)->first();
        return User::create([
            'role_id'       => $role->id,
            'nip'           => $nip,
            'password'      => Hash::make('test123'),
            'nama'          => 'Karyawan ' . $nip,
            'email'         => strtolower($nip) . '@cbn.test',
            'divisi'        => 'umum',
            'jabatan'       => 'Staff',
            'tanggal_masuk' => now()->subYear(),
            'is_active'     => true,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────

    public function test_guest_tidak_bisa_mengakses_dashboard_admin(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_tidak_bisa_mengakses_dashboard_admin(): void
    {
        $this->seedBaseData();
        $karyawan = $this->buatKaryawan();

        $response = $this->actingAs($karyawan)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_admin_bisa_mengakses_dashboard(): void
    {
        $this->seedBaseData();
        $admin = $this->buatAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    public function test_dashboard_menampilkan_statistik_karyawan(): void
    {
        $this->seedBaseData();
        $admin = $this->buatAdmin();

        // Buat 2 karyawan tetap dan 1 kontrak
        $this->buatKaryawan('karyawan_tetap', 'KT-CBN-0001');
        $this->buatKaryawan('karyawan_tetap', 'KT-CBN-0002');
        $this->buatKaryawan('karyawan_kontrak', 'KK-HC-0001');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['karyawan_tetap'] === 2
                && $stats['karyawan_kontrak'] === 1;
        });
    }

    public function test_dashboard_menghitung_kehadiran_hari_ini(): void
    {
        $this->seedBaseData();
        $admin = $this->buatAdmin();

        $karyawan1 = $this->buatKaryawan('karyawan_tetap', 'KT-CBN-0001');
        $karyawan2 = $this->buatKaryawan('karyawan_tetap', 'KT-CBN-0002');

        $mitra = Mitra::create([
            'nama_mitra'   => 'PT CBN Pusat',
            'latitude'     => -0.9471,
            'longitude'    => 100.4172,
            'radius_meter' => 200,
            'is_pusat'     => true,
        ]);

        // Karyawan 1: hadir, karyawan 2: telat
        Absensi::create([
            'user_id'     => $karyawan1->id,
            'mitra_id'    => $mitra->id,
            'tanggal'     => Carbon::today(),
            'waktu_masuk' => Carbon::today()->setTime(8, 0),
            'status'      => 'hadir',
            'is_telat'    => false,
        ]);

        Absensi::create([
            'user_id'     => $karyawan2->id,
            'mitra_id'    => $mitra->id,
            'tanggal'     => Carbon::today(),
            'waktu_masuk' => Carbon::today()->setTime(9, 30),
            'status'      => 'telat',
            'is_telat'    => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('hadirHariIni', 2); // hadir + telat = 2
        $response->assertViewHas('telatHariIni', 1);
    }
}
