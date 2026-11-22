<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $limit = $request->input('limit', 100);

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
            'password' => Hash::make($validated['password']),
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
        $role = strtolower($newJabatan) === 'kasir' ? 'kasir' : 'admin';

        $updateData = [
            'nama_lengkap' => $validated['nama_user'],
            'role' => $role,
            'jabatan' => $newJabatan,
            'status' => $validated['status'],
            'no_telepon' => $validated['no_telepon'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
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
        if ($pengguna->id_pengguna === $currentUser->id_pengguna) {
            return redirect()->route('pengguna.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $pengguna->delete();

        return redirect()->route('pengguna.index')->with('success', 'User berhasil dihapus.');
    }
}
