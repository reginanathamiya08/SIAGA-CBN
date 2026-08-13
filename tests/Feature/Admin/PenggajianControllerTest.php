<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\PeriodeGaji;
use App\Models\SlipGajiPeriode;
use App\Models\Absensi;
use App\Models\Configuration;
use App\Models\KomponenGaji;
use App\Models\DetailGajiKomponen;
use App\Models\Mitra;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Tests\TestCase;

class PenggajianControllerTest extends TestCase
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
            ['key' => 'umr_tahun_ini',         'label' => 'UMR',           'value' => '2994031', 'type' => 'number', 'group' => 'gaji'],
            ['key' => 'uang_makan_default',    'label' => 'Uang Makan',    'value' => '35000',   'type' => 'number', 'group' => 'gaji'],
            ['key' => 'uang_transport_default', 'label' => 'Uang Transport', 'value' => '45000', 'type' => 'number', 'group' => 'gaji'],
            ['key' => 'persen_bpjs_kes',       'label' => 'BPJS Kes',      'value' => '9.24',    'type' => 'percent', 'group' => 'gaji'],
            ['key' => 'persen_bpjs_tk',        'label' => 'BPJS TK',       'value' => '5.00',    'type' => 'percent', 'group' => 'gaji'],
            ['key' => 'kuota_cuti_tahunan',    'label' => 'Kuota Cuti',    'value' => '12',      'type' => 'number', 'group' => 'cuti'],
            ['key' => 'batas_tanggal_gaji',    'label' => 'Batas Tgl Gaji', 'value' => '25',     'type' => 'number', 'group' => 'gaji'],
        ];
        foreach ($configs as $c) {
            Configuration::firstOrCreate(['key' => $c['key']], $c);
        }

        // Mitra pusat
        Mitra::create([
            'nama_mitra'   => 'PT CBN Pusat',
            'latitude'     => -0.9471,
            'longitude'    => 100.4172,
            'radius_meter' => 200,
            'is_pusat'     => true,
        ]);
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

    private function buatKaryawanDenganGaji(): User
    {
        $role = Role::where('slug', 'karyawan_tetap')->first();
        $karyawan = User::create([
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

        // Set komponen gaji (gaji pokok 3 juta, uang makan 35rb, transport 45rb, BPJS)
        $karyawan->updateKomponenGaji([
            'gaji_pokok'      => 3000000,
            'uang_makan'      => 35000,
            'uang_transport'  => 45000,
            'persen_bpjs_kes' => 9.24,
            'persen_bpjs_tk'  => 5.00,
        ]);

        return $karyawan;
    }

    /**
     * Buat beberapa record absensi untuk karyawan di periode tertentu.
     */
    private function seedAbsensi(User $karyawan, string $mitraId, Carbon $mulai, Carbon $selesai): void
    {
        $tanggal = $mulai->copy();
        $count = 0;
        while ($tanggal->lte($selesai) && $count < 20) {
            // Skip Sabtu & Minggu
            if ($tanggal->isWeekday()) {
                Absensi::create([
                    'user_id'     => $karyawan->id,
                    'mitra_id'    => $mitraId,
                    'tanggal'     => $tanggal->toDateString(),
                    'waktu_masuk' => $tanggal->copy()->setTime(8, 0),
                    'status'      => 'hadir',
                    'is_telat'    => false,
                ]);
                $count++;
            }
            $tanggal->addDay();
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // TEST CASES
    // ─────────────────────────────────────────────────────────────────

    public function test_guest_tidak_bisa_mengakses_penggajian(): void
    {
        $response = $this->get(route('admin.penggajian.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_dapat_melihat_daftar_periode_gaji(): void
    {
        $this->seedBaseData();
        $admin = $this->buatAdmin();

        $response = $this->actingAs($admin)->get(route('admin.penggajian.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.penggajian.index');
        $response->assertViewHas('periode');
    }

    public function test_admin_dapat_membuat_periode_penggajian_baru(): void
    {
        $this->seedBaseData();
        $admin = $this->buatAdmin();

        // Gunakan bulan lalu agar tidak konflik dengan batas tanggal gaji
        $bulanLalu = now()->subMonth();

        $response = $this->actingAs($admin)->post(route('admin.penggajian.proses'), [
            'bulan' => $bulanLalu->month,
            'tahun' => $bulanLalu->year,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('periode_gaji', [
            'status' => 'draft',
        ]);
    }

    public function test_tidak_bisa_membuat_periode_duplikat(): void
    {
        $this->seedBaseData();
        $admin = $this->buatAdmin();

        $bulanLalu = now()->subMonth();

        // Buat periode pertama
        PeriodeGaji::create([
            'nama_periode'    => $bulanLalu->translatedFormat('F Y'),
            'tanggal_mulai'   => Carbon::create($bulanLalu->year, $bulanLalu->month, 26)->subMonth()->toDateString(),
            'tanggal_selesai' => Carbon::create($bulanLalu->year, $bulanLalu->month, 25)->toDateString(),
            'status'          => 'draft',
        ]);

        // Coba buat lagi
        $response = $this->actingAs($admin)->post(route('admin.penggajian.proses'), [
            'bulan' => $bulanLalu->month,
            'tahun' => $bulanLalu->year,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_admin_dapat_menghitung_gaji_karyawan(): void
    {
        $this->seedBaseData();
        $admin    = $this->buatAdmin();
        $karyawan = $this->buatKaryawanDenganGaji();

        // Buat pimpinan (dibutuhkan untuk notifikasi)
        $rolePimpinan = Role::where('slug', 'pimpinan')->first();
        User::create([
            'role_id'   => $rolePimpinan->id,
            'nip'       => 'PM-CBN-0001',
            'password'  => Hash::make('test123'),
            'nama'      => 'Pimpinan',
            'email'     => 'pimpinan@cbn.test',
            'divisi'    => 'manajemen',
            'jabatan'   => 'Direktur',
            'tanggal_masuk' => now(),
            'is_active' => true,
        ]);

        // Buat periode bulan lalu
        $bulanLalu = now()->subMonth();
        $periode = PeriodeGaji::create([
            'nama_periode'    => $bulanLalu->translatedFormat('F Y'),
            'tanggal_mulai'   => Carbon::create($bulanLalu->year, $bulanLalu->month, 26)->subMonth()->toDateString(),
            'tanggal_selesai' => Carbon::create($bulanLalu->year, $bulanLalu->month, 25)->toDateString(),
            'status'          => 'draft',
        ]);

        // Seed absensi untuk karyawan
        $mitra = Mitra::where('is_pusat', true)->first();
        $this->seedAbsensi($karyawan, $mitra->id, $periode->tanggal_mulai, $periode->tanggal_selesai);

        // Set tanggal = 26 agar melewati batas minimal (default: 25)
        Carbon::setTestNow(Carbon::create($bulanLalu->year, $bulanLalu->month, 26));

        $response = $this->actingAs($admin)->post(
            route('admin.penggajian.hitung', $periode->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Periode berubah ke 'proses'
        $periode->refresh();
        $this->assertEquals('proses', $periode->status);

        // Slip gaji karyawan terbuat
        $this->assertDatabaseHas('slip_gaji_periode', [
            'user_id'    => $karyawan->id,
            'periode_id' => $periode->id,
        ]);

        // Cek slip gaji memiliki nominal gaji bersih > 0
        $slip = SlipGajiPeriode::where('user_id', $karyawan->id)->first();
        $this->assertGreaterThan(0, $slip->gaji_bersih);
        $this->assertGreaterThan(0, $slip->total_hadir);

        // Cek detail komponen gaji tersimpan
        $detailCount = DetailGajiKomponen::where('slip_gaji_periode_id', $slip->id)->count();
        $this->assertGreaterThan(0, $detailCount);

        Carbon::setTestNow();
    }

    public function test_admin_dapat_menghapus_periode_draft(): void
    {
        $this->seedBaseData();
        $admin = $this->buatAdmin();

        $periode = PeriodeGaji::create([
            'nama_periode'    => 'Test Periode',
            'tanggal_mulai'   => now()->subMonth()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'status'          => 'draft',
        ]);

        $response = $this->actingAs($admin)->delete(
            route('admin.penggajian.destroy', $periode->id)
        );

        $response->assertRedirect(route('admin.penggajian.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('periode_gaji', ['id' => $periode->id]);
    }

    public function test_tidak_bisa_menghapus_periode_non_draft(): void
    {
        $this->seedBaseData();
        $admin = $this->buatAdmin();

        $periode = PeriodeGaji::create([
            'nama_periode'    => 'Test Periode',
            'tanggal_mulai'   => now()->subMonth()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'status'          => 'proses',
        ]);

        $response = $this->actingAs($admin)->delete(
            route('admin.penggajian.destroy', $periode->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('periode_gaji', ['id' => $periode->id]);
    }

    public function test_admin_dapat_melihat_detail_periode(): void
    {
        $this->seedBaseData();
        $admin = $this->buatAdmin();

        $periode = PeriodeGaji::create([
            'nama_periode'    => 'Test Periode',
            'tanggal_mulai'   => now()->subMonth()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'status'          => 'draft',
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.penggajian.show', $periode->id)
        );

        $response->assertStatus(200);
        $response->assertViewIs('admin.penggajian.show');
    }
}
