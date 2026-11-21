<?php

namespace Database\Seeders;

use App\Models\DetailTransaksi;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\VarianProduk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Pengguna (Admin & Kasir)
        $admin = User::updateOrCreate(
            ['username' => 'AdminKasir123'],
            [
                'nama_lengkap' => 'Administrator Toko',
                'password' => Hash::make('admin'),
                'role' => 'admin',
                'no_telepon' => '081234567890',
                'status' => 'aktif',
            ]
        );

        $kasir1 = User::updateOrCreate(
            ['username' => 'kasir1'],
            [
                'nama_lengkap' => 'Kasir 1 (Shift Pagi)',
                'password' => Hash::make('kasir'),
                'role' => 'kasir',
                'no_telepon' => '081234567891',
                'status' => 'aktif',
            ]
        );

        $kasir2 = User::updateOrCreate(
            ['username' => 'kasir2'],
            [
                'nama_lengkap' => 'Kasir 2 (Shift Siang)',
                'password' => Hash::make('kasir'),
                'role' => 'kasir',
                'no_telepon' => '081234567892',
                'status' => 'aktif',
            ]
        );

        // 2. Kategori Sepatu
        $katLifestyle = Kategori::updateOrCreate(
            ['nama_kategori' => 'Lifestyle'],
            ['keterangan' => 'Sepatu kasual harian gaya modern']
        );

        $katRunning = Kategori::updateOrCreate(
            ['nama_kategori' => 'Running'],
            ['keterangan' => 'Sepatu lari ringan & fleksibel']
        );

        $katSports = Kategori::updateOrCreate(
            ['nama_kategori' => 'Sports'],
            ['keterangan' => 'Sepatu olahraga basket & futsal']
        );

        $katCasual = Kategori::updateOrCreate(
            ['nama_kategori' => 'Casual'],
            ['keterangan' => 'Sepatu santai slip-on & loafers']
        );

        // 3. Produk Sepatu
        $spt1 = Produk::updateOrCreate(
            ['kode_produk' => 'kodeprdk01'],
            [
                'id_kategori' => $katLifestyle->id_kategori,
                'nama_produk' => 'WaterJyordan',
                'merek' => 'Asuz',
                'deskripsi' => 'Sneakers premium bahan leather breathable dengan bantalan empuk.',
                'harga_beli' => 2000000,
                'harga_jual' => 3110000,
                'gambar' => 'images/shoes/water-jyordan.png',
                'status' => 'aktif',
            ]
        );

        $spt2 = Produk::updateOrCreate(
            ['kode_produk' => 'kodeprdk02'],
            [
                'id_kategori' => $katLifestyle->id_kategori,
                'nama_produk' => 'FayerJyordan',
                'merek' => 'Zusa',
                'deskripsi' => 'Sneakers edisi terbatas dengan warna hitam elegan.',
                'harga_beli' => 4500000,
                'harga_jual' => 6220000,
                'gambar' => 'images/shoes/fayer-jyordan.png',
                'status' => 'aktif',
            ]
        );

        $spt3 = Produk::updateOrCreate(
            ['kode_produk' => 'kodeprdk03'],
            [
                'id_kategori' => $katRunning->id_kategori,
                'nama_produk' => 'ErthJyordan',
                'merek' => 'Uzas',
                'deskripsi' => 'Sepatu running ringan sol responsif warna abu elegan.',
                'harga_beli' => 4500000,
                'harga_jual' => 6220000,
                'gambar' => 'images/shoes/erth-jyordan.png',
                'status' => 'aktif',
            ]
        );

        $spt4 = Produk::updateOrCreate(
            ['kode_produk' => 'kodeprdk04'],
            [
                'id_kategori' => $katSports->id_kategori,
                'nama_produk' => 'WhinJyordan',
                'merek' => 'Uzuz',
                'deskripsi' => 'Sepatu olahraga stabil dengan grip maksimal di lapangan.',
                'harga_beli' => 4500000,
                'harga_jual' => 6220000,
                'gambar' => 'images/shoes/whin-jyordan.png',
                'status' => 'aktif',
            ]
        );

        // 4. Varian Produk
        // WaterJyordan
        $var1 = VarianProduk::updateOrCreate(
            ['sku' => 'WTR-40-RED'],
            ['id_produk' => $spt1->id_produk, 'ukuran' => '40', 'warna' => 'Merah', 'stok' => 20, 'stok_minimum' => 5]
        );
        $var2 = VarianProduk::updateOrCreate(
            ['sku' => 'WTR-41-RED'],
            ['id_produk' => $spt1->id_produk, 'ukuran' => '41', 'warna' => 'Merah', 'stok' => 16, 'stok_minimum' => 5]
        );
        $var3 = VarianProduk::updateOrCreate(
            ['sku' => 'WTR-42-BLK'],
            ['id_produk' => $spt1->id_produk, 'ukuran' => '42', 'warna' => 'Hitam', 'stok' => 15, 'stok_minimum' => 5]
        );
        $var4 = VarianProduk::updateOrCreate(
            ['sku' => 'WTR-43-BLK'],
            ['id_produk' => $spt1->id_produk, 'ukuran' => '43', 'warna' => 'Hitam', 'stok' => 15, 'stok_minimum' => 5]
        );

        // FayerJyordan (habis)
        $var5 = VarianProduk::updateOrCreate(
            ['sku' => 'FYR-44-BLK'],
            ['id_produk' => $spt2->id_produk, 'ukuran' => '44', 'warna' => 'Hitam', 'stok' => 0, 'stok_minimum' => 10]
        );

        // ErthJyordan (kritis)
        $var6 = VarianProduk::updateOrCreate(
            ['sku' => 'ERTH-40-GRY'],
            ['id_produk' => $spt3->id_produk, 'ukuran' => '40', 'warna' => 'Abu', 'stok' => 4, 'stok_minimum' => 5]
        );

        // WhinJyordan (kritis)
        $var7 = VarianProduk::updateOrCreate(
            ['sku' => 'WHN-40-BLU'],
            ['id_produk' => $spt4->id_produk, 'ukuran' => '40', 'warna' => 'Biru', 'stok' => 5, 'stok_minimum' => 5]
        );
        $var8 = VarianProduk::updateOrCreate(
            ['sku' => 'WHN-41-WHT'],
            ['id_produk' => $spt4->id_produk, 'ukuran' => '41', 'warna' => 'Putih', 'stok' => 5, 'stok_minimum' => 5]
        );

        // 5. Transaksi Contoh (Didistribusikan ke 12 Bulan Terakhir)
        $sampleMonths = [
            11 => ['qty' => 4, 'total' => 12440000],
            10 => ['qty' => 3, 'total' => 9330000],
            9  => ['qty' => 2, 'total' => 6220000],
            8  => ['qty' => 3, 'total' => 9330000],
            7  => ['qty' => 3, 'total' => 9330000],
            6  => ['qty' => 2, 'total' => 6220000],
            5  => ['qty' => 3, 'total' => 9330000],
            4  => ['qty' => 3, 'total' => 9330000],
            3  => ['qty' => 3, 'total' => 9330000],
            2  => ['qty' => 3, 'total' => 9330000],
            1  => ['qty' => 5, 'total' => 15550000],
            0  => ['qty' => 5, 'total' => 15550000],
        ];

        foreach ($sampleMonths as $subMonth => $data) {
            $date = $subMonth === 0 ? now() : now()->subMonths($subMonth);
            $nota = 'TRX-' . $date->format('Ym') . '-001';

            $trx = Transaksi::updateOrCreate(
                ['no_nota' => $nota],
                [
                    'id_pengguna'       => $kasir1->id_pengguna,
                    'tanggal_transaksi' => $date,
                    'total_item'        => $data['qty'],
                    'subtotal'          => $data['total'],
                    'diskon'            => 0,
                    'total_bayar'       => $data['total'],
                    'jumlah_bayar'      => $data['total'],
                    'kembalian'         => 0,
                    'metode_pembayaran' => 'cash',
                    'status'            => 'selesai',
                ]
            );

            DetailTransaksi::updateOrCreate(
                ['id_transaksi' => $trx->id_transaksi, 'id_varian' => $var1->id_varian],
                [
                    'jumlah'       => $data['qty'],
                    'harga_satuan' => 3110000,
                    'subtotal'     => $data['total'],
                ]
            );
        }
    }
}
