<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: Buat data role dasar yang dibutuhkan sistem.
     */
    private function seedRoles(): void
    {
        Role::create(['nama_role' => 'Administrator',     'slug' => 'admin']);
        Role::create(['nama_role' => 'Pimpinan',          'slug' => 'pimpinan']);
        Role::create(['nama_role' => 'Karyawan Tetap',    'slug' => 'karyawan_tetap']);
        Role::create(['nama_role' => 'Karyawan Kontrak',  'slug' => 'karyawan_kontrak']);
    }

    /**
     * Helper: Buat user dengan role tertentu.
     */
    private function buatUser(string $roleSlug, array $overrides = []): User
    {
        $role = Role::where('slug', $roleSlug)->first();

        return User::create(array_merge([
            'role_id'        => $role->id,
            'nip'            => 'NIP-' . strtoupper($roleSlug) . '-001',
            'password'       => Hash::make('password123'),
            'nama'           => 'Test ' . ucfirst($roleSlug),
            'email'          => $roleSlug . '@test.com',
            'divisi'         => 'umum',
            'jabatan'        => 'Staff',
            'tanggal_masuk'  => now(),
            'is_active'      => true,
        ], $overrides));
    }

    // ─────────────────────────────────────────────────────────────────
    // TEST CASES
    // ─────────────────────────────────────────────────────────────────

    public function test_menampilkan_halaman_login(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_redirect_admin_jika_sudah_login(): void
    {
        $this->seedRoles();
        $admin = $this->buatUser('admin');

        $response = $this->actingAs($admin)->get(route('login'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_redirect_pimpinan_jika_sudah_login(): void
    {
        $this->seedRoles();
        $pimpinan = $this->buatUser('pimpinan');

        $response = $this->actingAs($pimpinan)->get(route('login'));

        $response->assertRedirect(route('pimpinan.dashboard'));
    }

    public function test_redirect_karyawan_tetap_jika_sudah_login(): void
    {
        $this->seedRoles();
        $karyawan = $this->buatUser('karyawan_tetap');

        $response = $this->actingAs($karyawan)->get(route('login'));

        $response->assertRedirect(route('karyawan.dashboard'));
    }

    public function test_login_sukses_dengan_kredensial_valid(): void
    {
        $this->seedRoles();
        $admin = $this->buatUser('admin', [
            'email'    => 'admin@cbn.test',
            'password' => Hash::make('rahasia123'),
        ]);

        $response = $this->post(route('login.post'), [
            'email'    => 'admin@cbn.test',
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_gagal_dengan_email_salah(): void
    {
        $this->seedRoles();
        $this->buatUser('admin', [
            'email'    => 'admin@cbn.test',
            'password' => Hash::make('rahasia123'),
        ]);

        $response = $this->post(route('login.post'), [
            'email'    => 'salah@cbn.test',
            'password' => 'rahasia123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_gagal_dengan_password_salah(): void
    {
        $this->seedRoles();
        $this->buatUser('admin', [
            'email'    => 'admin@cbn.test',
            'password' => Hash::make('rahasia123'),
        ]);

        $response = $this->post(route('login.post'), [
            'email'    => 'admin@cbn.test',
            'password' => 'passwordsalah',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_gagal_jika_akun_non_aktif(): void
    {
        $this->seedRoles();
        $this->buatUser('admin', [
            'email'     => 'nonaktif@cbn.test',
            'password'  => Hash::make('rahasia123'),
            'is_active' => false,
        ]);

        $response = $this->post(route('login.post'), [
            'email'    => 'nonaktif@cbn.test',
            'password' => 'rahasia123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_validasi_email_wajib_diisi(): void
    {
        $response = $this->post(route('login.post'), [
            'email'    => '',
            'password' => 'rahasia123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_validasi_password_wajib_diisi(): void
    {
        $response = $this->post(route('login.post'), [
            'email'    => 'admin@cbn.test',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_logout_menghapus_session(): void
    {
        $this->seedRoles();
        $admin = $this->buatUser('admin');

        $this->actingAs($admin);
        $this->assertAuthenticatedAs($admin);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
