<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'tbl_produk';
    protected $primaryKey = 'id_produk';

    protected $fillable = [
        'id_kategori',
        'kode_produk',
        'nama_produk',
        'merek',
        'deskripsi',
        'harga_beli',
        'harga_jual',
        'gambar',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'harga_beli' => 'decimal:2',
            'harga_jual' => 'decimal:2',
        ];
    }

    /**
     * Gambar produk bisa berupa file lokal (images/shoes/...) atau URL lengkap (https://...).
     */
    public function getGambarIsRemoteAttribute(): bool
    {
        return (bool) preg_match('#^https?://#i', (string) $this->gambar);
    }

    public function getHasGambarAttribute(): bool
    {
        if (empty($this->gambar)) {
            return false;
        }

        return $this->gambar_is_remote || file_exists(public_path($this->gambar));
    }

    /**
     * URL gambar yang siap dipakai di <img src>, dengan gambar cadangan jika kosong/hilang.
     */
    public function getGambarUrlAttribute(): string
    {
        if ($this->gambar_is_remote) {
            return $this->gambar;
        }

        return asset($this->has_gambar ? $this->gambar : 'images/shoes/placeholder.svg');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    public function varian(): HasMany
    {
        return $this->hasMany(VarianProduk::class, 'id_produk', 'id_produk');
    }
}
