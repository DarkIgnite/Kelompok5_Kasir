<?php

namespace Tests\Feature;

use App\Models\DetailTransaksi;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\VarianProduk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tes untuk 10 temuan audit backend (checkout, stok, diskon, ukuran, PPN,
 * simulate-sale, hapus berantai, SKU/kategori duplikat, limit, bocor error).
 */
class BackendAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;
    private Kategori $kategori;
    private Produk $produk;
    private VarianProduk $v40;
    private VarianProduk $v41;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->kasir = User::factory()->create(['role' => 'kasir']);
        $this->kategori = Kategori::create(['nama_kategori' => 'Sneakers']);
        $this->produk = Produk::create([
            'id_kategori' => $this->kategori->id_kategori,
            'kode_produk' => 'SPT-T1',
            'nama_produk' => 'Sepatu Tes',
            'merek' => 'Tes',
            'harga_beli' => 500000,
            'harga_jual' => 1000000,
            'status' => 'aktif',
        ]);
        $this->v40 = VarianProduk::create(['id_produk' => $this->produk->id_produk, 'sku' => 'SPT-T1-40', 'ukuran' => '40', 'warna' => 'Hitam', 'stok' => 5, 'stok_minimum' => 1]);
        $this->v41 = VarianProduk::create(['id_produk' => $this->produk->id_produk, 'sku' => 'SPT-T1-41', 'ukuran' => '41', 'warna' => 'Hitam', 'stok' => 2, 'stok_minimum' => 1]);
    }

    private function checkout(array $override = [])
    {
        return $this->actingAs($this->kasir)->postJson('/kasir/transaksi', array_merge([
            'items' => [['id_varian' => $this->v41->id_varian, 'jumlah' => 1]],
            'metode_pembayaran' => 'Tunai',
            'jumlah_bayar' => 1000000,
            'diskon' => 0,
        ], $override));
    }

    // 1. Harga dari database, bukan dari browser
    public function test_checkout_ignores_client_price(): void
    {
        $this->checkout(['items' => [['id_varian' => $this->v41->id_varian, 'jumlah' => 1, 'harga' => 1]]])
            ->assertOk()
            ->assertJson(['success' => true, 'subtotal' => 1000000, 'total_bayar' => 1000000]);

        $this->assertDatabaseHas('tbl_detail_transaksi', ['id_varian' => $this->v41->id_varian, 'harga_satuan' => 1000000]);
    }

    // 2. Stok kurang => ditolak, stok tidak berubah, tidak ada nota
    public function test_checkout_rejects_insufficient_stock(): void
    {
        $this->checkout(['items' => [['id_varian' => $this->v41->id_varian, 'jumlah' => 3]], 'jumlah_bayar' => 5000000])
            ->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonFragment(['message' => 'Stok Sepatu Tes ukuran 41 (Hitam) tidak cukup: tersisa 2, diminta 3.']);

        $this->assertSame(2, $this->v41->fresh()->stok);
        $this->assertSame(0, Transaksi::count());
    }

    public function test_same_variant_on_two_lines_is_summed_for_stock_check(): void
    {
        $this->checkout(['items' => [
            ['id_varian' => $this->v41->id_varian, 'jumlah' => 2],
            ['id_varian' => $this->v41->id_varian, 'jumlah' => 1],
        ], 'jumlah_bayar' => 5000000])->assertStatus(422);
    }

    public function test_successful_checkout_decrements_stock(): void
    {
        $this->checkout(['items' => [['id_varian' => $this->v40->id_varian, 'jumlah' => 2]], 'jumlah_bayar' => 2500000])
            ->assertOk()
            ->assertJson(['total_item' => 2, 'kembalian' => 500000, 'metode_pembayaran' => 'Tunai']);

        $this->assertSame(3, $this->v40->fresh()->stok);
    }

    public function test_inactive_product_cannot_be_sold(): void
    {
        $this->produk->update(['status' => 'nonaktif']);
        $this->checkout()->assertStatus(422);
    }

    // 3. Diskon & jumlah bayar divalidasi server
    public function test_negative_or_excessive_discount_rejected(): void
    {
        $this->checkout(['diskon' => -100])->assertStatus(422);
        $this->checkout(['diskon' => 5000000])->assertStatus(422)
            ->assertJsonFragment(['message' => 'Diskon tidak boleh melebihi subtotal.']);
        $this->assertSame(0, Transaksi::count());
    }

    public function test_cash_payment_must_cover_total(): void
    {
        $this->checkout(['jumlah_bayar' => 999999])->assertStatus(422);
        $this->checkout(['jumlah_bayar' => null])->assertStatus(422);
    }

    public function test_qris_records_exact_amount(): void
    {
        $this->checkout(['metode_pembayaran' => 'QRIS', 'jumlah_bayar' => 1])
            ->assertOk()
            ->assertJson(['jumlah_bayar' => 1000000, 'kembalian' => 0, 'metode_pembayaran' => 'QRIS']);
    }

    // 4. Ukuran wajib dipilih (tidak lagi otomatis varian pertama)
    public function test_variant_is_required(): void
    {
        $this->checkout(['items' => [['id_produk' => $this->produk->id_produk, 'jumlah' => 1]]])->assertStatus(422);
    }

    public function test_kasir_page_sends_variant_list_for_size_popup(): void
    {
        $this->actingAs($this->kasir)->get('/kasir')
            ->assertOk()
            ->assertSee('id="sizeModal"', false)
            ->assertSee('"ukuran":"41"', false);
    }

    // 5. Tanpa PPN
    public function test_no_tax_in_total_or_response(): void
    {
        $res = $this->checkout()->assertOk();
        $this->assertArrayNotHasKey('pajak', $res->json());
        $this->assertEquals(1000000, $res->json('total_bayar'));

        $this->actingAs($this->kasir)->get('/kasir')->assertDontSee('Pajak 11%');
    }

    // 6. simulate-sale sudah dihapus
    public function test_kasir_cannot_simulate_sale(): void
    {
        $this->actingAs($this->kasir)->postJson('/dashboard/simulate-sale')->assertStatus(404);
        $this->assertSame(0, Transaksi::count());
    }

    // 7. Tidak bisa menghapus data yang punya riwayat penjualan
    public function test_cannot_delete_sold_variant_product_or_used_category(): void
    {
        $this->checkout()->assertOk();

        $this->actingAs($this->admin)->delete("/varian-produk/{$this->v41->id_varian}")->assertSessionHas('error');
        $this->actingAs($this->admin)->delete("/produk/{$this->produk->id_produk}")->assertSessionHas('error');
        $this->actingAs($this->admin)->delete("/kategori/{$this->kategori->id_kategori}")->assertSessionHas('error');

        $this->assertNotNull($this->v41->fresh());
        $this->assertSame(1, DetailTransaksi::count());
    }

    public function test_unsold_variant_and_empty_category_can_be_deleted(): void
    {
        $this->actingAs($this->admin)->delete("/varian-produk/{$this->v40->id_varian}")->assertSessionHas('success');
        $this->assertNull($this->v40->fresh());

        $kosong = Kategori::create(['nama_kategori' => 'Kosong']);
        $this->actingAs($this->admin)->delete("/kategori/{$kosong->id_kategori}")->assertSessionHas('success');
    }

    // 8. SKU / varian / kategori duplikat
    public function test_duplicate_variant_rejected_instead_of_500(): void
    {
        $this->actingAs($this->admin)->from('/varian-produk')->post('/varian-produk', [
            'id_produk' => $this->produk->id_produk, 'ukuran' => '40', 'warna' => 'hitam', 'stok_minimum' => 1, 'stok' => 3,
        ])->assertRedirect('/varian-produk')->assertSessionHasErrors('ukuran');

        $this->assertSame(2, VarianProduk::count());
    }

    public function test_colliding_sku_gets_suffix(): void
    {
        foreach (['Merah', 'Merah Muda'] as $warna) {
            $this->actingAs($this->admin)->post('/varian-produk', [
                'id_produk' => $this->produk->id_produk, 'ukuran' => '42', 'warna' => $warna, 'stok_minimum' => 1, 'stok' => 1,
            ])->assertSessionHasNoErrors();
        }
        $this->assertEqualsCanonicalizing(
            ['SPT-T1-42-MER', 'SPT-T1-42-MER-2'],
            VarianProduk::where('ukuran', '42')->pluck('sku')->all()
        );
    }

    public function test_variant_update_to_existing_size_rejected(): void
    {
        $this->actingAs($this->admin)->put("/varian-produk/{$this->v41->id_varian}", [
            'id_produk' => $this->produk->id_produk, 'ukuran' => '40', 'warna' => 'Hitam', 'stok_minimum' => 1, 'stok' => 2,
        ])->assertSessionHasErrors('ukuran');
    }

    public function test_duplicate_category_name_rejected(): void
    {
        $this->actingAs($this->admin)->post('/kategori', ['nama_kategori' => 'Sneakers'])->assertSessionHasErrors('nama_kategori');

        // Update dengan nama sendiri tetap boleh
        $this->actingAs($this->admin)->put("/kategori/{$this->kategori->id_kategori}", ['nama_kategori' => 'Sneakers', 'keterangan' => 'x'])
            ->assertSessionHasNoErrors();
    }

    // 9. ?limit= dibatasi
    public function test_limit_is_clamped(): void
    {
        foreach (['/produk', '/kategori', '/pengguna', '/varian-produk'] as $url) {
            $this->actingAs($this->admin)->get($url . '?limit=0')->assertOk();
            $this->actingAs($this->admin)->get($url . '?limit=999999')->assertOk();
        }
        $res = $this->actingAs($this->admin)->get('/varian-produk?limit=999999');
        $this->assertSame(100, $res->viewData('varian')->perPage());
        $res = $this->actingAs($this->admin)->get('/produk?limit=0');
        $this->assertSame(10, $res->viewData('produk')->perPage());
    }

    // 10. Pesan error internal tidak dikirim ke browser
    public function test_unknown_variant_gives_friendly_message(): void
    {
        $res = $this->checkout(['items' => [['id_varian' => 99999, 'jumlah' => 1]]])->assertStatus(422);
        $this->assertStringNotContainsString('SQLSTATE', $res->getContent());
        $this->assertStringContainsString('Varian produk tidak ditemukan', $res->getContent());
    }
}
