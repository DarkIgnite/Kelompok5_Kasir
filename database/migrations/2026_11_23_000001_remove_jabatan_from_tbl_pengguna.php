<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kembali ke struktur tabel resmi: tbl_pengguna hanya punya kolom `role` (admin / kasir).
 * Kolom `jabatan` (Owner/Manager) tambahan frontend dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('tbl_pengguna', 'jabatan')) {
            Schema::table('tbl_pengguna', function (Blueprint $table) {
                $table->dropColumn('jabatan');
            });
        }
    }

    public function down(): void
    {
        // Tidak dikembalikan: jabatan bukan bagian dari struktur tabel.
    }
};
