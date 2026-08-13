<?php

namespace Tests\Feature\Pimpinan;

use App\Models\Role;
use App\Models\User;
use App\Models\DetailPerizinan;
use App\Models\JenisPerizinan;
use App\Models\KuotaPerizinan;
use App\Models\Lembur;
use App\Models\Mitra;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApprovalControllerTest extends TestCase
{
    use RefreshDatabase;

    private function seedBaseData(): void
    {
        Role::create(['nama_role' => 'Administrator',     'slug' => 'admin']);
        Role::create(['nama_role' => 'Pimpinan',          'slug' => 'pimpinan']);
        Role::create(['nama_role' => 'Karyawan Tetap',    'slug' => 'karyawan_tetap']);
        Role::create(['nama_role' => 'Karyawan Kontrak',  'slug' => 'karyawan_kontrak']);

        // Mitra pusat (untuk efek perizinan membuat record absensi)
        Mitra::create([
            'nama_mitra'   => 'PT CBN Pusat',
            'latitude'     => -0.9471,
            'longitude'    => 100.4172,
            'radius_meter' => 200,
            'is_pusat'     => true,
        ]);
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
     * Buat pengajuan perizinan (cuti) yang menunggu approval.
     */
    private function buatPerizinanMenunggu(User $karyawan): DetailPerizinan
    {
        // Ambil jenis perizinan cuti (sudah di-seed oleh migration)
        $jenisCuti = JenisPerizinan::firstOrCreate(
            ['slug' => 'cuti'],
            [
                'nama_jenis'          => 'Cuti Tahunan',
                'memotong_kuota'      => true,
                'memotong_uang_makan' => true,
                'wajib_upload_bukti'  => false,
            ]
        );

        // Buat kuota perizinan karyawan
        $kuota = KuotaPerizinan::create([
            'user_id'     => $karyawan->id,
            'tahun'       => now()->year,
            'kuota_total' => 12,
            'terpakai'    => 0,
            'sisa'        => 12,
        ]);

        return DetailPerizinan::create([
            'user_id'            => $karyawan->id,
            'kuota_perizinan_id' => $kuota->id,
            'jenis_perizinan_id' => $jenisCuti->id,
            'tanggal_mulai'      => now()->addDays(3),
            'tanggal_selesai'    => now()->addDays(5),
            'jumlah_hari'        => 2,
            'keterangan'         => 'Cuti tahunan untuk liburan',
            'status_approval'    => 'menunggu',
        ]);
    }

    /**
     * Buat pengajuan lembur yang menunggu approval.
     */
    private function buatLemburMenunggu(User $karyawan): Lembur
    {
        return Lembur::create([
            'user_id'         => $karyawan->id,
            'tanggal'         => now()->addDay(),
            'jam_mulai'       => '18:00',
            'jam_selesai'     => '21:00',
            'total_jam'       => 3.0,
            'keterangan'      => 'Lembur project deadline',
            'status_approval' => 'menunggu',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // TEST CASES
    // ─────────────────────────────────────────────────────────────────

    public function test_guest_tidak_bisa_mengakses_approval(): void
    {
        $response = $this->get(route('pimpinan.approval.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_pimpinan_tidak_bisa_mengakses_approval(): void
    {
        $this->seedBaseData();
        $karyawan = $this->buatKaryawan();

        $response = $this->actingAs($karyawan)->get(route('pimpinan.approval.index'));

        $response->assertStatus(403);
    }

    public function test_pimpinan_dapat_melihat_daftar_pengajuan(): void
    {
        $this->seedBaseData();
        $pimpinan = $this->buatPimpinan();
        $karyawan = $this->buatKaryawan();

        $this->buatPerizinanMenunggu($karyawan);
        $this->buatLemburMenunggu($karyawan);

        $response = $this->actingAs($pimpinan)->get(route('pimpinan.approval.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pimpinan.approval.index');
        $response->assertViewHas('perizinan');
        $response->assertViewHas('lembur');
        $response->assertViewHas('jumlahMenunggu');
    }

    public function test_pimpinan_menyetujui_perizinan(): void
    {
        Mail::fake();
        $this->seedBaseData();
        $pimpinan = $this->buatPimpinan();
        $karyawan = $this->buatKaryawan();
        $perizinan = $this->buatPerizinanMenunggu($karyawan);

        $response = $this->actingAs($pimpinan)->patch(
            route('pimpinan.approval.perizinan.setuju', $perizinan->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $perizinan->refresh();
        $this->assertEquals('disetujui', $perizinan->status_approval);
        $this->assertEquals($pimpinan->id, $perizinan->approved_by);
        $this->assertNotNull($perizinan->approved_at);

        // Cek bahwa absensi otomatis dibuat untuk hari cuti
        $this->assertDatabaseHas('absensi', [
            'user_id' => $karyawan->id,
            'status'  => 'cuti',
        ]);

        // Cek bahwa kuota cuti terpotong
        $kuota = KuotaPerizinan::where('user_id', $karyawan->id)->first();
        $this->assertEquals(2, $kuota->terpakai);
        $this->assertEquals(10, $kuota->sisa);

        // Cek notifikasi terkirim
        $this->assertDatabaseHas('notifications', [
            'user_id' => $karyawan->id,
            'type'    => 'success',
        ]);
    }

    public function test_pimpinan_menolak_perizinan(): void
    {
        Mail::fake();
        $this->seedBaseData();
        $pimpinan = $this->buatPimpinan();
        $karyawan = $this->buatKaryawan();
        $perizinan = $this->buatPerizinanMenunggu($karyawan);

        $response = $this->actingAs($pimpinan)->patch(
            route('pimpinan.approval.perizinan.tolak', $perizinan->id),
            ['alasan_tolak' => 'Tidak sesuai jadwal perusahaan']
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $perizinan->refresh();
        $this->assertEquals('ditolak', $perizinan->status_approval);
        $this->assertEquals('Tidak sesuai jadwal perusahaan', $perizinan->alasan_tolak);
        $this->assertEquals($pimpinan->id, $perizinan->approved_by);

        // Kuota cuti TIDAK terpotong
        $kuota = KuotaPerizinan::where('user_id', $karyawan->id)->first();
        $this->assertEquals(0, $kuota->terpakai);
        $this->assertEquals(12, $kuota->sisa);
    }

    public function test_pimpinan_menolak_perizinan_wajib_alasan(): void
    {
        Mail::fake();
        $this->seedBaseData();
        $pimpinan = $this->buatPimpinan();
        $karyawan = $this->buatKaryawan();
        $perizinan = $this->buatPerizinanMenunggu($karyawan);

        $response = $this->actingAs($pimpinan)->patch(
            route('pimpinan.approval.perizinan.tolak', $perizinan->id),
            ['alasan_tolak' => '']
        );

        $response->assertSessionHasErrors('alasan_tolak');
    }

    public function test_pimpinan_menyetujui_lembur(): void
    {
        Mail::fake();
        $this->seedBaseData();
        $pimpinan = $this->buatPimpinan();
        $karyawan = $this->buatKaryawan();
        $lembur   = $this->buatLemburMenunggu($karyawan);

        $response = $this->actingAs($pimpinan)->patch(
            route('pimpinan.approval.lembur.setuju', $lembur->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $lembur->refresh();
        $this->assertEquals('disetujui', $lembur->status_approval);
        $this->assertEquals($pimpinan->id, $lembur->approved_by);
        $this->assertNotNull($lembur->approved_at);

        // Cek notifikasi terkirim
        $this->assertDatabaseHas('notifications', [
            'user_id' => $karyawan->id,
            'type'    => 'success',
        ]);
    }

    public function test_pimpinan_menolak_lembur(): void
    {
        Mail::fake();
        $this->seedBaseData();
        $pimpinan = $this->buatPimpinan();
        $karyawan = $this->buatKaryawan();
        $lembur   = $this->buatLemburMenunggu($karyawan);

        $response = $this->actingAs($pimpinan)->patch(
            route('pimpinan.approval.lembur.tolak', $lembur->id),
            ['alasan_tolak' => 'Overtime tidak diperlukan saat ini']
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $lembur->refresh();
        $this->assertEquals('ditolak', $lembur->status_approval);
        $this->assertEquals('Overtime tidak diperlukan saat ini', $lembur->alasan_tolak);
    }

    public function test_pengajuan_yang_sudah_diproses_tidak_bisa_diubah(): void
    {
        Mail::fake();
        $this->seedBaseData();
        $pimpinan = $this->buatPimpinan();
        $karyawan = $this->buatKaryawan();
        $perizinan = $this->buatPerizinanMenunggu($karyawan);

        // Setujui dulu
        $perizinan->update([
            'status_approval' => 'disetujui',
            'approved_by'     => $pimpinan->id,
            'approved_at'     => now(),
        ]);

        // Coba setujui lagi
        $response = $this->actingAs($pimpinan)->patch(
            route('pimpinan.approval.perizinan.setuju', $perizinan->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
