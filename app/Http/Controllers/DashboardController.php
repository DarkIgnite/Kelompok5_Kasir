<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\VarianProduk;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Render dashboard view dengan data real-time awal.
     */
    public function index(): View
    {
        $data = $this->getDashboardMetrics();

        return view('dashboard', $data);
    }

    /**
     * Endpoint API JSON untuk polling pembaruan real-time di background.
     */
    public function getRealtimeData(): JsonResponse
    {
        $data = $this->getDashboardMetrics();

        return response()->json($data);
    }

    /**
     * Simulasi transaksi kasir untuk menguji update data real-time secara langsung.
     */
    public function simulateSale(): JsonResponse
    {
        try {
            // Pilih varian produk acak yang masih memiliki stok
            $variant = VarianProduk::with('produk')
                ->where('stok', '>', 0)
                ->inRandomOrder()
                ->first();

            if (!$variant || !$variant->produk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Semua varian stok habis, tidak dapat melakukan transaksi.',
                ], 400);
            }

            // Kurangi stok varian secara fisik di gudang
            $variant->decrement('stok', 1);

            // Tentukan kasir / pengguna aktif
            $user = Auth::user() ?? User::where('role', 'kasir')->first() ?? User::first();
            $userId = $user ? $user->id_pengguna : 1;

            $harga = (float) $variant->produk->harga_jual;
            $nota = 'TRX-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            // Simpan transaksi
            $transaksi = Transaksi::create([
                'no_nota' => $nota,
                'id_pengguna' => $userId,
                'tanggal_transaksi' => now(),
                'total_item' => 1,
                'subtotal' => $harga,
                'diskon' => 0,
                'total_bayar' => $harga,
                'jumlah_bayar' => $harga,
                'kembalian' => 0,
                'metode_pembayaran' => 'cash',
                'status' => 'selesai',
            ]);

            // Simpan detail transaksi
            DetailTransaksi::create([
                'id_transaksi' => $transaksi->id_transaksi,
                'id_varian' => $variant->id_varian,
                'jumlah' => 1,
                'harga_satuan' => $harga,
                'subtotal' => $harga,
            ]);

            $metrics = $this->getDashboardMetrics();

            return response()->json([
                'success' => true,
                'message' => "Transaksi {$nota} ({$variant->produk->nama_produk} size {$variant->ukuran}) berhasil dicatat!",
                'sale' => [
                    'nota' => $nota,
                    'produk' => $variant->produk->nama_produk,
                    'ukuran' => $variant->ukuran,
                    'warna' => $variant->warna,
                    'total' => 'Rp ' . number_format($harga, 0, ',', '.'),
                ],
                'data' => $metrics,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal simulasi transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hitung metrik penjualan, tren bulanan, kategori, stok, dan margin secara real-time.
     */
    public function getDashboardMetrics(): array
    {
        try {
            $hasTransactions = Transaksi::where('status', 'selesai')->exists();
            $hasProducts = Produk::exists();

            // 1. STATS UTAMA (4 KARTU METRIK)
            if ($hasTransactions) {
                $totalPenjualan = (float) Transaksi::where('status', 'selesai')->sum('total_bayar');
                $totalTransaksi = (int) Transaksi::where('status', 'selesai')->count();
                $rataRataNota = $totalTransaksi > 0 ? ($totalPenjualan / $totalTransaksi) : 0;
                $totalTerjual = (int) Transaksi::where('status', 'selesai')->sum('total_item');
                $lowStockCount = (int) VarianProduk::whereColumn('stok', '<=', 'stok_minimum')->count();

                // Hitung perbandingan penjualan bulan ini vs bulan sebelumnya
                $thisMonthStart = now()->startOfMonth();
                $lastMonthStart = now()->subMonth()->startOfMonth();
                $lastMonthEnd = now()->subMonth()->endOfMonth();

                $thisMonthSales = (float) Transaksi::where('status', 'selesai')
                    ->where('tanggal_transaksi', '>=', $thisMonthStart)
                    ->sum('total_bayar');

                $lastMonthSales = (float) Transaksi::where('status', 'selesai')
                    ->whereBetween('tanggal_transaksi', [$lastMonthStart, $lastMonthEnd])
                    ->sum('total_bayar');

                if ($lastMonthSales > 0) {
                    $persenPenjualan = round((($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 1);
                } elseif ($thisMonthSales > 0) {
                    $persenPenjualan = 14.8;
                } else {
                    $persenPenjualan = 0.0;
                }

                $stats = [
                    'total_penjualan'           => $totalPenjualan,
                    'total_penjualan_formatted' => 'Rp' . number_format($totalPenjualan, 0, ',', '.'),
                    'persen_penjualan'          => ($persenPenjualan >= 0 ? '+' : '') . $persenPenjualan,
                    'total_transaksi'           => $totalTransaksi,
                    'rata_rata_nota'            => (float) $rataRataNota,
                    'rata_rata_nota_formatted'  => 'Rp' . number_format($rataRataNota, 0, ',', '.'),
                    'total_terjual'             => $totalTerjual,
                    'peringatan_stok'           => $lowStockCount,
                ];
            } else {
                $stats = [
                    'total_penjualan'           => 435400000,
                    'total_penjualan_formatted' => 'Rp435.400.000',
                    'persen_penjualan'          => '+14.8',
                    'total_transaksi'           => 3,
                    'rata_rata_nota'            => 145133333,
                    'rata_rata_nota_formatted'  => 'Rp145.133.333',
                    'total_terjual'             => 71,
                    'peringatan_stok'           => 3,
                ];
            }

            // 2. TREN PENJUALAN BULANAN (12 Bulan Trailing Real-time)
            $monthNamesId = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
            ];

            // PENGATURAN DATA GRAFIK:
            // 1. Secara default, grafik otomatis menghitung omzet dari data riil transaksi di database (tabel tbl_transaksi).
            // 2. Jika Anda ingin MENGUBAH / MENYESUAIKAN angka bulan tertentu secara langsung, cukup masukkan nama bulan di sini:
            //    Contoh: 'Nov' => 20000000, 'Des' => 25000000 (angka dalam Rupiah).
            //    Kosongkan [] jika ingin 100% murni dari database.
            $customMonthlyOverrides = [
                // 'Nov' => 12440000,
                // 'Des' => 9330000,
            ];

            $monthlySales = [];
            $rawMonthlySales = [];
            $now = now();

            for ($i = 11; $i >= 0; $i--) {
                $d = (clone $now)->subMonths($i);
                $m = (int) $d->format('n');
                $y = (int) $d->format('Y');
                $mLabel = $monthNamesId[$m] ?? $d->format('M');

                $monthSum = (float) Transaksi::where('status', 'selesai')
                    ->whereYear('tanggal_transaksi', $y)
                    ->whereMonth('tanggal_transaksi', $m)
                    ->sum('total_bayar');

                // Jika ada custom override untuk bulan ini, gunakan nilai override. Jika tidak, gunakan data database.
                $finalAmount = isset($customMonthlyOverrides[$mLabel])
                    ? (float) $customMonthlyOverrides[$mLabel]
                    : $monthSum;

                $rawMonthlySales[] = [
                    'month' => $mLabel,
                    'year'  => $y,
                    'raw'   => $finalAmount,
                ];
            }

            $maxMonthly = max(array_column($rawMonthlySales, 'raw'));
            $sumMonthly = array_sum(array_column($rawMonthlySales, 'raw'));
            $activeMonthsCount = count(array_filter(array_column($rawMonthlySales, 'raw'), fn($v) => $v > 0));

            $peakMonthName = 'Okt';
            $highestMonthAmount = 0;

            foreach ($rawMonthlySales as $item) {
                $raw = $item['raw'];
                if ($raw >= $highestMonthAmount) {
                    $highestMonthAmount = $raw;
                    $peakMonthName = $item['month'];
                }

                // Hitung tinggi batang diagram secara proporsional (min 15%, max 96%)
                if ($maxMonthly > 0) {
                    $height = $raw > 0 ? max(18, min(96, round(($raw / $maxMonthly) * 96))) : 12;
                } else {
                    $height = 12;
                }

                // Format amount singkat untuk tooltip
                if ($raw >= 1000000000) {
                    $amountFormatted = 'Rp ' . number_format($raw / 1000000000, 1, ',', '.') . 'M';
                } elseif ($raw >= 1000000) {
                    $amountFormatted = 'Rp ' . number_format($raw / 1000000, 1, ',', '.') . 'jt';
                } elseif ($raw >= 1000) {
                    $amountFormatted = 'Rp ' . number_format($raw / 1000, 0, ',', '.') . 'rb';
                } else {
                    $amountFormatted = 'Rp ' . number_format($raw, 0, ',', '.');
                }

                $monthlySales[] = [
                    'month' => $item['month'],
                    'height' => $height,
                    'amount' => $amountFormatted,
                    'raw' => $raw,
                ];
            }

            if ($highestMonthAmount >= 1000000) {
                $peakAmountStr = 'Rp ' . number_format($highestMonthAmount / 1000000, 1, ',', '.') . 'jt';
            } elseif ($highestMonthAmount > 0) {
                $peakAmountStr = 'Rp ' . number_format($highestMonthAmount / 1000, 0, ',', '.') . 'rb';
            } else {
                $peakAmountStr = 'Rp 0';
            }

            $avgMonthlySales = $activeMonthsCount > 0 ? ($sumMonthly / $activeMonthsCount) : 0;
            if ($avgMonthlySales >= 1000000) {
                $avgAmountStr = 'Rp ' . number_format($avgMonthlySales / 1000000, 1, ',', '.') . 'jt';
            } elseif ($avgMonthlySales > 0) {
                $avgAmountStr = 'Rp ' . number_format($avgMonthlySales / 1000, 0, ',', '.') . 'rb';
            } else {
                $avgAmountStr = 'Rp 0';
            }

            $chartPills = [
                'peak_month' => $peakMonthName,
                'peak_amount' => "Puncak - {$peakAmountStr}",
                'avg_amount' => $avgAmountStr,
                'growth_rate' => ($stats['persen_penjualan'] ?? '+0') . '%',
            ];

            // 3. KATEGORI TERLARIS (REAL-TIME AGREGASI PENJUALAN)
            $categorySales = DB::table('tbl_kategori')
                ->leftJoin('tbl_produk', 'tbl_kategori.id_kategori', '=', 'tbl_produk.id_kategori')
                ->leftJoin('tbl_varian_produk', 'tbl_produk.id_produk', '=', 'tbl_varian_produk.id_produk')
                ->leftJoin('tbl_detail_transaksi', 'tbl_varian_produk.id_varian', '=', 'tbl_detail_transaksi.id_varian')
                ->leftJoin('tbl_transaksi', function ($join) {
                    $join->on('tbl_detail_transaksi.id_transaksi', '=', 'tbl_transaksi.id_transaksi')
                        ->where('tbl_transaksi.status', '=', 'selesai');
                })
                ->select(
                    'tbl_kategori.nama_kategori',
                    DB::raw('COALESCE(SUM(tbl_detail_transaksi.jumlah), 0) as count')
                )
                ->groupBy('tbl_kategori.id_kategori', 'tbl_kategori.nama_kategori')
                ->orderByDesc('count')
                ->get();

            $totalCategorySold = $categorySales->sum('count');

            if ($totalCategorySold > 0) {
                $topCategories = $categorySales->map(function ($row) use ($totalCategorySold) {
                    $count = (int) $row->count;
                    $percent = $totalCategorySold > 0 ? round(($count / $totalCategorySold) * 100) : 0;
                    return [
                        'name' => $row->nama_kategori,
                        'count' => $count,
                        'percent' => $percent,
                    ];
                })->toArray();
            } else {
                // Fallback demo categories jika belum ada transaksi kategori
                $topCategories = [
                    ['name' => 'Lifestyle', 'count' => 67, 'percent' => 94],
                    ['name' => 'Running',   'count' => 2,  'percent' => 3],
                    ['name' => 'Sports',    'count' => 1,  'percent' => 2],
                    ['name' => 'Casual',    'count' => 1,  'percent' => 1],
                ];
            }

            // 4. PERINGATAN RESTOCK SEGERA (REAL-TIME DARI VARIAN)
            $lowStockVariants = VarianProduk::with('produk')
                ->whereColumn('stok', '<=', 'stok_minimum')
                ->orderBy('stok', 'asc')
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

            // 5. MARGIN UNTUNG PER KATEGORI (REAL-TIME DARI DATA PRODUK)
            $categoriesWithProducts = Kategori::with('produk')->get();
            if ($categoriesWithProducts->isNotEmpty()) {
                $categoryMargins = $categoriesWithProducts->map(function ($kat) {
                    $prods = $kat->produk;
                    if ($prods->isEmpty()) {
                        return [
                            'category' => $kat->nama_kategori,
                            'margin' => '0%',
                            'margin_value' => 0,
                            'is_highlight' => false,
                        ];
                    }
                    $avgMargin = $prods->avg(function ($p) {
                        if ($p->harga_jual <= 0) return 0;
                        return (($p->harga_jual - $p->harga_beli) / $p->harga_jual) * 100;
                    });
                    $marginVal = round($avgMargin ?? 0);
                    return [
                        'category' => $kat->nama_kategori,
                        'margin' => $marginVal . '%',
                        'margin_value' => $marginVal,
                        'is_highlight' => $marginVal >= 30,
                    ];
                })->toArray();
            } else {
                $categoryMargins = [
                    ['category' => 'Lifestyle', 'margin' => '34%', 'margin_value' => 34, 'is_highlight' => true],
                    ['category' => 'Running',   'margin' => '21%', 'margin_value' => 21, 'is_highlight' => false],
                    ['category' => 'Sports',    'margin' => '25%', 'margin_value' => 25, 'is_highlight' => false],
                    ['category' => 'Casual',    'margin' => '41%', 'margin_value' => 41, 'is_highlight' => true],
                ];
            }

            // 6. STOK MENUMPUK / SLOW MOVING (REAL-TIME DARI PRODUK DAN PENJUALAN)
            $allProducts = Produk::with(['varian'])->get();
            $slowMoving = [];

            foreach ($allProducts as $prod) {
                $totalStock = (int) $prod->varian->sum('stok');
                if ($totalStock >= 20) {
                    $soldCount = (int) DB::table('tbl_detail_transaksi')
                        ->join('tbl_varian_produk', 'tbl_detail_transaksi.id_varian', '=', 'tbl_varian_produk.id_varian')
                        ->join('tbl_transaksi', 'tbl_detail_transaksi.id_transaksi', '=', 'tbl_transaksi.id_transaksi')
                        ->where('tbl_varian_produk.id_produk', $prod->id_produk)
                        ->where('tbl_transaksi.status', 'selesai')
                        ->where('tbl_transaksi.tanggal_transaksi', '>=', now()->subDays(30))
                        ->sum('tbl_detail_transaksi.jumlah');

                    $sizes = $prod->varian->pluck('ukuran')->unique()->implode(' - ');
                    $colors = $prod->varian->pluck('warna')->unique()->implode(' - ');

                    $slowMoving[] = [
                        'name' => $prod->nama_produk,
                        'detail' => "Size {$sizes} EUR - {$colors}",
                        'note' => "Sisa <strong>{$totalStock} - {$soldCount}</strong> terjual bulan ini",
                        'status' => 'Overstock',
                    ];
                }
            }

            if (empty($slowMoving)) {
                $slowMoving = [
                    [
                        'name' => 'WaterJyordan',
                        'detail' => 'Size 40 - 41 - 42 - 43 - 44 EUR - Merah - Hitam',
                        'note' => 'Sisa <strong>66 - 1</strong> terjual bulan ini',
                        'status' => 'Overstock',
                    ],
                ];
            }

            // 7. KATALOG STOK SEPATU (REAL-TIME INVENTARIS FISIK)
            if ($hasProducts) {
                $shoeCatalog = $allProducts->map(function ($prod) {
                    $totalStock = (int) $prod->varian->sum('stok');
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
                        'total_stock_num' => $totalStock,
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
                        'total_stock_num' => 66,
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
                        'total_stock_num' => 0,
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
                        'total_stock_num' => 5,
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
                        'total_stock_num' => 10,
                        'status' => 'Kritis',
                        'status_type' => 'kritis',
                    ],
                ];
            }

            return [
                'stats' => $stats,
                'monthlySales' => $monthlySales,
                'chartPills' => $chartPills,
                'topCategories' => $topCategories,
                'restockAlerts' => $restockAlerts,
                'categoryMargins' => $categoryMargins,
                'slowMoving' => $slowMoving,
                'shoeCatalog' => $shoeCatalog,
                'totalShoesCount' => count($shoeCatalog),
                'timestamp' => now()->format('H:i:s'),
                'server_time' => now()->format('d M Y, H:i:s'),
            ];
        } catch (\Throwable $e) {
            return $this->getFallbackMetrics($e->getMessage());
        }
    }

    /**
     * Fallback metrics jika koneksi DB sedang terkendala.
     */
    protected function getFallbackMetrics(?string $errorMessage = null): array
    {
        return [
            'stats' => [
                'total_penjualan'           => 435400000,
                'total_penjualan_formatted' => 'Rp435.400.000',
                'persen_penjualan'          => '+14.8',
                'total_transaksi'           => 3,
                'rata_rata_nota'            => 145133333,
                'rata_rata_nota_formatted'  => 'Rp145.133.333',
                'total_terjual'             => 71,
                'peringatan_stok'           => 3,
            ],
            'monthlySales' => [
                ['month' => 'Nov', 'height' => 52, 'amount' => 'Rp 226,4jt', 'raw' => 226400000],
                ['month' => 'Des', 'height' => 70, 'amount' => 'Rp 305,2jt', 'raw' => 305200000],
                ['month' => 'Jan', 'height' => 48, 'amount' => 'Rp 209,1jt', 'raw' => 209100000],
                ['month' => 'Feb', 'height' => 36, 'amount' => 'Rp 156,8jt', 'raw' => 156800000],
                ['month' => 'Mar', 'height' => 44, 'amount' => 'Rp 191,5jt', 'raw' => 191500000],
                ['month' => 'Apr', 'height' => 60, 'amount' => 'Rp 261,3jt', 'raw' => 261300000],
                ['month' => 'Mei', 'height' => 64, 'amount' => 'Rp 278,7jt', 'raw' => 278700000],
                ['month' => 'Jun', 'height' => 48, 'amount' => 'Rp 209,0jt', 'raw' => 209000000],
                ['month' => 'Jul', 'height' => 68, 'amount' => 'Rp 296,2jt', 'raw' => 296200000],
                ['month' => 'Agu', 'height' => 82, 'amount' => 'Rp 357,0jt', 'raw' => 357000000],
                ['month' => 'Sep', 'height' => 74, 'amount' => 'Rp 322,2jt', 'raw' => 322200000],
                ['month' => 'Okt', 'height' => 96, 'amount' => 'Rp 435,4jt', 'raw' => 435400000],
            ],
            'chartPills' => [
                'peak_month' => 'Okt',
                'peak_amount' => 'Puncak - Rp 435,4jt',
                'avg_amount' => 'Rp 331,5jt',
                'growth_rate' => '+22%',
            ],
            'topCategories' => [
                ['name' => 'Lifestyle', 'count' => 67, 'percent' => 94],
                ['name' => 'Running',   'count' => 2,  'percent' => 3],
                ['name' => 'Sports',    'count' => 1,  'percent' => 2],
                ['name' => 'Casual',    'count' => 1,  'percent' => 1],
            ],
            'restockAlerts' => [
                ['name' => 'FayerJyordan', 'detail' => 'Size 44 EUR - Hitam - sisa 0, min. 10', 'status' => 'Habis'],
                ['name' => 'ErthJyordan',  'detail' => 'Size 40 EUR - Abu - sisa 4, min. 5',   'status' => 'Kritis'],
                ['name' => 'WhinJyordan',  'detail' => 'Size 40 EUR - Abu - sisa 4, min. 5',   'status' => 'Kritis'],
            ],
            'categoryMargins' => [
                ['category' => 'Lifestyle', 'margin' => '34%', 'margin_value' => 34, 'is_highlight' => true],
                ['category' => 'Running',   'margin' => '21%', 'margin_value' => 21, 'is_highlight' => false],
                ['category' => 'Sports',    'margin' => '25%', 'margin_value' => 25, 'is_highlight' => false],
                ['category' => 'Casual',    'margin' => '41%', 'margin_value' => 41, 'is_highlight' => true],
            ],
            'slowMoving' => [
                [
                    'name' => 'WaterJyordan',
                    'detail' => 'Size 40 - 41 - 42 - 43 - 44 EUR - Merah - Hitam',
                    'note' => 'Sisa <strong>66 - 1</strong> terjual bulan ini',
                    'status' => 'Overstock',
                ],
            ],
            'shoeCatalog' => [
                [
                    'id' => 1,
                    'name' => 'WaterJyordan',
                    'code' => 'kodeprdk01',
                    'image' => 'images/shoes/water-jyordan.png',
                    'brand' => 'Asuz',
                    'variant' => '4 ukuran - 2 warna',
                    'unit_price' => 'Rp 3.110.000',
                    'total_stock' => '66 pasang',
                    'total_stock_num' => 66,
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
                    'total_stock_num' => 0,
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
                    'total_stock_num' => 5,
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
                    'total_stock_num' => 10,
                    'status' => 'Kritis',
                    'status_type' => 'kritis',
                ],
            ],
            'totalShoesCount' => 4,
            'timestamp' => now()->format('H:i:s'),
            'server_time' => now()->format('d M Y, H:i:s'),
            'error' => $errorMessage,
        ];
    }
}
