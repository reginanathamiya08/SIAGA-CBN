<?php

namespace Tests\Feature\Pimpinan;

use App\Models\Role;
use App\Models\User;
use App\Models\PeriodeGaji;
use App\Models\SlipGajiPeriode;
use App\Models\KomponenGaji;
use App\Models\Configuration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MonitoringGajiControllerTest extends TestCase
{
    use RefreshDatabase;

    private function seedBaseData(): void
    {
        Role::create(['nama_role' => 'Administrator',     'slug' => 'admin']);
        Role::create(['nama_role' => 'Pimpinan',          'slug' => 'pimpinan']);
        Role::create(['nama_role' => 'Karyawan Tetap',    'slug' => 'karyawan_tetap']);
        Role::create(['nama_role' => 'Karyawan Kontrak',  'slug' => 'karyawan_kontrak']);

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

        $configs = [
            ['key' => 'umr_tahun_ini',         'label' => 'UMR',            'value' => '2994031', 'type' => 'number', 'group' => 'gaji'],
            ['key' => 'uang_makan_default',    'label' => 'Uang Makan',     'value' => '35000',   'type' => 'number', 'group' => 'gaji'],
            ['key' => 'uang_transport_default', 'label' => 'Uang Transport', 'value' => '45000',  'type' => 'number', 'group' => 'gaji'],
            ['key' => 'persen_bpjs_kes',       'label' => 'BPJS Kes',       'value' => '9.24',    'type' => 'percent', 'group' => 'gaji'],
            ['key' => 'persen_bpjs_tk',        'label' => 'BPJS TK',        'value' => '5.00',    'type' => 'percent', 'group' => 'gaji'],
            ['key' => 'kuota_cuti_tahunan',    'label' => 'Kuota Cuti',     'value' => '12',      'type' => 'number', 'group' => 'cuti'],
            ['key' => 'batas_tanggal_gaji',    'label' => 'Batas Tgl Gaji', 'value' => '25',      'type' => 'number', 'group' => 'gaji'],
        ];
        foreach ($configs as $c) {
            Configuration::firstOrCreate(['key' => $c['key']], $c);
        }
    }

    private function buatPimpinan(): User
    {
        $role = Role::where('slug', 'pimpinan')->first();
        return User::create([
            'role_id'        => $role->id,
            'nip'            => 'PM-CBN-0001',
            'password'       => Hash::make('pimpinan123'),
            'nama'           => 'Pimpinan Test',
            'email'          => 'pimpinan@cbn.test',
            'divisi'         => 'manajemen',
            'jabatan'        => 'Direktur',
            'tanggal_masuk'  => now(),
            'is_active'      => true,
        ]);
    }

    private function buatAdmin(): User
    {
        $role = Role::where('slug', 'admin')->first();
        return User::create([
            'role_id'        => $role->id,
            'nip'            => 'ADM-CBN-0001',
            'password'       => Hash::make('admin123'),
            'nama'           => 'Admin Test',
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
            'tanggal_masuk'     => now()->subYear(),
            'is_active'         => true,
        ]);
    }

    /**
     * Buat periode dengan slip gaji berstatus 'proses' (menunggu persetujuan).
     */
    private function buatPeriodeDenganSlip(User $karyawan): PeriodeGaji
    {
        $periode = PeriodeGaji::create([
            'nama_periode'    => 'Juni 2026',
            'tanggal_mulai'   => '2026-05-26',
            'tanggal_selesai' => '2026-06-25',
            'status'          => 'proses',
        ]);

        SlipGajiPeriode::create([
            'user_id'        => $karyawan->id,
            'periode_id'     => $periode->id,
            'total_hadir'    => 20,
            'total_telat'    => 1,
            'total_alfa'     => 2,
            'total_izin'     => 0,
            'total_cuti'     => 0,
            'total_potongan' => 427200,
            'gaji_bersih'    => 4972800,
            'status'         => 'draft',
        ]);

        return $periode;
    }

    // ─────────────────────────────────────────────────────────────────
    // TEST CASES
    // ─────────────────────────────────────────────────────────────────

    public function test_guest_tidak_bisa_mengakses_monitoring_gaji(): void
    {
        $response = $this->get(route('pimpinan.monitoring-gaji.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_pimpinan_tidak_bisa_mengakses_monitoring_gaji(): void
    {
        $this->seedBaseData();
        $karyawan = $this->buatKaryawan();

        $response = $this->actingAs($karyawan)->get(route('pimpinan.monitoring-gaji.index'));

        $response->assertStatus(403);
    }

    public function test_pimpinan_dapat_melihat_monitoring_gaji(): void
    {
        $this->seedBaseData();
        $pimpinan = $this->buatPimpinan();
        $karyawan = $this->buatKaryawan();

        $this->buatPeriodeDenganSlip($karyawan);

        $response = $this->actingAs($pimpinan)->get(route('pimpinan.monitoring-gaji.index'));

        $response->assertStatus(200);
        $response->assertViewHas('slipTetap');
        $response->assertViewHas('slipKontrak');
        $response->assertViewHas('semuaPeriode');
        $response->assertViewHas('totalPengeluaran');
    }

    public function test_pimpinan_menyetujui_seluruh_penggajian(): void
    {
        $this->seedBaseData();
        $pimpinan = $this->buatPimpinan();
        $admin    = $this->buatAdmin();
        $karyawan = $this->buatKaryawan();

        $periode = $this->buatPeriodeDenganSlip($karyawan);

        $response = $this->actingAs($pimpinan)->post(
            route('pimpinan.monitoring-gaji.approve', $periode->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Periode menjadi final
        $periode->refresh();
        $this->assertEquals('final', $periode->status);
        $this->assertNotNull($periode->finalisasi_at);
        $this->assertEquals($pimpinan->id, $periode->finalisasi_by);

        // Slip gaji berubah status menjadi 'diterbitkan'
        $slip = SlipGajiPeriode::where('periode_id', $periode->id)->first();
        $this->assertEquals('diterbitkan', $slip->status);
        $this->assertNotNull($slip->diterbitkan_at);

        // Notifikasi ke karyawan terkirim
        $this->assertDatabaseHas('notifications', [
            'user_id' => $karyawan->id,
            'type'    => 'success',
        ]);

        // Notifikasi ke admin terkirim
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'type'    => 'success',
        ]);
    }

    public function test_pimpinan_menolak_seluruh_penggajian(): void
    {
        $this->seedBaseData();
        $pimpinan = $this->buatPimpinan();
        $admin    = $this->buatAdmin();
        $karyawan = $this->buatKaryawan();

        $periode = $this->buatPeriodeDenganSlip($karyawan);

        $response = $this->actingAs($pimpinan)->post(
            route('pimpinan.monitoring-gaji.reject', $periode->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Periode kembali ke draft
        $periode->refresh();
        $this->assertEquals('draft', $periode->status);
        $this->assertNull($periode->finalisasi_at);
        $this->assertNull($periode->finalisasi_by);

        // Slip gaji dihapus
        $this->assertDatabaseMissing('slip_gaji_periode', [
            'periode_id' => $periode->id,
        ]);

        // Notifikasi ke admin terkirim
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'type'    => 'danger',
        ]);
    }

    public function test_pimpinan_submit_keputusan_per_slip(): void
    {
        $this->seedBaseData();
        $pimpinan = $this->buatPimpinan();
        $admin    = $this->buatAdmin();
        $karyawan = $this->buatKaryawan();

        $periode = $this->buatPeriodeDenganSlip($karyawan);
        $slip = SlipGajiPeriode::where('periode_id', $periode->id)->first();

        // Tolak slip individu
        $response = $this->actingAs($pimpinan)->post(
            route('pimpinan.monitoring-gaji.submit', $periode->id),
            [
                'slips' => [
                    $slip->id => [
                        'status' => 'tolak',
                        'alasan' => 'Data absensi perlu dicek ulang',
                    ],
                ],
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Slip ditolak
        $slip->refresh();
        $this->assertEquals('ditolak', $slip->status);
        $this->assertEquals('Data absensi perlu dicek ulang', $slip->alasan_tolak);

        // Periode kembali ke draft karena ada slip ditolak
        $periode->refresh();
        $this->assertEquals('draft', $periode->status);
    }

    public function test_pimpinan_submit_semua_disetujui(): void
    {
        $this->seedBaseData();
        $pimpinan = $this->buatPimpinan();
        $admin    = $this->buatAdmin();
        $karyawan = $this->buatKaryawan();

        $periode = $this->buatPeriodeDenganSlip($karyawan);
        $slip = SlipGajiPeriode::where('periode_id', $periode->id)->first();

        // Setujui semua slip
        $response = $this->actingAs($pimpinan)->post(
            route('pimpinan.monitoring-gaji.submit', $periode->id),
            [
                'slips' => [
                    $slip->id => [
                        'status' => 'setuju',
                        'alasan' => null,
                    ],
                ],
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Periode menjadi final
        $periode->refresh();
        $this->assertEquals('final', $periode->status);

        // Slip diterbitkan
        $slip->refresh();
        $this->assertEquals('diterbitkan', $slip->status);
    }

    public function test_tidak_bisa_approve_periode_non_proses(): void
    {
        $this->seedBaseData();
        $pimpinan = $this->buatPimpinan();

        $periode = PeriodeGaji::create([
            'nama_periode'    => 'Test',
            'tanggal_mulai'   => now()->subMonth()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'status'          => 'draft',
        ]);

        $response = $this->actingAs($pimpinan)->post(
            route('pimpinan.monitoring-gaji.approve', $periode->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
