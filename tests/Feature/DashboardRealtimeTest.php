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

class DashboardRealtimeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->admin()->create();

        $kategori = Kategori::create(['nama_kategori' => 'Lifestyle']);
        $produk = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'kode_produk' => 'P01',
            'nama_produk' => 'Nike Air Force 1',
            'merek' => 'Nike',
            'harga_beli' => 100000,
            'harga_jual' => 200000,
            'status' => 'aktif',
        ]);
        VarianProduk::create([
            'id_produk' => $produk->id_produk,
            'sku' => 'P01-42-HTM',
            'ukuran' => '42',
            'warna' => 'Hitam',
            'stok' => 10,
            'stok_minimum' => 2,
        ]);
    }

    private function makeSale(string $tanggal, float $total, int $item = 1): void
    {
        $trx = Transaksi::create([
            'no_nota' => 'TRX-' . uniqid(),
            'id_pengguna' => $this->user->id_pengguna,
            'tanggal_transaksi' => $tanggal,
            'total_item' => $item,
            'subtotal' => $total,
            'total_bayar' => $total,
            'jumlah_bayar' => $total,
            'metode_pembayaran' => 'cash',
            'status' => 'selesai',
        ]);
        DetailTransaksi::create([
            'id_transaksi' => $trx->id_transaksi,
            'id_varian' => VarianProduk::first()->id_varian,
            'jumlah' => $item,
            'harga_satuan' => $total / $item,
            'subtotal' => $total,
        ]);
    }

    public function test_guest_is_redirected_when_accessing_dashboard_data(): void
    {
        $this->get('/dashboard/data')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard_page(): void
    {
        $this->actingAs($this->user)->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('Dashboard Penjualan & Inventaris', false)
            ->assertSee('flash-alert-stack', false)
            ->assertDontSee('toast-container', false);
    }

    public function test_authenticated_user_can_fetch_realtime_metrics_json(): void
    {
        $this->actingAs($this->user)->getJson('/dashboard/data')
            ->assertStatus(200)
            ->assertJsonStructure([
                'stats' => ['total_penjualan', 'total_penjualan_formatted', 'persen_penjualan',
                    'total_transaksi', 'rata_rata_nota', 'total_terjual', 'peringatan_stok'],
                'monthlySales', 'chartPills', 'topCategories', 'restockAlerts',
                'categoryMargins', 'slowMoving', 'shoeCatalog', 'timestamp',
            ]);
    }

    public function test_simulate_sale_endpoint_is_removed(): void
    {
        $this->actingAs($this->user)->postJson('/dashboard/simulate-sale')->assertStatus(404);
    }

    public function test_date_filter_updates_kpi_and_chart_but_not_stock_warning(): void
    {
        $this->makeSale('2026-03-05 10:00:00', 300000, 2);
        $this->makeSale('2026-03-20 10:00:00', 200000, 1);
        $this->makeSale('2026-05-01 10:00:00', 999000, 5); // di luar rentang

        $all = $this->actingAs($this->user)->getJson('/dashboard/data')->json('stats.peringatan_stok');

        $res = $this->actingAs($this->user)
            ->getJson('/dashboard/data?start=2026-03-01&end=2026-03-31&per=minggu')
            ->assertOk();

        $this->assertEquals(500000, $res->json('stats.total_penjualan'));
        $this->assertSame(2, $res->json('stats.total_transaksi'));
        $this->assertSame(3, $res->json('stats.total_terjual'));
        $this->assertSame($all, $res->json('stats.peringatan_stok'));
        $this->assertSame('minggu', $res->json('filter.per'));
        $this->assertEquals(500000, array_sum(array_column($res->json('monthlySales'), 'raw')));
    }

    public function test_per_hari_and_bulan_bucket_counts(): void
    {
        $hari = $this->actingAs($this->user)
            ->getJson('/dashboard/data?start=2026-03-01&end=2026-03-31&per=hari')->assertOk();
        $this->assertCount(31, $hari->json('monthlySales'));

        $bulan = $this->actingAs($this->user)
            ->getJson('/dashboard/data?start=2025-11-01&end=2026-10-31&per=bulan')->assertOk();
        $this->assertCount(12, $bulan->json('monthlySales'));
    }

    public function test_chart_never_exceeds_150_bars(): void
    {
        // 2 tahun per hari (~730 batang) -> otomatis jadi per minggu (~105 batang)
        $res = $this->actingAs($this->user)
            ->getJson('/dashboard/data?start=2024-01-01&end=2025-12-31&per=hari')->assertOk();
        $this->assertLessThanOrEqual(150, count($res->json('monthlySales')));
        $this->assertTrue($res->json('filter.per_adjusted'));

        // 20 tahun per bulan (240 batang) -> dipotong 150 batang terakhir
        $res = $this->actingAs($this->user)
            ->getJson('/dashboard/data?start=2006-01-01&end=2025-12-31&per=bulan')->assertOk();
        $this->assertCount(150, $res->json('monthlySales'));
        $this->assertTrue($res->json('filter.truncated'));
    }

    public function test_invalid_date_range_is_rejected(): void
    {
        $this->actingAs($this->user)
            ->getJson('/dashboard/data?start=2026-05-01&end=2026-04-01&per=hari')
            ->assertStatus(422);

        $this->actingAs($this->user)
            ->getJson('/dashboard/data?start=2026-05-01&end=2026-05-02&per=tahun')
            ->assertStatus(422);
    }

    public function test_default_dashboard_uses_real_data_without_fake_baseline(): void
    {
        $this->makeSale(now()->startOfMonth()->addDay()->format('Y-m-d 10:00:00'), 500000, 2);
        $this->makeSale(now()->subYears(2)->format('Y-m-d 10:00:00'), 900000, 1); // di luar 12 bulan

        $res = $this->actingAs($this->user)->getJson('/dashboard/data')->assertOk();
        $this->assertEquals(500000, $res->json('stats.total_penjualan'));
        $this->assertSame(1, $res->json('stats.total_transaksi'));
        $this->assertSame(2, $res->json('stats.total_terjual'));
        $this->assertCount(12, $res->json('monthlySales'));
        $this->assertEquals(500000, array_sum(array_column($res->json('monthlySales'), 'raw')));

        $html = $this->actingAs($this->user)->get('/dashboard')->assertOk()->getContent();
        $this->assertStringContainsString('Rp500.000', $html);
        foreach (['435.400.000', 'Jyordan', '14.8', 'Target bulanan tercapai'] as $palsu) {
            $this->assertStringNotContainsString($palsu, $html, "Masih ada data palsu: {$palsu}");
        }
    }

    public function test_dashboard_renders_with_empty_database(): void
    {
        DetailTransaksi::query()->delete();
        Transaksi::query()->delete();
        Produk::query()->delete();

        $res = $this->actingAs($this->user)->getJson('/dashboard/data')->assertOk();
        $this->assertEquals(0, $res->json('stats.total_penjualan'));
        $this->assertSame([], $res->json('shoeCatalog'));
        $this->assertSame([], $res->json('restockAlerts'));

        $this->actingAs($this->user)->get('/dashboard')->assertOk()
            ->assertSee('Belum ada produk')
            ->assertDontSee('Jyordan');
    }
}
