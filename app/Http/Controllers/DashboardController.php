<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\VarianProduk;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Batas aman jumlah batang grafik agar mode hari/minggu tidak membuat grafik penuh/error.
     */
    public const MAX_BARS = 150;

    protected const MONTH_NAMES_ID = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    /**
     * Render dashboard view dengan data real-time awal.
     */
    public function index(): View
    {
        $data = $this->getDashboardMetrics();

        // Tanggal transaksi pertama, dipakai tombol "Sejak toko buka" pada filter grafik
        $first = Transaksi::where('status', 'selesai')->min('tanggal_transaksi');
        $data['firstTransactionDate'] = $first ? Carbon::parse($first)->format('Y-m-d') : now()->format('Y-m-d');

        return view('dashboard', $data);
    }

    /**
     * Endpoint API JSON untuk polling pembaruan real-time di background.
     */
    public function getRealtimeData(Request $request): JsonResponse
    {
        // Tanpa parameter: perilaku lama (dipakai polling default)
        if (! $request->hasAny(['start', 'end', 'per'])) {
            return response()->json($this->getDashboardMetrics());
        }

        $validated = $request->validate([
            'start' => ['required', 'date_format:Y-m-d', 'after_or_equal:2000-01-01'],
            'end'   => ['required', 'date_format:Y-m-d', 'after_or_equal:start'],
            'per'   => ['nullable', 'in:hari,minggu,bulan'],
        ]);

        $start = Carbon::createFromFormat('Y-m-d', $validated['start'])->startOfDay();
        $end = Carbon::createFromFormat('Y-m-d', $validated['end'])->endOfDay();
        $per = $validated['per'] ?? 'bulan';

        $data = $this->getDashboardMetrics();
        $filtered = $this->getFilteredSalesMetrics($start, $end, $per);

        // Hanya 3 KPI pertama + grafik tren yang ikut filter.
        // Peringatan stok (card ke-4) tetap kondisi gudang saat ini.
        $data['stats'] = array_merge($data['stats'], $filtered['stats']);
        $data['monthlySales'] = $filtered['monthlySales'];
        $data['chartPills'] = $filtered['chartPills'];
        $data['filter'] = $filtered['filter'];

        return response()->json($data);
    }

    /**
     * Hitung KPI penjualan & tren grafik untuk rentang tanggal tertentu.
     *
     * @return array{stats: array, monthlySales: array, chartPills: array, filter: array}
     */
    public function getFilteredSalesMetrics(Carbon $start, Carbon $end, string $per = 'bulan'): array
    {
        $requestedPer = $per;

        // Batas aman 150 batang: naikkan satuan (hari -> minggu -> bulan) jika terlalu banyak
        $order = ['hari', 'minggu', 'bulan'];
        while ($this->countBuckets($start, $end, $per) > self::MAX_BARS && $per !== 'bulan') {
            $per = $order[array_search($per, $order) + 1];
        }

        // Satu query untuk seluruh rentang, dikelompokkan di PHP (aman untuk SQLite & MySQL)
        $rows = Transaksi::where('status', 'selesai')
            ->whereBetween('tanggal_transaksi', [$start, $end])
            ->get(['tanggal_transaksi', 'total_bayar', 'total_item']);

        $buckets = [];
        foreach ($this->bucketPeriod($start, $end, $per) as $key => $label) {
            $buckets[$key] = ['month' => $label, 'raw' => 0.0];
        }

        foreach ($rows as $row) {
            $key = $this->bucketKey(Carbon::parse($row->tanggal_transaksi), $per);
            if (isset($buckets[$key])) {
                $buckets[$key]['raw'] += (float) $row->total_bayar;
            }
        }

        $truncated = false;
        if (count($buckets) > self::MAX_BARS) {
            // Rentang sangat panjang (> 150 bulan): tampilkan 150 batang terakhir saja
            $buckets = array_slice($buckets, -self::MAX_BARS, null, true);
            $truncated = true;
        }

        $raws = array_column($buckets, 'raw');
        $maxRaw = $raws ? max($raws) : 0;
        $monthlySales = [];
        $peakLabel = '-';
        $peakRaw = 0;
        foreach ($buckets as $bucket) {
            $raw = $bucket['raw'];
            if ($raw > $peakRaw) {
                $peakRaw = $raw;
                $peakLabel = $bucket['month'];
            }
            $monthlySales[] = [
                'month'  => $bucket['month'],
                // Tanpa penjualan = garis tipis 2%, supaya tidak terlihat seperti ada transaksi
                'height' => ($maxRaw > 0 && $raw > 0) ? max(18, min(96, round(($raw / $maxRaw) * 96))) : 2,
                'amount' => $this->formatShortRupiah($raw),
                'raw'    => $raw,
            ];
        }

        $totalPenjualan = (float) $rows->sum('total_bayar');
        $totalTransaksi = $rows->count();
        $totalTerjual = (int) $rows->sum('total_item');
        $rataRata = $totalTransaksi > 0 ? $totalPenjualan / $totalTransaksi : 0;

        // Pertumbuhan dibanding periode sebelumnya dengan panjang yang sama
        $days = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
        $prevEnd = $start->copy()->subDay()->endOfDay();
        $prevStart = $start->copy()->subDays($days)->startOfDay();
        $prevPenjualan = (float) Transaksi::where('status', 'selesai')
            ->whereBetween('tanggal_transaksi', [$prevStart, $prevEnd])
            ->sum('total_bayar');
        $growth = $prevPenjualan > 0 ? (($totalPenjualan - $prevPenjualan) / $prevPenjualan) * 100 : 0;
        $growthStr = ($growth >= 0 ? '+' : '') . number_format($growth, 1, '.', '');

        $activeBars = count(array_filter($raws, fn ($v) => $v > 0));

        return [
            'stats' => [
                'total_penjualan'           => $totalPenjualan,
                'total_penjualan_formatted' => 'Rp' . number_format($totalPenjualan, 0, ',', '.'),
                'persen_penjualan'          => $growthStr,
                'total_transaksi'           => $totalTransaksi,
                'rata_rata_nota'            => (float) $rataRata,
                'rata_rata_nota_formatted'  => 'Rp' . number_format($rataRata, 0, ',', '.'),
                'total_terjual'             => $totalTerjual,
            ],
            'monthlySales' => $monthlySales,
            'chartPills' => [
                'peak_month'  => $peakLabel,
                'peak_amount' => 'Puncak - ' . $this->formatShortRupiah($peakRaw),
                'avg_amount'  => $this->formatShortRupiah($activeBars > 0 ? array_sum($raws) / $activeBars : 0),
                'growth_rate' => $growthStr . '%',
            ],
            'filter' => [
                'start'         => $start->format('Y-m-d'),
                'end'           => $end->format('Y-m-d'),
                'per'           => $per,
                'per_requested' => $requestedPer,
                'per_adjusted'  => $per !== $requestedPer,
                'days'          => $days,
                'bar_count'     => count($monthlySales),
                'truncated'     => $truncated,
                'max_bars'      => self::MAX_BARS,
                'range_label'   => $this->rangeLabel($start, $end),
            ],
        ];
    }

    protected function countBuckets(Carbon $start, Carbon $end, string $per): int
    {
        return count($this->bucketPeriod($start, $end, $per));
    }

    /**
     * Daftar bucket (key => label) berurutan untuk rentang tanggal.
     */
    protected function bucketPeriod(Carbon $start, Carbon $end, string $per): array
    {
        $buckets = [];
        $multiYear = $start->year !== $end->year;

        if ($per === 'hari') {
            foreach (CarbonPeriod::create($start->copy()->startOfDay(), '1 day', $end->copy()->startOfDay()) as $d) {
                $buckets[$this->bucketKey($d, 'hari')] = $d->format('d/m');
            }
        } elseif ($per === 'minggu') {
            $cursor = $start->copy()->startOfWeek(Carbon::MONDAY);
            while ($cursor->lte($end)) {
                $buckets[$this->bucketKey($cursor, 'minggu')] = $cursor->format('d/m');
                $cursor->addWeek();
            }
        } else {
            $cursor = $start->copy()->startOfMonth();
            while ($cursor->lte($end)) {
                $label = self::MONTH_NAMES_ID[$cursor->month] . ($multiYear ? ' ' . $cursor->format('y') : '');
                $buckets[$this->bucketKey($cursor, 'bulan')] = $label;
                $cursor->addMonthNoOverflow();
            }
        }

        return $buckets;
    }

    protected function bucketKey(Carbon $date, string $per): string
    {
        return match ($per) {
            'hari'   => $date->format('Y-m-d'),
            'minggu' => $date->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
            default  => $date->format('Y-m'),
        };
    }

    protected function rangeLabel(Carbon $start, Carbon $end): string
    {
        $fmt = fn (Carbon $d) => $d->day . ' ' . self::MONTH_NAMES_ID[$d->month] . ' ' . $d->year;

        return $fmt($start) . ' - ' . $fmt($end);
    }

    protected function formatShortRupiah(float $raw): string
    {
        if ($raw >= 1000000000) {
            return 'Rp ' . number_format($raw / 1000000000, 1, ',', '.') . 'M';
        }
        if ($raw >= 1000000) {
            return 'Rp ' . number_format($raw / 1000000, 1, ',', '.') . 'jt';
        }
        if ($raw >= 1000) {
            return 'Rp ' . number_format($raw / 1000, 0, ',', '.') . 'rb';
        }

        return 'Rp ' . number_format($raw, 0, ',', '.');
    }

    /**
     * Hitung metrik penjualan, tren bulanan, kategori, stok, dan margin secara real-time.
     */
    public function getDashboardMetrics(): array
    {
        try {
            $hasProducts = Produk::exists();

            // 1+2. KPI & TREN PENJUALAN: data asli 12 bulan terakhir
            //      (sama dengan pilihan "12 bulan terakhir" pada filter grafik).
            [$defaultStart, $defaultEnd] = $this->defaultRange();
            $sales = $this->getFilteredSalesMetrics($defaultStart, $defaultEnd, 'bulan');

            $lowStockCount = (int) VarianProduk::whereColumn('stok', '<=', 'stok_minimum')->count();

            $stats = array_merge($sales['stats'], [
                'peringatan_stok' => $lowStockCount,
            ]);
            $monthlySales = $sales['monthlySales'];
            $chartPills = $sales['chartPills'];
            $filter = $sales['filter'];

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

            $topCategories = $categorySales->map(function ($row) use ($totalCategorySold) {
                $count = (int) $row->count;
                return [
                    'name' => $row->nama_kategori,
                    'count' => $count,
                    'percent' => $totalCategorySold > 0 ? round(($count / $totalCategorySold) * 100) : 0,
                ];
            })->toArray();

            // 4. PERINGATAN RESTOCK SEGERA (REAL-TIME DARI VARIAN)
            $lowStockVariants = VarianProduk::with('produk')
                ->whereColumn('stok', '<=', 'stok_minimum')
                ->orderBy('stok', 'asc')
                ->take(5)
                ->get();

            $restockAlerts = $lowStockVariants->map(function ($item) {
                return [
                    'name' => $item->produk->nama_produk ?? $item->sku,
                    'detail' => "Size {$item->ukuran} - {$item->warna} - sisa {$item->stok}, min. {$item->stok_minimum}",
                    'status' => $item->stok == 0 ? 'Habis' : 'Kritis',
                ];
            })->toArray();

            // 5. MARGIN UNTUNG PER KATEGORI (REAL-TIME DARI DATA PRODUK)
            $categoriesWithProducts = Kategori::with('produk')->get();
            $categoryMargins = [];
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
            }

            // 6. STOK MENUMPUK / SLOW MOVING (REAL-TIME DARI PRODUK DAN PENJUALAN)
            $allProducts = Produk::with(['varian'])->get();
            $slowMoving = [];

            // Penjualan 30 hari terakhir per produk dalam satu query (sebelumnya 1 query per produk)
            $soldLast30 = DB::table('tbl_detail_transaksi')
                ->join('tbl_varian_produk', 'tbl_detail_transaksi.id_varian', '=', 'tbl_varian_produk.id_varian')
                ->join('tbl_transaksi', 'tbl_detail_transaksi.id_transaksi', '=', 'tbl_transaksi.id_transaksi')
                ->where('tbl_transaksi.status', 'selesai')
                ->where('tbl_transaksi.tanggal_transaksi', '>=', now()->subDays(30))
                ->groupBy('tbl_varian_produk.id_produk')
                ->selectRaw('tbl_varian_produk.id_produk as id_produk, SUM(tbl_detail_transaksi.jumlah) as terjual')
                ->pluck('terjual', 'id_produk');

            foreach ($allProducts as $prod) {
                $totalStock = (int) $prod->varian->sum('stok');
                if ($totalStock >= 20) {
                    $soldCount = (int) ($soldLast30[$prod->id_produk] ?? 0);

                    $sizes = $prod->varian->pluck('ukuran')->unique()->implode(' - ');
                    $colors = $prod->varian->pluck('warna')->unique()->implode(' - ');

                    $slowMoving[] = [
                        '_sold' => $soldCount,
                        '_stock' => $totalStock,
                        'name' => $prod->nama_produk,
                        'detail' => "Size {$sizes} EUR - {$colors}",
                        'note' => "Sisa <strong>{$totalStock} - {$soldCount}</strong> terjual bulan ini",
                        'status' => 'Overstock',
                    ];
                }
            }

            // Tampilkan 3 produk paling menumpuk: paling sedikit terjual, lalu stok terbanyak
            usort($slowMoving, fn ($a, $b) => [$a['_sold'], -$a['_stock']] <=> [$b['_sold'], -$b['_stock']]);
            $slowMoving = array_map(function ($item) {
                unset($item['_sold'], $item['_stock']);
                return $item;
            }, array_slice($slowMoving, 0, 3));

            // 7. KATALOG STOK SEPATU (REAL-TIME INVENTARIS FISIK)
            $shoeCatalog = [];
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

            // Mapping detail varian untuk sub-tabel accordion
                $variantsList = $prod->varian->sortBy('ukuran')->values()->map(function ($v) {
                    if ($v->stok == 0 && $v->stok_minimum == 0) {
                        $vStatus = 'Kosong';
                        $vType = 'kosong';
                    } elseif ($v->stok == 0) {
                        $vStatus = 'Habis';
                        $vType = 'habis';
                    } elseif ($v->stok_minimum > 0 && $v->stok < $v->stok_minimum) {
                        $vStatus = 'Kritis';
                        $vType = 'kritis';
                    } else {
                        $vStatus = 'Aman';
                        $vType = 'aman';
                    }

                    return [
                        'sku'          => $v->sku,
                        'ukuran'       => $v->ukuran,
                        'warna'        => $v->warna,
                        'stok'         => $v->stok,
                        'stok_minimum' => $v->stok_minimum,
                        'status'       => $vStatus,
                        'status_type'  => $vType,
                    ];
                })->toArray();

                    return [
                        'id' => $prod->id_produk,
                        'name' => $prod->nama_produk,
                        'code' => $prod->kode_produk,
                        'image' => $prod->gambar ?: 'images/shoes/placeholder.svg',
                        'image_url' => $prod->gambar_url,
                        'image_remote' => $prod->gambar_is_remote,
                        'brand' => $prod->merek,
                        'variant' => $variantStr,
                        'unit_price' => 'Rp ' . number_format($prod->harga_jual, 0, ',', '.'),
                        'total_stock' => $totalStock . ' pasang',
                        'total_stock_num' => $totalStock,
                        'status' => $status,
                        'status_type' => $statusType,
                        'variants'        => $variantsList,
                    ];
                })->toArray();
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
                'filter' => $filter,
                'timestamp' => now()->format('H:i:s'),
                'server_time' => now()->format('d M Y, H:i:s'),
            ];
        } catch (\Throwable $e) {
            report($e); // detail error hanya di storage/logs/laravel.log
            return $this->getFallbackMetrics('Data dashboard gagal dimuat. Silakan muat ulang halaman.');
        }
    }

    /**
     * Fallback metrics jika koneksi DB sedang terkendala.
     */
    protected function getFallbackMetrics(?string $errorMessage = null): array
    {
        return [
            'stats' => [
                'total_penjualan'           => 0,
                'total_penjualan_formatted' => 'Rp0',
                'persen_penjualan'          => '+0.0',
                'total_transaksi'           => 0,
                'rata_rata_nota'            => 0,
                'rata_rata_nota_formatted'  => 'Rp0',
                'total_terjual'             => 0,
                'peringatan_stok'           => 0,
            ],
            'monthlySales'    => [],
            'chartPills'      => ['peak_month' => '-', 'peak_amount' => 'Belum ada data', 'avg_amount' => 'Rp 0', 'growth_rate' => '+0.0%'],
            'topCategories'   => [],
            'restockAlerts'   => [],
            'categoryMargins' => [],
            'slowMoving'      => [],
            'shoeCatalog'     => [],
            'totalShoesCount' => 0,
            'filter'          => null,
            'timestamp'       => now()->format('H:i:s'),
            'server_time'     => now()->format('d M Y, H:i:s'),
            'error'           => $errorMessage,
        ];
    }

    /**
     * Rentang default dashboard: awal bulan 11 bulan lalu s.d. hari ini (12 bulan).
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function defaultRange(): array
    {
        return [now()->startOfMonth()->subMonthsNoOverflow(11)->startOfDay(), now()->endOfDay()];
    }
}
