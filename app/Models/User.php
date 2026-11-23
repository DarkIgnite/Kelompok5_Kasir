<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'tbl_pengguna';
    protected $primaryKey = 'id_pengguna';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_lengkap',
        'username',
        'password',
        'role',
        'no_telepon',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Name accessor for compatibility.
     */
    public function getNameAttribute(): string
    {
        return $this->nama_lengkap ?? '';
    }

    /**
     * Label role untuk ditampilkan di tabel: "Admin" / "Kasir".
     */
    public function getRoleLabelAttribute(): string
    {
        return $this->role === 'admin' ? 'Admin' : 'Kasir';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    /**
     * Halaman utama setelah login: admin ke dashboard, kasir ke halaman kasir.
     */
    public function homeRoute(): string
    {
        return $this->isAdmin() ? 'dashboard' : 'kasir.index';
    }

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    /**
     * Admin boleh mengedit akunnya sendiri dan semua akun kasir.
     * Admin tidak boleh mengedit admin lain (mencegah saling mengambil alih akun).
     */
    public function canEditUser(User $target): bool
    {
        if (! $this->isAdmin()) {
            return false;
        }

        return (int) $this->id_pengguna === (int) $target->id_pengguna || $target->isKasir();
    }

    /**
     * Admin hanya boleh menghapus/nonaktifkan akun kasir, dan tidak boleh menghapus dirinya sendiri.
     */
    public function canDeleteUser(User $target): bool
    {
        return $this->isAdmin()
            && (int) $this->id_pengguna !== (int) $target->id_pengguna
            && $target->isKasir();
    }

    /**
     * Transaksi yang diproses oleh pengguna / kasir ini.
     */
    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'id_pengguna', 'id_pengguna');
    }
}
