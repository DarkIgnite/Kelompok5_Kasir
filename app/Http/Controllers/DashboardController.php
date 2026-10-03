<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_penjualan'   => 435400000,
            'persen_penjualan'  => 14.8,
            'total_transaksi'   => 3,
            'rata_rata_nota'    => 145133333,
            'total_terjual'     => 71,
            'peringatan_stok'   => 1,
        ];

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

        $restockAlerts = [
            [
                'name' => 'FayerJyordan',
                'detail' => 'Size 44 EUR - Hitam - sisa 0, min. 10',
                'status' => 'Kritis',
            ],
            [
                'name' => 'ErthJyordan',
                'detail' => 'Size 40 EUR - Abu - sisa 4, min. 5',
                'status' => 'Kritis',
            ],
            [
                'name' => 'LoremIpsum',
                'detail' => 'LoremIpsum1234567890',
                'status' => 'Kritis',
            ],
        ];

        $categoryMargins = [
            ['category' => 'Lifestyle', 'margin' => '34%', 'is_highlight' => true],
            ['category' => 'Running',   'margin' => '21%', 'is_highlight' => false],
            ['category' => 'Sports',    'margin' => '25%', 'is_highlight' => false],
            ['category' => 'Casual',    'margin' => '41%', 'is_highlight' => true],
        ];

        $slowMoving = [
            'name' => 'WaterJyordan',
            'detail' => 'Size 40 - 41 - 42 - 43 - 44 EUR - Merah - Hitam',
            'note' => 'Sisa <strong>66 - 1</strong> terjual bulan ini',
            'status' => 'Overstock',
        ];

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
