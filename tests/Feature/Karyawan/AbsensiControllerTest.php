<?php

namespace Tests\Feature\Karyawan;

use App\Models\Role;
use App\Models\User;
use App\Models\Mitra;
use App\Models\Absensi;
use App\Models\DetailRiwayatPenempatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Tests\TestCase;

class AbsensiControllerTest extends TestCase
{
    use RefreshDatabase;

    private function seedBaseData(): void
    {
        Role::create(['nama_role' => 'Administrator',     'slug' => 'admin']);
        Role::create(['nama_role' => 'Pimpinan',          'slug' => 'pimpinan']);
        Role::create(['nama_role' => 'Karyawan Tetap',    'slug' => 'karyawan_tetap']);
        Role::create(['nama_role' => 'Karyawan Kontrak',  'slug' => 'karyawan_kontrak']);
    }

    /**
     * Buat mitra (kantor) pusat untuk karyawan tetap.
     */
    private function buatMitraPusat(): Mitra
    {
        return Mitra::create([
            'nama_mitra'   => 'PT CBN Pusat',
            'latitude'     => -0.9471,
            'longitude'    => 100.4172,
            'radius_meter' => 200,
            'ip_public'    => '127.0.0.1',
            'jam_masuk'    => '08:00:00',
            'jam_pulang'   => '17:00:00',
            'is_pusat'     => true,
        ]);
    }

    /**
     * Buat karyawan tetap beserta penempatan di mitra pusat.
     */
    private function buatKaryawanTetap(Mitra $mitra): User
    {
        $role = Role::where('slug', 'karyawan_tetap')->first();

        $karyawan = User::create([
            'role_id'           => $role->id,
            'nip'               => 'KT-CBN-0001',
            'password'          => Hash::make('karyawan123'),
            'nama'              => 'Karyawan Tetap Test',
            'email'             => 'karyawan@cbn.test',
            'divisi'            => 'keuangan',
            'jabatan'           => 'Staff Keuangan',
            'tanggal_masuk'     => now()->subYear(),
            'is_active'         => true,
            'is_shift'          => false,
        ]);

        return $karyawan;
    }

    // ─────────────────────────────────────────────────────────────────
    // TEST CASES
    // ─────────────────────────────────────────────────────────────────

