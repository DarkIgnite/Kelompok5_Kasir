<?php

namespace Tests\Feature;

use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Kelola pengguna sesuai tbl_pengguna (role: admin / kasir) dan DFD 4.x.
 */
class PenggunaRoleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $admin2;
    private User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->admin2 = User::factory()->admin()->create();
        $this->kasir = User::factory()->create(['role' => 'kasir']);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'nama_lengkap' => 'Nama Baru',
            'role' => 'kasir',
            'status' => 'aktif',
            'no_telepon' => '0811',
        ], $override);
    }

    public function test_jabatan_column_is_gone(): void
    {
        $this->assertFalse(\Schema::hasColumn('tbl_pengguna', 'jabatan'));
    }

    public function test_admin_can_add_kasir_and_admin(): void
    {
        $this->actingAs($this->admin)->post('/pengguna', $this->payload(['nama_lengkap' => 'Kasir Tiga', 'password' => 'kasir3']))
            ->assertSessionHas('success');
        $user = User::where('nama_lengkap', 'Kasir Tiga')->firstOrFail();
        $this->assertSame('kasir', $user->role);
        $this->assertSame('kasirtiga', $user->username);
        $this->assertTrue(Hash::check('kasir3', $user->password)); // di-hash sekali

        $this->actingAs($this->admin)->post('/pengguna', $this->payload(['nama_lengkap' => 'Admin Dua', 'role' => 'admin', 'password' => 'x1234']))
            ->assertSessionHas('success');
        $this->assertSame('admin', User::where('nama_lengkap', 'Admin Dua')->value('role'));
    }

    public function test_role_must_be_admin_or_kasir(): void
    {
        $this->actingAs($this->admin)->post('/pengguna', $this->payload(['role' => 'owner', 'password' => 'x1234']))
            ->assertSessionHasErrors('role');
        $this->actingAs($this->admin)->post('/pengguna', $this->payload(['role' => 'Manager', 'password' => 'x1234']))
            ->assertSessionHasErrors('role');
    }

    public function test_admin_can_edit_and_delete_kasir(): void
    {
        $this->actingAs($this->admin)->put("/pengguna/{$this->kasir->id_pengguna}", $this->payload(['nama_lengkap' => 'Kasir Edit']))
            ->assertSessionHas('success');
        $this->assertSame('Kasir Edit', $this->kasir->fresh()->nama_lengkap);

        $this->actingAs($this->admin)->delete("/pengguna/{$this->kasir->id_pengguna}")->assertSessionHas('success');
        $this->assertModelMissing($this->kasir);
    }

    public function test_admin_cannot_edit_or_delete_other_admin(): void
    {
        $this->actingAs($this->admin)->put("/pengguna/{$this->admin2->id_pengguna}", $this->payload(['role' => 'admin']))
            ->assertForbidden();
        $this->actingAs($this->admin)->delete("/pengguna/{$this->admin2->id_pengguna}")->assertForbidden();
        $this->assertModelExists($this->admin2);
    }

    public function test_admin_can_edit_self_but_not_demote_or_deactivate_self(): void
    {
        $this->actingAs($this->admin)->put("/pengguna/{$this->admin->id_pengguna}", $this->payload(['nama_lengkap' => 'Admin Baru', 'role' => 'admin']))
            ->assertSessionHas('success');
        $this->assertSame('Admin Baru', $this->admin->fresh()->nama_lengkap);

        $this->actingAs($this->admin)->put("/pengguna/{$this->admin->id_pengguna}", $this->payload(['role' => 'kasir']))
            ->assertSessionHas('error');
        $this->actingAs($this->admin)->put("/pengguna/{$this->admin->id_pengguna}", $this->payload(['role' => 'admin', 'status' => 'nonaktif']))
            ->assertSessionHas('error');
        $this->assertSame('admin', $this->admin->fresh()->role);
        $this->assertSame('aktif', $this->admin->fresh()->status);
    }

    public function test_user_cannot_delete_self(): void
    {
        $this->actingAs($this->admin)->delete("/pengguna/{$this->admin->id_pengguna}")->assertSessionHas('error');
        $this->assertModelExists($this->admin);
    }

    public function test_password_only_changes_when_filled(): void
    {
        $oldHash = $this->kasir->password;
        $this->actingAs($this->admin)->put("/pengguna/{$this->kasir->id_pengguna}", $this->payload(['password' => '']));
        $this->assertSame($oldHash, $this->kasir->fresh()->password);

        $this->actingAs($this->admin)->put("/pengguna/{$this->kasir->id_pengguna}", $this->payload(['password' => 'baru123']));
        $this->post('/logout');
        $this->post('/login', ['username' => $this->kasir->username, 'password' => 'baru123'])
            ->assertRedirect(route('kasir.index'));
    }

    public function test_kasir_with_transactions_is_not_deleted(): void
    {
        Transaksi::create([
            'no_nota' => 'TRX-1', 'id_pengguna' => $this->kasir->id_pengguna,
            'tanggal_transaksi' => now(), 'total_item' => 1, 'subtotal' => 1000,
            'total_bayar' => 1000, 'jumlah_bayar' => 1000, 'metode_pembayaran' => 'cash',
        ]);

        $this->actingAs($this->admin)->delete("/pengguna/{$this->kasir->id_pengguna}")->assertSessionHas('error');
        $this->assertModelExists($this->kasir);
    }

    public function test_page_shows_role_options_and_permissions(): void
    {
        $html = $this->actingAs($this->admin)->get('/pengguna')->assertOk()->getContent();

        $this->assertStringContainsString('<option value="admin">Admin</option>', $html);
        $this->assertStringContainsString('<option value="kasir">Kasir</option>', $html);
        $this->assertStringNotContainsString('Owner', $html);
        $this->assertStringNotContainsString('Manager', $html);
        $this->assertStringContainsString("openEditUserModal({$this->kasir->id_pengguna},", $html);
        $this->assertStringNotContainsString("openEditUserModal({$this->admin2->id_pengguna},", $html);
    }
}
