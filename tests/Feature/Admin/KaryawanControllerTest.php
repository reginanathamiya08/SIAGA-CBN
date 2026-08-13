<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\Configuration;
use App\Models\KomponenGaji;
use App\Models\KuotaPerizinan;
use App\Models\DetailGajiKomponen;
use App\Models\JenisKaryawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KaryawanControllerTest extends TestCase
{
    use RefreshDatabase;

    private function seedBaseData(): void
    {
        // Roles
        Role::create(['nama_role' => 'Administrator',     'slug' => 'admin']);
        Role::create(['nama_role' => 'Pimpinan',          'slug' => 'pimpinan']);
        Role::create(['nama_role' => 'Karyawan Tetap',    'slug' => 'karyawan_tetap']);
        Role::create(['nama_role' => 'Karyawan Kontrak',  'slug' => 'karyawan_kontrak']);

        // Komponen Gaji Master
        $komponenList = [
            ['id' => 'MKG-00001', 'nama_komponen' => 'Gaji Pokok',           'tipe' => 'pendapatan'],
            ['id' => 'MKG-00002', 'nama_komponen' => 'Tunjangan Pangan',     'tipe' => 'pendapatan'],
            ['id' => 'MKG-00003', 'nama_komponen' => 'Uang Makan',           'tipe' => 'pendapatan'],
            ['id' => 'MKG-00004', 'nama_komponen' => 'Uang Transport',       'tipe' => 'pendapatan'],
            ['id' => 'MKG-00005', 'nama_komponen' => 'Tunjangan Jamsostek',  'tipe' => 'pendapatan'],
            ['id' => 'MKG-00006', 'nama_komponen' => 'Tunjangan Askes',      'tipe' => 'pendapatan'],
            ['id' => 'MKG-00007', 'nama_komponen' => 'Potongan Telat',       'tipe' => 'potongan'],
            ['id' => 'MKG-00008', 'nama_komponen' => 'Potongan Izin/Alfa',   'tipe' => 'potongan'],
            ['id' => 'MKG-00009', 'nama_komponen' => 'Potongan BPJS Kes',    'tipe' => 'potongan'],
            ['id' => 'MKG-00010', 'nama_komponen' => 'Potongan BPJS TK',     'tipe' => 'potongan'],
        ];
        foreach ($komponenList as $k) {
            KomponenGaji::firstOrCreate(['id' => $k['id']], $k);
        }

        // Configurations
        $configs = [
            ['key' => 'umr_tahun_ini',        'label' => 'UMR', 'value' => '2994031', 'type' => 'number', 'group' => 'gaji'],
            ['key' => 'uang_makan_default',    'label' => 'Uang Makan', 'value' => '35000', 'type' => 'number', 'group' => 'gaji'],
            ['key' => 'uang_transport_default', 'label' => 'Uang Transport', 'value' => '45000', 'type' => 'number', 'group' => 'gaji'],
            ['key' => 'persen_bpjs_kes',       'label' => 'BPJS Kes', 'value' => '9.24', 'type' => 'percent', 'group' => 'gaji'],
            ['key' => 'persen_bpjs_tk',        'label' => 'BPJS TK', 'value' => '5.00', 'type' => 'percent', 'group' => 'gaji'],
            ['key' => 'kuota_cuti_tahunan',    'label' => 'Kuota Cuti', 'value' => '12', 'type' => 'number', 'group' => 'cuti'],
            ['key' => 'batas_tanggal_gaji',    'label' => 'Batas Tgl Gaji', 'value' => '25', 'type' => 'number', 'group' => 'gaji'],
        ];
        foreach ($configs as $c) {
            Configuration::firstOrCreate(['key' => $c['key']], $c);
        }
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

    private function buatKaryawan(): User
    {
        $role = Role::where('slug', 'karyawan_tetap')->first();
        return User::create([
            'role_id'           => $role->id,
            'nip'               => 'KT-CBN-0001',
            'password'          => Hash::make('karyawan123'),
            'nama'              => 'Karyawan Test',
            'email'             => 'karyawan@cbn.test',
            'divisi'            => 'keuangan',
            'jabatan'           => 'Staff Keuangan',
            'tanggal_masuk'     => now(),
            'is_active'         => true,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // TEST CASES
    // ─────────────────────────────────────────────────────────────────

    public function test_guest_tidak_bisa_mengakses_manajemen_karyawan(): void
    {
        $response = $this->get(route('admin.karyawan.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_tidak_bisa_mengakses_manajemen_karyawan(): void
    {
        $this->seedBaseData();
        $karyawan = $this->buatKaryawan();

        $response = $this->actingAs($karyawan)->get(route('admin.karyawan.index'));

        $response->assertStatus(403);
    }

    public function test_admin_dapat_melihat_daftar_karyawan(): void
    {
        $this->seedBaseData();
        $admin    = $this->buatAdmin();
        $karyawan = $this->buatKaryawan();

        $response = $this->actingAs($admin)->get(route('admin.karyawan.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.karyawan.index');
        $response->assertViewHas('karyawan');
        $response->assertViewHas('stats');
    }

    public function test_admin_dapat_melihat_form_tambah_karyawan(): void
    {
        $this->seedBaseData();
        $admin = $this->buatAdmin();

        $response = $this->actingAs($admin)->get(route('admin.karyawan.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.karyawan.create');
    }

    public function test_admin_dapat_melihat_detail_karyawan(): void
    {
        $this->seedBaseData();
        $admin    = $this->buatAdmin();
        $karyawan = $this->buatKaryawan();

        $response = $this->actingAs($admin)->get(route('admin.karyawan.show', $karyawan->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.karyawan.show');
    }

    public function test_admin_dapat_toggle_status_karyawan(): void
    {
        $this->seedBaseData();
        $admin    = $this->buatAdmin();
        $karyawan = $this->buatKaryawan();

        $this->assertTrue($karyawan->is_active);

        $response = $this->actingAs($admin)->patch(
            route('admin.karyawan.toggle-status', $karyawan->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $karyawan->refresh();
        $this->assertFalse($karyawan->is_active);
    }

    public function test_admin_dapat_mereset_password_karyawan(): void
    {
        $this->seedBaseData();
        $admin    = $this->buatAdmin();
        $karyawan = $this->buatKaryawan();

        $passwordLama = $karyawan->password;

        $response = $this->actingAs($admin)->patch(
            route('admin.karyawan.reset-password', $karyawan->id),
            [
                'password_baru'              => 'passwordbaru123',
                'password_baru_confirmation' => 'passwordbaru123',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $karyawan->refresh();
        $this->assertTrue(Hash::check('passwordbaru123', $karyawan->password));
    }

    public function test_reset_password_gagal_jika_konfirmasi_tidak_cocok(): void
    {
        $this->seedBaseData();
        $admin    = $this->buatAdmin();
        $karyawan = $this->buatKaryawan();

        $response = $this->actingAs($admin)->patch(
            route('admin.karyawan.reset-password', $karyawan->id),
            [
                'password_baru'              => 'passwordbaru123',
                'password_baru_confirmation' => 'tidakcocok',
            ]
        );

        $response->assertSessionHasErrors('password_baru');
    }

    public function test_admin_dapat_filter_karyawan_berdasarkan_jenis(): void
    {
        $this->seedBaseData();
        $admin    = $this->buatAdmin();
        $karyawan = $this->buatKaryawan();

        $response = $this->actingAs($admin)->get(
            route('admin.karyawan.index', ['jenis' => 'JNS-00001'])
        );

        $response->assertStatus(200);
        $response->assertViewHas('karyawan');
    }
}
