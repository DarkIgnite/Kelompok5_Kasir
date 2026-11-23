<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\VarianProduk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Hak akses sesuai DFD Level 2 + riwayat (5.1), batal (5.3), detail/struk (6.1).
 */
class HakAksesDfdTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;
    private User $kasir2;
    private VarianProduk $varian;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->kasir = User::factory()->create(['role' => 'kasir', 'nama_lengkap' => 'Kasir Satu']);
        $this->kasir2 = User::factory()->create(['role' => 'kasir']);

        $kat = Kategori::create(['nama_kategori' => 'Sneakers']);
        $produk = Produk::create([
            'id_kategori' => $kat->id_kategori, 'kode_produk' => 'SPT-X', 'nama_produk' => 'Sepatu X',
            'merek' => 'X', 'harga_beli' => 100000, 'harga_jual' => 250000, 'status' => 'aktif',
        ]);
        $this->varian = VarianProduk::create([
            'id_produk' => $produk->id_produk, 'sku' => 'SPT-X-40', 'ukuran' => '40', 'warna' => 'Hitam', 'stok' => 10, 'stok_minimum' => 1,
        ]);
    }

    private function jual(User $kasir, int $qty = 2): Transaksi
    {
        $this->actingAs($kasir)->postJson('/kasir/transaksi', [
            'items' => [['id_varian' => $this->varian->id_varian, 'jumlah' => $qty]],
            'metode_pembayaran' => 'QRIS',
        ])->assertOk();

        return Transaksi::latest('id_transaksi')->first();
    }

    public function test_login_redirects_by_role(): void
    {
        $this->post('/login', ['username' => $this->admin->username, 'password' => 'password'])->assertRedirect(route('dashboard'));
        $this->post('/logout');
        $this->post('/login', ['username' => $this->kasir->username, 'password' => 'password'])->assertRedirect(route('kasir.index'));
    }

    public function test_admin_cannot_use_kasir_features(): void
    {
        $this->actingAs($this->admin)->get('/kasir')->assertRedirect(route('dashboard'))->assertSessionHas('error');
        $this->actingAs($this->admin)->get('/transaksi')->assertRedirect(route('dashboard'));
        $this->actingAs($this->admin)->postJson('/kasir/transaksi', [
            'items' => [['id_varian' => $this->varian->id_varian, 'jumlah' => 1]], 'metode_pembayaran' => 'QRIS',
        ])->assertForbidden();
        $this->assertSame(0, Transaksi::count());
    }

    public function test_kasir_can_view_dashboard_but_not_admin_pages(): void
    {
        $this->actingAs($this->kasir)->get('/dashboard')->assertOk();
        $this->actingAs($this->kasir)->getJson('/dashboard/data')->assertOk()->assertJsonStructure(['stats']);

        foreach (['/kategori', '/produk', '/varian-produk', '/pengguna'] as $url) {
            $this->actingAs($this->kasir)->get($url)->assertRedirect(route('kasir.index'));
        }
        $this->actingAs($this->kasir)->post('/kategori', ['nama_kategori' => 'Hack'])->assertForbidden();
    }

    public function test_sidebar_matches_role(): void
    {
        $kasirHtml = $this->actingAs($this->kasir)->get('/kasir')->getContent();
        $this->assertStringContainsString('Riwayat Transaksi', $kasirHtml);
        $this->assertStringContainsString('title="Dashboard"', $kasirHtml);
        $this->assertStringNotContainsString('title="Produk"', $kasirHtml);

        $adminHtml = $this->actingAs($this->admin)->get('/produk')->getContent();
        $this->assertStringContainsString('title="Dashboard"', $adminHtml);
        $this->assertStringNotContainsString('title="Kasir / Transaksi"', $adminHtml);
    }

    public function test_riwayat_and_detail_struk(): void
    {
        $trx = $this->jual($this->kasir);

        $this->actingAs($this->kasir2)->get('/transaksi')
            ->assertOk()->assertSee($trx->no_nota)->assertSee('Kasir Satu');
        $this->actingAs($this->kasir2)->get('/transaksi?saya=1')->assertOk()->assertDontSee($trx->no_nota);

        $this->actingAs($this->kasir)->get("/transaksi/{$trx->id_transaksi}")
            ->assertOk()->assertSee('Cetak Struk')->assertSee('SPT-X-40')->assertSee('Batalkan Transaksi');
    }

    public function test_kasir_can_cancel_own_transaction_and_stock_returns(): void
    {
        $trx = $this->jual($this->kasir, 3);
        $this->assertSame(7, $this->varian->fresh()->stok);

        $this->actingAs($this->kasir)->post("/transaksi/{$trx->id_transaksi}/batal")->assertSessionHas('success');
        $this->assertSame('dibatalkan', $trx->fresh()->status);
        $this->assertSame(10, $this->varian->fresh()->stok);

        // dibatalkan dua kali tidak menambah stok lagi
        $this->actingAs($this->kasir)->post("/transaksi/{$trx->id_transaksi}/batal")->assertSessionHas('error');
        $this->assertSame(10, $this->varian->fresh()->stok);
    }

    public function test_cannot_cancel_other_kasir_or_old_transaction(): void
    {
        $trx = $this->jual($this->kasir);

        $this->actingAs($this->kasir2)->get("/transaksi/{$trx->id_transaksi}")->assertDontSee('Batalkan Transaksi');
        $this->actingAs($this->kasir2)->post("/transaksi/{$trx->id_transaksi}/batal")->assertSessionHas('error');
        $this->assertSame('selesai', $trx->fresh()->status);

        $trx->update(['tanggal_transaksi' => now()->subDay()]);
        $this->actingAs($this->kasir)->post("/transaksi/{$trx->id_transaksi}/batal")->assertSessionHas('error');
        $this->assertSame('selesai', $trx->fresh()->status);
    }

    public function test_cancelled_sale_not_counted_on_dashboard(): void
    {
        $trx = $this->jual($this->kasir, 2);
        $before = $this->actingAs($this->admin)->getJson('/dashboard/data')->json('stats.total_penjualan');
        $this->assertEquals(500000, $before);

        $this->actingAs($this->kasir)->post("/transaksi/{$trx->id_transaksi}/batal");
        $after = $this->actingAs($this->kasir)->getJson('/dashboard/data')->json('stats.total_penjualan');
        $this->assertEquals(0, $after);
    }

    public function test_database_blocks_deleting_kasir_with_history(): void
    {
        $this->jual($this->kasir);
        $this->expectException(\Illuminate\Database\QueryException::class);
        \DB::table('tbl_pengguna')->where('id_pengguna', $this->kasir->id_pengguna)->delete();
    }
}
