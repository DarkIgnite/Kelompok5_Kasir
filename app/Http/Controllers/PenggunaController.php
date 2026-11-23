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

        if ($request->filled('role')) {
            $roleFilter = $request->input('role');
            if (in_array(strtolower($roleFilter), ['owner', 'manager', 'kasir'])) {
                $query->where('jabatan', ucfirst($roleFilter));
            } else {
                $query->where('role', $roleFilter);
            }
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $validated = $request->validate([
            'nama_user' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:4'],
            'jabatan' => ['required', 'string', 'in:Owner,Manager,Kasir'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
        ], [
            'nama_user.required' => 'Nama user wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        // Cegah privilege escalation: hanya Owner yang boleh membuat akun Owner
        if (! $currentUser->canAssignJabatan($validated['jabatan'])) {
            return redirect()->route('pengguna.index')
                ->with('error', 'Anda tidak berhak membuat user dengan jabatan ' . $validated['jabatan'] . '.');
        }

        // Map jabatan to role auth
        $role = strtolower($validated['jabatan']) === 'kasir' ? 'kasir' : 'admin';

        // Generate username unik dari nama_user
        $baseUsername = Str::slug($validated['nama_user'], '');
        if (empty($baseUsername)) {
            $baseUsername = 'user';
        }
        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        User::create([
            'nama_lengkap' => $validated['nama_user'],
            'username' => $username,
            // Model User memakai cast 'password' => 'hashed', jadi kirim plain text
            // (Hash::make di sini membuat kode bergantung pada pengecekan anti double-hash).
            'password' => $validated['password'],
            'role' => $role,
            'jabatan' => $validated['jabatan'],
            'status' => $validated['status'],
            'no_telepon' => $validated['no_telepon'] ?? null,
        ]);

        return redirect()->route('pengguna.index')->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $pengguna)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Hierarki role: Admin/Manager tidak boleh mengedit Owner atau Manager lain
        if (! $currentUser->canEditUser($pengguna)) {
            abort(403, 'Anda tidak berhak mengedit user ini.');
        }

        $validated = $request->validate([
            'nama_user' => ['required', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:4'],
            'jabatan' => ['required', 'string', 'in:Owner,Manager,Kasir'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
        ], [
            'nama_user.required' => 'Nama user wajib diisi.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        $newJabatan = $validated['jabatan'];

        // Cegah privilege escalation (mis. Manager menjadikan dirinya / orang lain Owner).
        // Jabatan yang tidak berubah tetap boleh disimpan apa adanya.
        $jabatanBerubah = strtolower($newJabatan) !== strtolower($pengguna->jabatan);
        if ($jabatanBerubah && ! $currentUser->canAssignJabatan($newJabatan)) {
            return redirect()->route('pengguna.index')
                ->with('error', 'Anda tidak berhak mengubah jabatan menjadi ' . $newJabatan . '.');
        }

        // Owner tidak boleh menurunkan jabatannya sendiri jika ia Owner terakhir
        if ($jabatanBerubah && (int) $pengguna->id_pengguna === (int) $currentUser->id_pengguna
            && $currentUser->isOwner() && strtolower($newJabatan) !== 'owner') {
            return redirect()->route('pengguna.index')
                ->with('error', 'Owner tidak dapat menurunkan jabatannya sendiri.');
        }

        $role = strtolower($newJabatan) === 'kasir' ? 'kasir' : 'admin';

        $updateData = [
            'nama_lengkap' => $validated['nama_user'],
            'role' => $role,
            'jabatan' => $newJabatan,
            'status' => $validated['status'],
            'no_telepon' => $validated['no_telepon'] ?? null,
        ];

        // Password hanya diganti jika diisi. Tidak di-Hash::make manual karena
        // model sudah memakai cast 'hashed' (menghindari double-hash).
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

        // Hierarki role: Admin/Manager tidak boleh menghapus Owner atau Manager lain
        if (! $currentUser->canDeleteUser($pengguna)) {
            abort(403, 'Anda tidak berhak menghapus user ini.');
        }

        // tbl_transaksi.id_pengguna memakai cascadeOnDelete: menghapus user yang sudah
        // pernah bertransaksi akan ikut MENGHAPUS riwayat penjualannya.
        if ($pengguna->transaksi()->exists()) {
            return redirect()->route('pengguna.index')
                ->with('error', 'User ini sudah memiliki riwayat transaksi. Ubah statusnya menjadi Nonaktif agar riwayat penjualan tidak ikut terhapus.');
        }

        $pengguna->delete();

        return redirect()->route('pengguna.index')->with('success', 'User berhasil dihapus.');
    }
}
