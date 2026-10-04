<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VarianProduk extends Model
{
    use HasFactory;

    protected $table = 'tbl_varian_produk';
    protected $primaryKey = 'id_varian';

    protected $fillable = [
        'id_produk',
        'sku',
        'ukuran',
        'warna',
        'stok',
        'stok_minimum',
    ];

    protected function casts(): array
    {
        return [
            'stok' => 'integer',
            'stok_minimum' => 'integer',
        ];
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'id_varian', 'id_varian');
    }

    public function isLowStock(): bool
    {
        return $this->stok <= $this->stok_minimum;
    }
}
