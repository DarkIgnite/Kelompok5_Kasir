<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat penjualan tidak boleh ikut terhapus.
 * Sebelumnya: menghapus kasir / varian otomatis menghapus nota-notanya (cascade).
 * Sekarang database menolak penghapusan itu (restrict). Detail nota tetap ikut terhapus
 * jika notanya sendiri dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_transaksi', function (Blueprint $table) {
            $table->dropForeign(['id_pengguna']);
            $table->foreign('id_pengguna')->references('id_pengguna')->on('tbl_pengguna')->restrictOnDelete();
        });

        Schema::table('tbl_detail_transaksi', function (Blueprint $table) {
            $table->dropForeign(['id_varian']);
            $table->foreign('id_varian')->references('id_varian')->on('tbl_varian_produk')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tbl_transaksi', function (Blueprint $table) {
            $table->dropForeign(['id_pengguna']);
            $table->foreign('id_pengguna')->references('id_pengguna')->on('tbl_pengguna')->cascadeOnDelete();
        });

        Schema::table('tbl_detail_transaksi', function (Blueprint $table) {
            $table->dropForeign(['id_varian']);
            $table->foreign('id_varian')->references('id_varian')->on('tbl_varian_produk')->cascadeOnDelete();
        });
    }
};
