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
        'jabatan',
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
     * Jabatan Accessor dengan fallback cerdas.
     */
    public function getJabatanAttribute($value): string
    {
        if (!empty($value)) {
            return ucfirst($value);
        }
        if ($this->id_pengguna == 1 || $this->username === 'admin') {
            return 'Owner';
        }
        return $this->role === 'admin' ? 'Manager' : 'Kasir';
    }

    /**
     * Hierarki level jabatan:
     * Level 3: Owner (Tertinggi)
     * Level 2: Manager
     * Level 1: Kasir (Bawahan)
     */
    public function getJabatanRank(): int
    {
        $j = strtolower($this->jabatan ?? '');
        if ($j === 'owner' || $this->id_pengguna == 1) {
            return 3;
        }
        if ($j === 'manager' || $this->role === 'admin') {
            return 2;
        }
        return 1; // Kasir
    }

    /**
     * Cek apakah user adalah Owner.
     */
    public function isOwner(): bool
    {
        return $this->getJabatanRank() === 3;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->isOwner();
    }

    /**
     * Check if user is kasir.
     */
    public function isKasir(): bool
    {
        return $this->role === 'kasir' && !$this->isOwner();
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    /**
     * Cek apakah user saat ini dapat mengedit target user.
     */
    public function canEditUser(User $target): bool
    {
        return true;
    }

    /**
     * Cek apakah user saat ini dapat menghapus target user.
     */
    public function canDeleteUser(User $target): bool
    {
        // Hanya cegah menghapus akun sendiri agar tidak terkunci keluar
        return $this->id_pengguna !== $target->id_pengguna;
    }

    /**
     * Transaksi yang diproses oleh pengguna / kasir ini.
     */
    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'id_pengguna', 'id_pengguna');
    }
}
