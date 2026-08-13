<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\Configuration;
use App\Models\KomponenGaji;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConfigurationControllerTest extends TestCase
{
    use RefreshDatabase;

    private function seedBaseData(): void
    {
        Role::create(['nama_role' => 'Administrator',     'slug' => 'admin']);
        Role::create(['nama_role' => 'Pimpinan',          'slug' => 'pimpinan']);
        Role::create(['nama_role' => 'Karyawan Tetap',    'slug' => 'karyawan_tetap']);
        Role::create(['nama_role' => 'Karyawan Kontrak',  'slug' => 'karyawan_kontrak']);

        // Komponen Gaji Master (diperlukan oleh ConfigurationController@index)
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
            ['key' => 'umr_tahun_ini',         'label' => 'UMR Sumatera Barat',   'value' => '2994031', 'type' => 'number', 'group' => 'gaji'],
            ['key' => 'uang_makan_default',    'label' => 'Uang Makan Harian',    'value' => '35000',   'type' => 'number', 'group' => 'gaji'],
            ['key' => 'uang_transport_default', 'label' => 'Uang Transport Harian', 'value' => '45000', 'type' => 'number', 'group' => 'gaji'],
            ['key' => 'persen_bpjs_kes',       'label' => 'BPJS Kesehatan',        'value' => '9.24',   'type' => 'percent', 'group' => 'gaji'],
            ['key' => 'persen_bpjs_tk',        'label' => 'BPJS Ketenagakerjaan',  'value' => '5.00',   'type' => 'percent', 'group' => 'gaji'],
            ['key' => 'kuota_cuti_tahunan',    'label' => 'Kuota Cuti Tahunan',    'value' => '12',     'type' => 'number', 'group' => 'cuti'],
            ['key' => 'batas_tanggal_gaji',    'label' => 'Batas Tgl Gaji',        'value' => '25',     'type' => 'number', 'group' => 'gaji'],
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

    public function test_guest_tidak_bisa_mengakses_konfigurasi(): void
    {
        $response = $this->get(route('admin.konfigurasi.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_tidak_bisa_mengakses_konfigurasi(): void
    {
        $this->seedBaseData();
        $karyawan = $this->buatKaryawan();

        $response = $this->actingAs($karyawan)->get(route('admin.konfigurasi.index'));

        $response->assertStatus(403);
    }

    public function test_admin_dapat_mengakses_halaman_konfigurasi(): void
    {
        $this->seedBaseData();
        $admin = $this->buatAdmin();

        $response = $this->actingAs($admin)->get(route('admin.konfigurasi.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.configurations.index');
        $response->assertViewHas('configs');
        $response->assertViewHas('masterComponents');
    }

    public function test_admin_dapat_memperbarui_konfigurasi_umr(): void
    {
        $this->seedBaseData();
        $admin = $this->buatAdmin();

        $response = $this->actingAs($admin)->post(
            route('admin.konfigurasi.update'),
            [
                'umr_tahun_ini' => '3200000',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('configurations', [
            'key'   => 'umr_tahun_ini',
            'value' => '3200000',
        ]);
    }

    public function test_admin_dapat_memperbarui_konfigurasi_cuti(): void
    {
        $this->seedBaseData();
        $admin = $this->buatAdmin();

        $response = $this->actingAs($admin)->post(
            route('admin.konfigurasi.update'),
            [
                'kuota_cuti_tahunan' => '14',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('configurations', [
            'key'   => 'kuota_cuti_tahunan',
            'value' => '14',
        ]);
    }

    public function test_admin_dapat_memperbarui_beberapa_konfigurasi_sekaligus(): void
    {
        $this->seedBaseData();
        $admin = $this->buatAdmin();

        $response = $this->actingAs($admin)->post(
            route('admin.konfigurasi.update'),
            [
                'umr_tahun_ini'      => '3500000',
                'uang_makan_default' => '40000',
                'persen_bpjs_kes'    => '10.00',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('configurations', ['key' => 'umr_tahun_ini', 'value' => '3500000']);
        $this->assertDatabaseHas('configurations', ['key' => 'uang_makan_default', 'value' => '40000']);
        $this->assertDatabaseHas('configurations', ['key' => 'persen_bpjs_kes', 'value' => '10.00']);
    }
}
