<?php

namespace Database\Seeders;

use App\Models\DetailTransaksi;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\VarianProduk;
use Illuminate\Database\Seeder;

/**
 * Data dummy produk sepatu (model nyata) untuk halaman Kasir & Katalog.
 *
 * Foto diambil dari Unsplash (https://unsplash.com/license — gratis dipakai,
 * hotlink ke images.unsplash.com memang diizinkan). Karena gambarnya dimuat
 * dari internet, foto hanya tampil saat komputer terhubung ke internet.
 *
 * Seeder ini juga:
 *  - menghapus 4 produk demo lama (WaterJyordan, FayerJyordan, ErthJyordan, WhinJyordan)
 *    beserta transaksi yang berisi produk tersebut, dan
 *  - membuat transaksi demo 12 bulan terakhir dari produk baru (untuk grafik dashboard).
 *
 * Jalankan: php artisan db:seed --class=ProdukSepatuSeeder
 * Aman dijalankan berulang (updateOrCreate).
 */
class ProdukSepatuSeeder extends Seeder
{
    private const IMG = 'https://images.unsplash.com/photo-%s?auto=format&fit=max&w=600&q=75';

    /** Kode produk demo lama yang diganti dengan produk sepatu nyata. */
    private const PRODUK_LAMA = ['kodeprdk01', 'kodeprdk02', 'kodeprdk03', 'kodeprdk04'];

    public function run(): void
    {
        $this->hapusProdukLama();

        $kategori = [];
        foreach ([
            'Lifestyle' => 'Sepatu kasual harian gaya modern',
            'Running'   => 'Sepatu lari ringan & fleksibel',
            'Sports'    => 'Sepatu olahraga basket & futsal',
            'Casual'    => 'Sepatu santai slip-on & loafers',
        ] as $nama => $ket) {
            $kategori[$nama] = Kategori::firstOrCreate(['nama_kategori' => $nama], ['keterangan' => $ket])->id_kategori;
        }

        $produk = [
            // [kode, nama, merek, kategori, harga beli, harga jual, warna, kode warna, id foto Unsplash, fotografer, deskripsi]
            ['SPT-NK-AF1-BLK', "Nike Air Force 1 '07", 'Nike', 'Lifestyle', 1200000, 1729000, 'Hitam', 'BLK',
                '1576188973526-0e5d7047b0cf', 'Tamas Pap', 'Sneaker basket klasik dengan upper kulit full-grain dan sol Air.'],
            ['SPT-NK-AF1-CRH', 'Nike Air Force 1 x Carhartt WIP', 'Nike', 'Lifestyle', 2100000, 2999000, 'Cokelat', 'BRN',
                '1549298916-f52d724204b4', 'Maksim Larin', 'Kolaborasi Nike dan Carhartt WIP dengan material kanvas tahan lama.'],
            ['SPT-JD-AJ1-CHI', 'Air Jordan 1 Retro High OG', 'Jordan', 'Sports', 2400000, 3249000, 'Putih/Merah/Hitam', 'CHI',
                '1559478293-ed32557862a5', 'Junior Samson', 'Ikon sneaker basket high-top dengan colorway Chicago.'],
            ['SPT-JD-AJ1-UNC', 'Air Jordan 1 Retro High OG Obsidian UNC', 'Jordan', 'Sports', 2400000, 3249000, 'Biru/Putih', 'UNC',
                '1586525198428-225f6f12cff5', 'Branislav Belko', 'Air Jordan 1 high-top dengan kombinasi biru obsidian dan biru muda.'],
            ['SPT-CV-CT-HI', 'Converse Chuck Taylor All Star Hi', 'Converse', 'Casual', 550000, 799000, 'Hitam', 'BLK',
                '1463100099107-aa0980c362e6', 'Camila Damásio', 'Sepatu kanvas high-top legendaris untuk gaya santai sehari-hari.'],
            ['SPT-NB-530-WHT', 'New Balance 530', 'New Balance', 'Running', 1100000, 1599000, 'Putih/Abu', 'WHT',
                '1542272604-78d13c1f741a', 'Mnz', 'Sneaker bergaya running era 2000-an dengan bantalan ABZORB.'],
            ['SPT-VN-AUTH-BW', 'Vans Authentic', 'Vans', 'Casual', 520000, 799000, 'Hitam/Putih', 'BW',
                '1608454883429-cb31c212d0da', 'Klim Musalimov', 'Sepatu kanvas low-top klasik dengan sol waffle khas Vans.'],
            ['SPT-AD-SST-WHT', 'Adidas Superstar', 'Adidas', 'Lifestyle', 1050000, 1500000, 'Putih/Hitam', 'WHT',
                '1535944575480-975564904e3a', 'Noémi Macavei-Katócz', 'Sneaker shell-toe ikonik dengan tiga garis Adidas.'],
            ['SPT-NK-PEG-WHT', 'Nike Air Zoom Pegasus 40', 'Nike', 'Running', 1350000, 1909000, 'Putih', 'WHT',
                '1615743472612-93b21e520fad', 'Solen Feyissa', 'Sepatu lari harian yang responsif dengan unit Zoom Air.'],
        ];

        // Stok per ukuran dibuat bervariasi (ada yang aman, kritis, dan habis) agar dashboard terlihat realistis
        $polaStok = [
            [12, 18, 25, 15, 8], [4, 9, 2, 6, 0], [7, 3, 11, 5, 2], [14, 20, 9, 6, 3], [22, 30, 18, 12, 6],
            [6, 10, 15, 4, 1], [16, 24, 19, 10, 5], [9, 0, 12, 7, 4], [11, 15, 8, 3, 2],
        ];
        $ukuran = ['39', '40', '41', '42', '43'];

        foreach ($produk as $i => [$kode, $nama, $merek, $kat, $beli, $jual, $warna, $kodeWarna, $foto, $fotografer, $deskripsi]) {
            $p = Produk::updateOrCreate(
                ['kode_produk' => $kode],
                [
                    'id_kategori' => $kategori[$kat],
                    'nama_produk' => $nama,
                    'merek' => $merek,
                    'deskripsi' => $deskripsi . " Foto: {$fotografer} / Unsplash.",
                    'harga_beli' => $beli,
                    'harga_jual' => $jual,
                    'gambar' => sprintf(self::IMG, $foto),
                    'status' => 'aktif',
                ]
            );

            foreach ($ukuran as $u => $size) {
                VarianProduk::updateOrCreate(
                    ['sku' => "{$kode}-{$size}"],
                    [
                        'id_produk' => $p->id_produk,
                        'ukuran' => $size,
                        'warna' => $warna,
                        'stok' => $polaStok[$i][$u],
                        'stok_minimum' => 3,
                    ]
                );
            }
        }

        $this->buatTransaksiDemo();
    }

