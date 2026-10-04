<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_varian_produk', function (Blueprint $table) {
            $table->id('id_varian');
            $table->foreignId('id_produk')->constrained('tbl_produk', 'id_produk')->cascadeOnDelete();
            $table->string('sku', 40)->unique();
            $table->string('ukuran', 10);
            $table->string('warna', 30);
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_varian_produk');
    }
};
