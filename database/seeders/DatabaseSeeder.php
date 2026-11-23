<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\User;
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

        // 3. Produk, varian & transaksi demo: sepatu nyata (foto Unsplash).
        //    Produk demo lama (WaterJyordan, FayerJyordan, ErthJyordan, WhinJyordan) sudah tidak dipakai.
        $this->call(ProdukSepatuSeeder::class);
    }
}
