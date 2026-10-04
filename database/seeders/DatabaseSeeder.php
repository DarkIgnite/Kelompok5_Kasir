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
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'nama_lengkap' => 'Administrator Toko',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'no_telepon' => '081234567890',
                'status' => 'aktif',
            ]
        );

        $kasir1 = User::firstOrCreate(
            ['username' => 'kasir1'],
            [
                'nama_lengkap' => 'Kasir 1 (Shift Pagi)',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'no_telepon' => '081234567891',
                'status' => 'aktif',
            ]
        );

        $kasir2 = User::firstOrCreate(
            ['username' => 'kasir2'],
            [
                'nama_lengkap' => 'Kasir 2 (Shift Siang)',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'no_telepon' => '081234567892',
                'status' => 'aktif',
            ]
        );

        // 2. Kategori Sepatu
        $katLifestyle = Kategori::firstOrCreate(
            ['nama_kategori' => 'Lifestyle'],
            ['keterangan' => 'Sepatu kasual harian gaya modern']
        );

        $katRunning = Kategori::firstOrCreate(
            ['nama_kategori' => 'Running'],
            ['keterangan' => 'Sepatu lari ringan & fleksibel']
        );

        $katSports = Kategori::firstOrCreate(
            ['nama_kategori' => 'Sports'],
            ['keterangan' => 'Sepatu olahraga basket & futsal']
        );

        $katCasual = Kategori::firstOrCreate(
            ['nama_kategori' => 'Casual'],
            ['keterangan' => 'Sepatu santai slip-on & loafers']
        );

        // 3. Produk Sepatu
        $spt1 = Produk::firstOrCreate(
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

        $spt2 = Produk::firstOrCreate(
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

        $spt3 = Produk::firstOrCreate(
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

        $spt4 = Produk::firstOrCreate(
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
        $var1 = VarianProduk::firstOrCreate(
            ['sku' => 'WTR-40-RED'],
            ['id_produk' => $spt1->id_produk, 'ukuran' => '40', 'warna' => 'Merah', 'stok' => 20, 'stok_minimum' => 5]
        );
        $var2 = VarianProduk::firstOrCreate(
            ['sku' => 'WTR-41-RED'],
            ['id_produk' => $spt1->id_produk, 'ukuran' => '41', 'warna' => 'Merah', 'stok' => 16, 'stok_minimum' => 5]
        );
        $var3 = VarianProduk::firstOrCreate(
            ['sku' => 'WTR-42-BLK'],
            ['id_produk' => $spt1->id_produk, 'ukuran' => '42', 'warna' => 'Hitam', 'stok' => 15, 'stok_minimum' => 5]
        );
        $var4 = VarianProduk::firstOrCreate(
            ['sku' => 'WTR-43-BLK'],
            ['id_produk' => $spt1->id_produk, 'ukuran' => '43', 'warna' => 'Hitam', 'stok' => 15, 'stok_minimum' => 5]
        );

        // FayerJyordan (habis)
        $var5 = VarianProduk::firstOrCreate(
            ['sku' => 'FYR-44-BLK'],
            ['id_produk' => $spt2->id_produk, 'ukuran' => '44', 'warna' => 'Hitam', 'stok' => 0, 'stok_minimum' => 10]
        );

        // ErthJyordan (kritis)
        $var6 = VarianProduk::firstOrCreate(
            ['sku' => 'ERTH-40-GRY'],
            ['id_produk' => $spt3->id_produk, 'ukuran' => '40', 'warna' => 'Abu', 'stok' => 4, 'stok_minimum' => 5]
        );

        // WhinJyordan (kritis)
        $var7 = VarianProduk::firstOrCreate(
            ['sku' => 'WHN-40-BLU'],
            ['id_produk' => $spt4->id_produk, 'ukuran' => '40', 'warna' => 'Biru', 'stok' => 5, 'stok_minimum' => 5]
        );
        $var8 = VarianProduk::firstOrCreate(
            ['sku' => 'WHN-41-WHT'],
            ['id_produk' => $spt4->id_produk, 'ukuran' => '41', 'warna' => 'Putih', 'stok' => 5, 'stok_minimum' => 5]
        );

        // 5. Transaksi Contoh
        $trx1 = Transaksi::firstOrCreate(
            ['no_nota' => 'TRX-20261001-001'],
            [
                'id_pengguna' => $kasir1->id_pengguna,
                'tanggal_transaksi' => now()->subDays(2),
                'total_item' => 2,
                'subtotal' => 6220000,
                'diskon' => 0,
                'total_bayar' => 6220000,
                'jumlah_bayar' => 6500000,
                'kembalian' => 280000,
                'metode_pembayaran' => 'cash',
                'status' => 'selesai',
            ]
        );

        DetailTransaksi::firstOrCreate(
            ['id_transaksi' => $trx1->id_transaksi, 'id_varian' => $var1->id_varian],
            [
                'jumlah' => 2,
                'harga_satuan' => 3110000,
                'subtotal' => 6220000,
            ]
        );
    }
}