    public function test_guest_tidak_bisa_mengakses_halaman_absensi(): void
    {
        $response = $this->get(route('karyawan.absensi.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_karyawan_dapat_melihat_halaman_absensi(): void
    {
        $this->seedBaseData();
        $mitra    = $this->buatMitraPusat();
        $karyawan = $this->buatKaryawanTetap($mitra);

        $response = $this->actingAs($karyawan)->get(route('karyawan.absensi.index'));

        $response->assertStatus(200);
        $response->assertViewIs('karyawan.absensi.index');
        $response->assertViewHas('karyawan');
        $response->assertViewHas('penempatan');
    }

    public function test_absen_masuk_sukses_dalam_radius_dan_ip_valid(): void
    {
        $this->seedBaseData();
        $mitra    = $this->buatMitraPusat();
        $karyawan = $this->buatKaryawanTetap($mitra);

        // Waktu kerja: 08:00 - 17:00, absen di jam 08:00 pada hari kerja (Selasa 18 Aug 2026)
        Carbon::setTestNow(Carbon::parse('2026-08-18 08:00:00'));

        $response = $this->actingAs($karyawan)->post(
            route('karyawan.absensi.masuk'),
            [
                'latitude'  => -0.9471,  // Koordinat sama = jarak 0m
                'longitude' => 100.4172,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('absensi', [
            'user_id' => $karyawan->id,
            'status'  => 'hadir',
        ]);

        Carbon::setTestNow(); // Reset waktu
    }

    public function test_absen_masuk_terdeteksi_telat(): void
    {
        $this->seedBaseData();
        $mitra    = $this->buatMitraPusat();
        $karyawan = $this->buatKaryawanTetap($mitra);

        // Telat: absen jam 09:00, batas toleransi 08:00 + 15 menit = 08:15
        Carbon::setTestNow(Carbon::today()->setTime(9, 0));

        $response = $this->actingAs($karyawan)->post(
            route('karyawan.absensi.masuk'),
            [
                'latitude'  => -0.9471,
                'longitude' => 100.4172,
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('absensi', [
            'user_id' => $karyawan->id,
            'status'  => 'telat',
            'is_telat' => true,
        ]);

        Carbon::setTestNow();
    }

    public function test_absen_masuk_gagal_jika_di_luar_radius_gps(): void
    {
        $this->seedBaseData();
        $mitra    = $this->buatMitraPusat();
        $karyawan = $this->buatKaryawanTetap($mitra);

        Carbon::setTestNow(Carbon::today()->setTime(8, 0));

        // Koordinat jauh (Jakarta) = jarak > 200m
        $response = $this->actingAs($karyawan)->post(
            route('karyawan.absensi.masuk'),
            [
                'latitude'  => -6.2088,
                'longitude' => 106.8456,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('absensi', [
            'user_id' => $karyawan->id,
        ]);

        Carbon::setTestNow();
    }

    public function test_absen_masuk_gagal_jika_sudah_absen_hari_ini(): void
    {
        $this->seedBaseData();
        $mitra    = $this->buatMitraPusat();
        $karyawan = $this->buatKaryawanTetap($mitra);

        Carbon::setTestNow(Carbon::today()->setTime(8, 0));

        // Absen pertama kali
        Absensi::create([
            'user_id'     => $karyawan->id,
            'mitra_id'    => $mitra->id,
            'tanggal'     => Carbon::today(),
            'waktu_masuk' => Carbon::now(),
            'status'      => 'hadir',
            'is_telat'    => false,
        ]);

        // Coba absen kedua kali
        $response = $this->actingAs($karyawan)->post(
            route('karyawan.absensi.masuk'),
            [
                'latitude'  => -0.9471,
                'longitude' => 100.4172,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');

        Carbon::setTestNow();
    }

    public function test_absen_pulang_sukses(): void
    {
        $this->seedBaseData();
        $mitra    = $this->buatMitraPusat();
        $karyawan = $this->buatKaryawanTetap($mitra);

        // Buat absensi masuk jam 08:00
        $workday = Carbon::parse('2026-08-18');
        $absensi = Absensi::create([
            'user_id'     => $karyawan->id,
            'mitra_id'    => $mitra->id,
            'tanggal'     => $workday,
            'waktu_masuk' => $workday->copy()->setTime(8, 0),
            'status'      => 'hadir',
            'is_telat'    => false,
        ]);

        // Set waktu = 17:00 (jam pulang)
        Carbon::setTestNow($workday->copy()->setTime(17, 0));

        $response = $this->actingAs($karyawan)->post(
            route('karyawan.absensi.pulang'),
            [
                'latitude'  => -0.9471,
                'longitude' => 100.4172,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $absensi->refresh();
        $this->assertNotNull($absensi->waktu_pulang);

        Carbon::setTestNow();
    }

    public function test_absen_pulang_gagal_belum_waktunya(): void
    {
        $this->seedBaseData();
        $mitra    = $this->buatMitraPusat();
        $karyawan = $this->buatKaryawanTetap($mitra);

        // Buat absensi masuk jam 08:00
        Absensi::create([
            'user_id'     => $karyawan->id,
            'mitra_id'    => $mitra->id,
            'tanggal'     => Carbon::today(),
            'waktu_masuk' => Carbon::today()->setTime(8, 0),
            'status'      => 'hadir',
            'is_telat'    => false,
        ]);

        // Set waktu = 10:00 (jauh sebelum jam pulang 17:00 - 15 menit)
        Carbon::setTestNow(Carbon::today()->setTime(10, 0));

        $response = $this->actingAs($karyawan)->post(
            route('karyawan.absensi.pulang'),
            [
                'latitude'  => -0.9471,
                'longitude' => 100.4172,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');

        Carbon::setTestNow();
    }

    public function test_karyawan_dapat_melihat_riwayat_absensi(): void
    {
        $this->seedBaseData();
        $mitra    = $this->buatMitraPusat();
        $karyawan = $this->buatKaryawanTetap($mitra);

        $response = $this->actingAs($karyawan)->get(route('karyawan.absensi.riwayat'));

        $response->assertStatus(200);
        $response->assertViewIs('karyawan.absensi.riwayat');
        $response->assertViewHas('riwayat');
        $response->assertViewHas('rekapBulan');
    }

    public function test_absen_masuk_bisa_dilakukan_setelah_jam_12_siang(): void
    {
        $this->seedBaseData();
        $mitra    = $this->buatMitraPusat();
        $karyawan = $this->buatKaryawanTetap($mitra);

        // Simulasi absen di siang hari jam 14:00 (lewat jam 12:00) pada hari kerja
        Carbon::setTestNow(Carbon::parse('2026-08-18 14:00:00'));

        $response = $this->actingAs($karyawan)->post(
            route('karyawan.absensi.masuk'),
            [
                'latitude'  => -0.9471,
                'longitude' => 100.4172,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('warning'); // Terlambat

        $this->assertDatabaseHas('absensi', [
            'user_id'  => $karyawan->id,
            'status'   => 'telat',
            'is_telat' => true,
        ]);

        Carbon::setTestNow();
    }

    public function test_absen_masuk_mengabaikan_dan_mengubah_auto_alfa_hari_ini(): void
    {
        $this->seedBaseData();
        $mitra    = $this->buatMitraPusat();
        $karyawan = $this->buatKaryawanTetap($mitra);

        // Buat record Alfa pada hari ini (misal dari sistem sebelumnya)
        $absensiAlfa = Absensi::create([
            'user_id'  => $karyawan->id,
            'mitra_id' => $mitra->id,
            'tanggal'  => Carbon::today(),
            'status'   => 'alfa',
            'is_telat' => false,
        ]);

        // Simulasi absen masuk jam 13:00
        Carbon::setTestNow(Carbon::today()->setTime(13, 0));

        $response = $this->actingAs($karyawan)->post(
            route('karyawan.absensi.masuk'),
            [
                'latitude'  => -0.9471,
                'longitude' => 100.4172,
            ]
        );

        $response->assertRedirect();

        $absensiAlfa->refresh();
        $this->assertEquals('telat', $absensiAlfa->status);
        $this->assertNotNull($absensiAlfa->waktu_masuk);

        Carbon::setTestNow();
    }
}
