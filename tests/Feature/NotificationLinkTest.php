<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use App\Models\Role;
use App\Models\Notification;

class NotificationLinkTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $karyawanUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'nama_role' => 'Admin',
            'slug'      => 'admin',
        ]);

        $karyawanRole = Role::create([
            'nama_role' => 'Karyawan Tetap',
            'slug'      => 'karyawan_tetap',
        ]);

        $this->adminUser = User::create([
            'role_id'       => $adminRole->id,
            'nip'           => 'ADM-001',
            'nama'          => 'Admin Test',
            'email'         => 'admin@test.com',
            'password'      => bcrypt('password'),
            'tanggal_masuk' => now(),
            'is_active'     => true,
        ]);

        $this->karyawanUser = User::create([
            'role_id'       => $karyawanRole->id,
            'nip'           => 'KRY-001',
            'nama'          => 'Karyawan Test',
            'email'         => 'karyawan@test.com',
            'password'      => bcrypt('password'),
            'tanggal_masuk' => now(),
            'is_active'     => true,
        ]);
    }

    #[Test]
    public function notification_persistence_stores_relative_url_from_named_route()
    {
        $namedRouteUrl = route('karyawan.slip-gaji.index');
        $this->assertStringContainsString('http', $namedRouteUrl);

        $notif = Notification::send(
            $this->karyawanUser->id,
            'Slip Gaji Terbit',
            'Detail slip gaji',
            'info',
            route('karyawan.slip-gaji.index', [], false)
        );

        $this->assertEquals('/karyawan/slip-gaji', $notif->link);
        $this->assertStringNotContainsString('127.0.0.1', $notif->link);
        $this->assertStringNotContainsString('localhost', $notif->link);
    }

    #[Test]
    public function notification_model_normalizes_absolute_localhost_urls_automatically_on_save()
    {
        $notif = Notification::create([
            'user_id' => $this->adminUser->id,
            'title'   => 'Test Auto Normalization',
            'message' => 'Message',
            'type'    => 'info',
            'link'    => 'http://127.0.0.1:8000/admin/penggajian/PRD-00003',
        ]);

        $this->assertEquals('/admin/penggajian/PRD-00003', $notif->link);
    }

    #[Test]
    public function existing_relative_url_remains_unchanged()
    {
        $normalized = Notification::normalizeInternalLink('/karyawan/slip-gaji');
        $this->assertEquals('/karyawan/slip-gaji', $normalized);
    }

    #[Test]
    public function legacy_localhost_normalization_converts_to_relative()
    {
        $input = 'http://127.0.0.1:8000/admin/penggajian/PRD-00003';
        $normalized = Notification::normalizeInternalLink($input);
        $this->assertEquals('/admin/penggajian/PRD-00003', $normalized);

        $inputPortless = 'http://localhost/admin/penggajian/PRD-00003';
        $normalizedPortless = Notification::normalizeInternalLink($inputPortless);
        $this->assertEquals('/admin/penggajian/PRD-00003', $normalizedPortless);
    }

    #[Test]
    public function query_string_and_fragment_are_preserved_during_normalization()
    {
        $input = 'http://127.0.0.1:8000/pimpinan/monitoring-gaji?periode_id=PRD-00003#section-1';
        $normalized = Notification::normalizeInternalLink($input);
        $this->assertEquals('/pimpinan/monitoring-gaji?periode_id=PRD-00003#section-1', $normalized);
    }

    #[Test]
    public function notification_read_redirect_redirects_to_internal_path_and_never_exposes_localhost_or_127_in_target()
    {
        config(['app.url' => 'https://siaga-cbn.unand.online']);

        $notif = Notification::send(
            $this->karyawanUser->id,
            'Slip Gaji Terbit',
            'Message',
            'info',
            'http://127.0.0.1:8000/karyawan/slip-gaji'
        );

        $response = $this->actingAs($this->karyawanUser)
            ->get(route('karyawan.notifications.read', $notif->id, false));

        $response->assertRedirect('/karyawan/slip-gaji');

        $locationHeader = $response->headers->get('Location');
        $this->assertEquals('/karyawan/slip-gaji', parse_url($locationHeader, PHP_URL_PATH));
        $this->assertEquals('/karyawan/slip-gaji', $notif->fresh()->link);
        $this->assertStringNotContainsString('127.0.0.1', $notif->fresh()->link);
        $this->assertStringNotContainsString('localhost', $notif->fresh()->link);
    }

    #[Test]
    public function malicious_external_link_does_not_become_an_unrestricted_external_redirect()
    {
        $notif = Notification::create([
            'user_id' => $this->karyawanUser->id,
            'title'   => 'Malicious Link',
            'message' => 'Phishing',
            'type'    => 'danger',
            'link'    => 'https://evil-phishing-site.com/login',
        ]);

        $response = $this->actingAs($this->karyawanUser)
            ->get(route('karyawan.notifications.read', $notif->id, false));

        $response->assertStatus(302);
        $locationHeader = $response->headers->get('Location');
        $this->assertStringNotContainsString('evil-phishing-site.com', $locationHeader);
    }
}
