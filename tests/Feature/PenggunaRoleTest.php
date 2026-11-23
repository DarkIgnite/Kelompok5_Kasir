<?php

namespace Tests\Feature;

use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PenggunaRoleTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $manager;
    private User $manager2;
    private User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        // id_pengguna = 1 diperlakukan sebagai Owner oleh model
        $this->owner = User::factory()->create(['role' => 'admin', 'jabatan' => 'Owner']);
        $this->manager = User::factory()->create(['role' => 'admin', 'jabatan' => 'Manager']);
        $this->manager2 = User::factory()->create(['role' => 'admin', 'jabatan' => 'Manager']);
        $this->kasir = User::factory()->create(['role' => 'kasir', 'jabatan' => 'Kasir']);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'nama_user' => 'Nama Baru',
            'jabatan' => 'Kasir',
            'status' => 'aktif',
            'no_telepon' => '0811',
        ], $override);
    }

    public function test_user_cannot_delete_self(): void
    {
        $this->actingAs($this->owner)->delete("/pengguna/{$this->owner->id_pengguna}")
            ->assertSessionHas('error');
        $this->assertModelExists($this->owner);
    }

    public function test_manager_cannot_edit_or_delete_owner(): void
    {
        $this->actingAs($this->manager)
            ->put("/pengguna/{$this->owner->id_pengguna}", $this->payload(['jabatan' => 'Owner']))
            ->assertForbidden();

        $this->actingAs($this->manager)->delete("/pengguna/{$this->owner->id_pengguna}")
            ->assertForbidden();

        $this->assertModelExists($this->owner);
    }

    public function test_manager_cannot_edit_or_delete_other_manager(): void
    {
        $this->actingAs($this->manager)
            ->put("/pengguna/{$this->manager2->id_pengguna}", $this->payload(['jabatan' => 'Manager']))
            ->assertForbidden();
        $this->actingAs($this->manager)->delete("/pengguna/{$this->manager2->id_pengguna}")
            ->assertForbidden();
    }

    public function test_manager_can_edit_and_delete_kasir(): void
    {
        $this->actingAs($this->manager)
            ->put("/pengguna/{$this->kasir->id_pengguna}", $this->payload(['nama_user' => 'Kasir Edit']))
            ->assertSessionHas('success');
        $this->assertSame('Kasir Edit', $this->kasir->fresh()->nama_lengkap);

        $this->actingAs($this->manager)->delete("/pengguna/{$this->kasir->id_pengguna}")
            ->assertSessionHas('success');
        $this->assertModelMissing($this->kasir);
    }

    public function test_manager_cannot_escalate_to_owner(): void
    {
        // menaikkan kasir jadi owner
        $this->actingAs($this->manager)
            ->put("/pengguna/{$this->kasir->id_pengguna}", $this->payload(['jabatan' => 'Owner']))
            ->assertSessionHas('error');
        $this->assertSame('Kasir', $this->kasir->fresh()->jabatan);

        // menaikkan diri sendiri jadi owner
        $this->actingAs($this->manager)
            ->put("/pengguna/{$this->manager->id_pengguna}", $this->payload(['jabatan' => 'Owner']))
            ->assertSessionHas('error');
        $this->assertSame('Manager', $this->manager->fresh()->jabatan);

        // membuat user baru berjabatan owner
        $this->actingAs($this->manager)
            ->post('/pengguna', $this->payload(['jabatan' => 'Owner', 'password' => 'rahasia']))
            ->assertSessionHas('error');
        $this->assertDatabaseMissing('tbl_pengguna', ['jabatan' => 'Owner', 'nama_lengkap' => 'Nama Baru']);
    }

    public function test_owner_can_edit_and_delete_manager(): void
    {
        $this->actingAs($this->owner)
            ->put("/pengguna/{$this->manager->id_pengguna}", $this->payload(['jabatan' => 'Kasir']))
            ->assertSessionHas('success');
        $this->assertSame('Kasir', $this->manager->fresh()->jabatan);

        $this->actingAs($this->owner)->delete("/pengguna/{$this->manager2->id_pengguna}")
            ->assertSessionHas('success');
        $this->assertModelMissing($this->manager2);
    }

    public function test_password_only_changes_when_filled_and_is_not_double_hashed(): void
    {
        $oldHash = $this->kasir->password;

        $this->actingAs($this->owner)
            ->put("/pengguna/{$this->kasir->id_pengguna}", $this->payload(['password' => '']))
            ->assertSessionHas('success');
        $this->assertSame($oldHash, $this->kasir->fresh()->password);

        $this->actingAs($this->owner)
            ->put("/pengguna/{$this->kasir->id_pengguna}", $this->payload(['password' => 'baru123']))
            ->assertSessionHas('success');
        $this->assertTrue(Hash::check('baru123', $this->kasir->fresh()->password));

        $this->post('/logout');
        $this->post('/login', ['username' => $this->kasir->username, 'password' => 'baru123'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_new_user_password_is_hashed_once(): void
    {
        $this->actingAs($this->owner)
            ->post('/pengguna', $this->payload(['nama_user' => 'Kasir Tiga', 'password' => 'kasir3']))
            ->assertSessionHas('success');

        $user = User::where('nama_lengkap', 'Kasir Tiga')->firstOrFail();
        $this->assertTrue(Hash::check('kasir3', $user->password));
    }

    public function test_user_with_transactions_is_not_deleted(): void
    {
        Transaksi::create([
            'no_nota' => 'TRX-1', 'id_pengguna' => $this->kasir->id_pengguna,
            'tanggal_transaksi' => now(), 'total_item' => 1, 'subtotal' => 1000,
            'total_bayar' => 1000, 'jumlah_bayar' => 1000, 'metode_pembayaran' => 'cash',
        ]);

        $this->actingAs($this->owner)->delete("/pengguna/{$this->kasir->id_pengguna}")
            ->assertSessionHas('error');
        $this->assertModelExists($this->kasir);
        $this->assertDatabaseHas('tbl_transaksi', ['no_nota' => 'TRX-1']);
    }

    public function test_buttons_reflect_permissions_for_manager(): void
    {
        $html = $this->actingAs($this->manager)->get('/pengguna')->assertOk()->getContent();

        $this->assertStringNotContainsString("openHapusUserModal({$this->owner->id_pengguna},", $html);
        $this->assertStringNotContainsString("openEditUserModal({$this->owner->id_pengguna},", $html);
        $this->assertStringContainsString("openEditUserModal({$this->kasir->id_pengguna},", $html);
        $this->assertStringNotContainsString('<option value="Owner">Owner</option>', $html);
    }
}