    /**
     * Hapus produk demo lama. Transaksi yang berisi produk tersebut ikut dihapus,
     * karena detail transaksinya akan hilang (cascade) dan menyisakan nota kosong.
     */
    private function hapusProdukLama(): void
    {
        $produkLama = Produk::whereIn('kode_produk', self::PRODUK_LAMA)->pluck('id_produk');
        if ($produkLama->isEmpty()) {
            return;
        }

        $varianLama = VarianProduk::whereIn('id_produk', $produkLama)->pluck('id_varian');
        $transaksiLama = DetailTransaksi::whereIn('id_varian', $varianLama)->distinct()->pluck('id_transaksi');

        $jmlTransaksi = Transaksi::whereIn('id_transaksi', $transaksiLama)->delete(); // detail ikut terhapus (cascade)
        $jmlProduk = Produk::whereIn('id_produk', $produkLama)->delete();             // varian ikut terhapus (cascade)

        $this->command?->info("Produk lama dihapus: {$jmlProduk} produk, {$jmlTransaksi} transaksi terkait.");
    }

    /**
     * Satu sampai beberapa transaksi per bulan selama 12 bulan terakhir dari produk baru,
     * supaya grafik Tren Penjualan, KPI, dan Kategori Terlaris berisi data nyata.
     */
    private function buatTransaksiDemo(): void
    {
        $kasir = User::where('role', 'kasir')->orderBy('id_pengguna')->first() ?? User::first();
        if (! $kasir) {
            return;
        }

        // [bulan lalu ke-, sku, jumlah]
        $penjualan = [
            [11, 'SPT-CV-CT-HI-41', 2], [10, 'SPT-NK-AF1-BLK-42', 1], [10, 'SPT-VN-AUTH-BW-40', 2],
            [9, 'SPT-AD-SST-WHT-41', 2], [8, 'SPT-NB-530-WHT-42', 1], [8, 'SPT-CV-CT-HI-40', 1],
            [7, 'SPT-NK-PEG-WHT-42', 2], [6, 'SPT-JD-AJ1-CHI-42', 1], [5, 'SPT-NK-AF1-BLK-41', 2],
            [5, 'SPT-VN-AUTH-BW-41', 1], [4, 'SPT-AD-SST-WHT-40', 1], [3, 'SPT-NB-530-WHT-41', 2],
            [3, 'SPT-JD-AJ1-UNC-43', 1], [2, 'SPT-NK-AF1-CRH-42', 1], [2, 'SPT-CV-CT-HI-42', 3],
            [1, 'SPT-NK-AF1-BLK-40', 2], [1, 'SPT-NK-PEG-WHT-41', 1], [0, 'SPT-JD-AJ1-CHI-41', 1],
            [0, 'SPT-AD-SST-WHT-42', 2], [0, 'SPT-VN-AUTH-BW-42', 1],
        ];
        $metode = ['cash', 'qris', 'debit'];

        foreach ($penjualan as $n => [$bulanLalu, $sku, $qty]) {
            $varian = VarianProduk::with('produk')->where('sku', $sku)->first();
            if (! $varian) {
                continue;
            }

            $tanggal = now()->startOfMonth()->subMonthsNoOverflow($bulanLalu)
                ->addDays(($n * 3) % 25)->setTime(10 + $n % 8, 15);
            if ($tanggal->isFuture()) {
                $tanggal = now()->subHours($n + 1);
            }

            $harga = (float) $varian->produk->harga_jual;
            $total = $harga * $qty;

            $trx = Transaksi::updateOrCreate(
                ['no_nota' => 'TRX-' . $tanggal->format('Ym') . '-D' . str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT)],
                [
                    'id_pengguna'       => $kasir->id_pengguna,
                    'tanggal_transaksi' => $tanggal,
                    'total_item'        => $qty,
                    'subtotal'          => $total,
                    'diskon'            => 0,
                    'total_bayar'       => $total,
                    'jumlah_bayar'      => $total,
                    'kembalian'         => 0,
                    'metode_pembayaran' => $metode[$n % 3],
                    'status'            => 'selesai',
                ]
            );

            DetailTransaksi::updateOrCreate(
                ['id_transaksi' => $trx->id_transaksi, 'id_varian' => $varian->id_varian],
                ['jumlah' => $qty, 'harga_satuan' => $harga, 'subtotal' => $total]
            );
        }
    }
}
