<?php

namespace Tests\Feature;

use App\Models\AlokasiLoker;
use App\Models\Karyawan;
use App\Models\Loker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminMasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Administrator SNA',
            'username' => 'admin',
            'email' => 'admin@snamedika.com',
            'password' => Hash::make('adminsna'),
        ]);
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);
        $response->assertSee('Login Administrator');
    }

    public function test_admin_can_login_with_username_and_password(): void
    {
        $response = $this->post(route('admin.login.submit'), [
            'username' => 'admin',
            'password' => 'adminsna',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_admin_cannot_login_with_wrong_password(): void
    {
        $response = $this->from(route('admin.login'))->post(route('admin.login.submit'), [
            'username' => 'admin',
            'password' => 'wrongpass',
        ]);

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_admin_dashboard_accessible_when_authenticated(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Dashboard Overview');
    }

    public function test_karyawan_crud_operations(): void
    {
        // 1. Create
        $response = $this->actingAs($this->admin)->post(route('admin.karyawan.store'), [
            'nik' => '99887766',
            'nama' => 'Karyawan Baru Test',
            'divisi' => 'Laboratorium',
            'jabatan' => 'Analis',
            'gender' => 'L',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.karyawan.index'));
        $this->assertDatabaseHas('karyawan', [
            'nik' => '99887766',
            'nama' => 'Karyawan Baru Test',
        ]);

        $karyawan = Karyawan::where('nik', '99887766')->first();

        // 2. Update
        $response = $this->actingAs($this->admin)->put(route('admin.karyawan.update', $karyawan), [
            'nik' => '99887766',
            'nama' => 'Karyawan Diubah',
            'divisi' => 'Farmasi',
            'jabatan' => 'Kepala',
            'gender' => 'P',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.karyawan.index'));
        $this->assertDatabaseHas('karyawan', [
            'nik' => '99887766',
            'nama' => 'Karyawan Diubah',
            'divisi' => 'Farmasi',
            'gender' => 'P',
        ]);

        // 3. Delete
        $response = $this->actingAs($this->admin)->delete(route('admin.karyawan.destroy', $karyawan));
        $response->assertRedirect(route('admin.karyawan.index'));
        $this->assertDatabaseMissing('karyawan', [
            'nik' => '99887766',
        ]);
    }

    public function test_loker_crud_operations(): void
    {
        // 1. Create
        $response = $this->actingAs($this->admin)->post(route('admin.loker.store'), [
            'kode_loker' => 'L-101',
            'nomor_urut' => 101,
            'gender_group' => 'L',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.loker.index'));
        $this->assertDatabaseHas('lokers', [
            'kode_loker' => 'L-101',
            'nomor_urut' => 101,
        ]);

        $loker = Loker::where('kode_loker', 'L-101')->first();

        // 2. Update
        $response = $this->actingAs($this->admin)->put(route('admin.loker.update', $loker), [
            'kode_loker' => 'L-101-EDIT',
            'nomor_urut' => 101,
            'gender_group' => 'L',
            'is_active' => false,
        ]);

        $response->assertRedirect(route('admin.loker.index'));
        $this->assertDatabaseHas('lokers', [
            'kode_loker' => 'L-101-EDIT',
            'is_active' => 0,
        ]);

        // 3. Toggle Active
        $this->actingAs($this->admin)->post(route('admin.loker.toggle-active', $loker));
        $this->assertEquals(1, $loker->fresh()->is_active);

        // 4. Delete
        $response = $this->actingAs($this->admin)->delete(route('admin.loker.destroy', $loker));
        $response->assertRedirect(route('admin.loker.index'));
        $this->assertDatabaseMissing('lokers', [
            'kode_loker' => 'L-101-EDIT',
        ]);
    }

    public function test_force_release_active_allocation(): void
    {
        $karyawan = Karyawan::create([
            'nik' => '112233',
            'nama' => 'Test User',
            'gender' => 'L',
            'is_active' => true,
        ]);

        $loker = Loker::create([
            'kode_loker' => 'L-1',
            'nomor_urut' => 1,
            'gender_group' => 'L',
            'is_active' => true,
        ]);

        $alokasi = AlokasiLoker::create([
            'karyawan_id' => $karyawan->id,
            'loker_id' => $loker->id,
            'waktu_masuk' => now(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.alokasi.force-release', $alokasi));
        $response->assertRedirect();

        $this->assertEquals('completed', $alokasi->fresh()->status);
        $this->assertNotNull($alokasi->fresh()->waktu_keluar);
    }
}
