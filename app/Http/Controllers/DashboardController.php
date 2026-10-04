<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\VarianProduk;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $hasTransactions = Transaksi::count() > 0;
        $hasProducts = Produk::count() > 0;

        // Total Transaksi & Penjualan dari DB atau fallback
        if ($hasTransactions) {
            $totalPenjualan = Transaksi::where('status', 'selesai')->sum('total_bayar');
            $totalTransaksi = Transaksi::where('status', 'selesai')->count();
            $rataRataNota = $totalTransaksi > 0 ? ($totalPenjualan / $totalTransaksi) : 0;
            $totalTerjual = Transaksi::where('status', 'selesai')->sum('total_item');
            $lowStockCount = VarianProduk::whereColumn('stok', '<=', 'stok_minimum')->count();

            $stats = [
                'total_penjualan'   => (float) $totalPenjualan,
                'persen_penjualan'  => 14.8,
                'total_transaksi'   => $totalTransaksi,
                'rata_rata_nota'    => (float) $rataRataNota,
                'total_terjual'     => (int) $totalTerjual,
                'peringatan_stok'   => $lowStockCount,
            ];
        } else {
            $stats = [
                'total_penjualan'   => 435400000,
                'persen_penjualan'  => 14.8,
                'total_transaksi'   => 3,
                'rata_rata_nota'    => 145133333,
                'total_terjual'     => 71,
                'peringatan_stok'   => 3,
            ];
        }

        // Restock Alerts dari database atau fallback
        $lowStockVariants = VarianProduk::with('produk')
            ->whereColumn('stok', '<=', 'stok_minimum')
            ->take(5)
            ->get();

        if ($lowStockVariants->isNotEmpty()) {
            $restockAlerts = $lowStockVariants->map(function ($item) {
                return [
                    'name' => $item->produk->nama_produk ?? $item->sku,
                    'detail' => "Size {$item->ukuran} - {$item->warna} - sisa {$item->stok}, min. {$item->stok_minimum}",
                    'status' => $item->stok == 0 ? 'Habis' : 'Kritis',
                ];
            })->toArray();
        } else {
            $restockAlerts = [
                ['name' => 'FayerJyordan', 'detail' => 'Size 44 EUR - Hitam - sisa 0, min. 10', 'status' => 'Habis'],
                ['name' => 'ErthJyordan',  'detail' => 'Size 40 EUR - Abu - sisa 4, min. 5',   'status' => 'Kritis'],
                ['name' => 'WhinJyordan',  'detail' => 'Size 40 EUR - Abu - sisa 4, min. 5',   'status' => 'Kritis'],
            ];
        }

        // Katalog Sepatu dari database jika ada produk, atau fallback data demo
        $products = Produk::with(['kategori', 'varian'])->get();
        if ($products->isNotEmpty()) {
            $shoeCatalog = $products->map(function ($prod) {
                $totalStock = $prod->varian->sum('stok');
                $sizesCount = $prod->varian->pluck('ukuran')->unique()->count();
                $colorsCount = $prod->varian->pluck('warna')->unique()->count();
                $variantStr = "{$sizesCount} ukuran - {$colorsCount} warna";

                $status = 'Normal';
                $statusType = 'normal';
                if ($totalStock == 0) {
                    $status = 'Habis';
                    $statusType = 'habis';
                } elseif ($totalStock <= 10) {
                    $status = 'Kritis';
                    $statusType = 'kritis';
                } elseif ($totalStock > 50) {
                    $status = 'Overstock';
                    $statusType = 'overstock';
                }

                return [
                    'id' => $prod->id_produk,
                    'name' => $prod->nama_produk,
                    'code' => $prod->kode_produk,
                    'image' => $prod->gambar ?: 'images/shoes/water-jyordan.png',
                    'brand' => $prod->merek,
                    'variant' => $variantStr,
                    'unit_price' => 'Rp ' . number_format($prod->harga_jual, 0, ',', '.'),
                    'total_stock' => $totalStock . ' pasang',
                    'status' => $status,
                    'status_type' => $statusType,
                ];
            })->toArray();
        } else {
            $shoeCatalog = [
                [
                    'id' => 1,
                    'name' => 'WaterJyordan',
                    'code' => 'kodeprdk01',
                    'image' => 'images/shoes/water-jyordan.png',
                    'brand' => 'Asuz',
                    'variant' => '4 ukuran - 2 warna',
                    'unit_price' => 'Rp 3.110.000',
                    'total_stock' => '66 pasang',
                    'status' => 'Overstock',
                    'status_type' => 'overstock',
                ],
                [
                    'id' => 2,
                    'name' => 'FayerJyordan',
                    'code' => 'kodeprdk02',
                    'image' => 'images/shoes/fayer-jyordan.png',
                    'brand' => 'Zusa',
                    'variant' => '4 ukuran - 1 warna',
                    'unit_price' => 'Rp6.220.000',
                    'total_stock' => '0 pasang',
                    'status' => 'Habis',
                    'status_type' => 'habis',
                ],
                [
                    'id' => 3,
                    'name' => 'ErthJyordan',
                    'code' => 'kodeprdk03',
                    'image' => 'images/shoes/erth-jyordan.png',
                    'brand' => 'Uzas',
                    'variant' => '3 ukuran - 1 warna',
                    'unit_price' => 'Rp6.220.000',
                    'total_stock' => '5 pasang',
                    'status' => 'Kritis',
                    'status_type' => 'kritis',
                ],
                [
                    'id' => 4,
                    'name' => 'WhinJyordan',
                    'code' => 'kodeprdk04',
                    'image' => 'images/shoes/whin-jyordan.png',
                    'brand' => 'Uzuz',
                    'variant' => '3 ukuran - 2 warna',
                    'unit_price' => 'Rp6.220.000',
                    'total_stock' => '10 pasang',
                    'status' => 'Kritis',
                    'status_type' => 'kritis',
                ],
            ];
        }

        $monthlySales = [
            ['month' => 'Nov', 'height' => 52, 'amount' => 'Rp 226,4jt'],
            ['month' => 'Des', 'height' => 70, 'amount' => 'Rp 305,2jt'],
            ['month' => 'Jan', 'height' => 48, 'amount' => 'Rp 209,1jt'],
            ['month' => 'Feb', 'height' => 36, 'amount' => 'Rp 156,8jt'],
            ['month' => 'Mar', 'height' => 44, 'amount' => 'Rp 191,5jt'],
            ['month' => 'Apr', 'height' => 60, 'amount' => 'Rp 261,3jt'],
            ['month' => 'Mei', 'height' => 64, 'amount' => 'Rp 278,7jt'],
            ['month' => 'Jun', 'height' => 48, 'amount' => 'Rp 209,0jt'],
            ['month' => 'Jul', 'height' => 68, 'amount' => 'Rp 296,2jt'],
            ['month' => 'Agu', 'height' => 82, 'amount' => 'Rp 357,0jt'],
            ['month' => 'Sep', 'height' => 74, 'amount' => 'Rp 322,2jt'],
            ['month' => 'Okt', 'height' => 96, 'amount' => 'Rp 435,4jt'],
        ];

        $topCategories = [
            ['name' => 'Lifestyle', 'count' => 67, 'percent' => 94],
            ['name' => 'Running',   'count' => 2,  'percent' => 3],
            ['name' => 'Sports',    'count' => 1,  'percent' => 2],
            ['name' => 'Casual',    'count' => 1,  'percent' => 1],
        ];

        $categoryMargins = [
            ['category' => 'Lifestyle', 'margin' => '34%', 'is_highlight' => true],
            ['category' => 'Running',   'margin' => '21%', 'is_highlight' => false],
            ['category' => 'Sports',    'margin' => '25%', 'is_highlight' => false],
            ['category' => 'Casual',    'margin' => '41%', 'is_highlight' => true],
        ];

        $slowMoving = [
            [
                'name' => 'WaterJyordan',
                'detail' => 'Size 40 - 41 - 42 - 43 - 44 EUR - Merah - Hitam',
                'note' => 'Sisa <strong>66 - 1</strong> terjual bulan ini',
                'status' => 'Overstock',
            ],
        ];

        return view('dashboard', compact(
            'stats',
            'monthlySales',
            'topCategories',
            'restockAlerts',
            'categoryMargins',
            'slowMoving',
            'shoeCatalog'
        ));
    }
}
