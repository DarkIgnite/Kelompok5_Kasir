<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PenggunaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('no_telepon', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && in_array($request->input('status'), ['aktif', 'nonaktif'])) {
            $query->where('status', $request->input('status'));
        }

        if (in_array($request->input('role'), ['admin', 'kasir'], true)) {
            $query->where('role', $request->input('role'));
        }

        $limit = min(max((int) $request->input('limit', 100), 10), 100); // dibatasi 10–100 baris per halaman

        // Syarat: User sekarang (current user) HARUS PALING ATAS di list
        $currentUserId = Auth::id() ?? 0;
        $pengguna = $query->orderByRaw("CASE WHEN id_pengguna = ? THEN 0 ELSE 1 END, id_pengguna ASC", [$currentUserId])
                          ->paginate($limit)
                          ->withQueryString();

        return view('pengguna.index', compact('pengguna'));
    }

    /**
     * Tambah akun pengguna (DFD 4.4). Role mengikuti tbl_pengguna: admin / kasir.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'password'     => ['required', 'string', 'min:4'],
            'role'         => ['required', 'in:admin,kasir'],
            'status'       => ['required', 'in:aktif,nonaktif'],
            'no_telepon'   => ['nullable', 'string', 'max:20'],
        ], [
            'nama_lengkap.required' => 'Nama user wajib diisi.',
            'password.required'     => 'Password wajib diisi.',
            'role.required'         => 'Jabatan wajib dipilih.',
            'role.in'               => 'Jabatan harus Admin atau Kasir.',
            'status.required'       => 'Status wajib dipilih.',
        ]);

        // Username dibuat otomatis dari nama, dijamin unik
        $baseUsername = Str::slug($validated['nama_lengkap'], '') ?: 'user';
        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter++;
        }

        User::create([
            'nama_lengkap' => $validated['nama_lengkap'],
            'username'     => $username,
            'password'     => $validated['password'], // di-hash otomatis oleh cast 'hashed' di model
            'role'         => $validated['role'],
            'status'       => $validated['status'],
            'no_telepon'   => $validated['no_telepon'] ?? null,
        ]);

        return redirect()->route('pengguna.index')->with('success', "User berhasil ditambahkan dengan username: {$username}");
    }

    /**
     * Edit data pengguna (DFD 4.5).
     */
    public function update(Request $request, User $pengguna)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->canEditUser($pengguna)) {
            abort(403, 'Admin tidak dapat mengedit akun admin lain.');
        }

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'password'     => ['nullable', 'string', 'min:4'],
            'role'         => ['required', 'in:admin,kasir'],
            'status'       => ['required', 'in:aktif,nonaktif'],
            'no_telepon'   => ['nullable', 'string', 'max:20'],
        ], [
            'nama_lengkap.required' => 'Nama user wajib diisi.',
            'role.required'         => 'Jabatan wajib dipilih.',
            'role.in'               => 'Jabatan harus Admin atau Kasir.',
            'status.required'       => 'Status wajib dipilih.',
        ]);

        // Admin tidak boleh menurunkan / menonaktifkan akunnya sendiri (bisa terkunci keluar)
        $isSelf = (int) $pengguna->id_pengguna === (int) $currentUser->id_pengguna;
        if ($isSelf && ($validated['role'] !== 'admin' || $validated['status'] !== 'aktif')) {
            return redirect()->route('pengguna.index')
                ->with('error', 'Anda tidak dapat mengubah jabatan atau menonaktifkan akun Anda sendiri.');
        }

        $updateData = [
            'nama_lengkap' => $validated['nama_lengkap'],
            'role'         => $validated['role'],
            'status'       => $validated['status'],
            'no_telepon'   => $validated['no_telepon'] ?? null,
        ];

        // Password hanya diganti jika diisi (di-hash otomatis oleh cast model)
        if ($request->filled('password')) {
            $updateData['password'] = $validated['password'];
        }

        $pengguna->update($updateData);

        return redirect()->route('pengguna.index')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $pengguna)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Syarat: Tidak bisa hapus diri sendiri
        if ((int) $pengguna->id_pengguna === (int) $currentUser->id_pengguna) {
            return redirect()->route('pengguna.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if (! $currentUser->canDeleteUser($pengguna)) {
            abort(403, 'Admin tidak dapat menghapus akun admin lain.');
        }

        // Kasir yang sudah pernah bertransaksi tidak dihapus agar riwayat nota tetap utuh (DFD 4.6: nonaktifkan)
        if ($pengguna->transaksi()->exists()) {
            return redirect()->route('pengguna.index')
                ->with('error', 'User ini sudah memiliki riwayat transaksi. Ubah statusnya menjadi Nonaktif agar riwayat penjualan tidak ikut terhapus.');
        }

        $pengguna->delete();

        return redirect()->route('pengguna.index')->with('success', 'User berhasil dihapus.');
    }
}
