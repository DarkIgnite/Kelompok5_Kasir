<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Kategori::withCount('produk');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('nama_kategori', 'like', "%{$search}%");
        }

        $limit = min(max((int) $request->input('limit', 100), 10), 100); // dibatasi 10–100 baris per halaman
        $kategori = $query->orderBy('id_kategori', 'asc')->paginate($limit)->withQueryString();

        return view('kategori.index', compact('kategori'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:50', Rule::unique('tbl_kategori', 'nama_kategori')],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.max' => 'Nama kategori maksimal 50 karakter.',
            'nama_kategori.unique' => 'Nama kategori sudah dipakai.',
        ]);

        Kategori::create($validated);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kategori $kategori)
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:50', Rule::unique('tbl_kategori', 'nama_kategori')->ignore($kategori->id_kategori, 'id_kategori')],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.max' => 'Nama kategori maksimal 50 karakter.',
            'nama_kategori.unique' => 'Nama kategori sudah dipakai.',
        ]);

        $kategori->update($validated);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kategori $kategori)
    {
        // Menghapus kategori ikut menghapus semua produk, varian, dan riwayat penjualannya (cascade)
        $jumlahProduk = $kategori->produk()->count();
        if ($jumlahProduk > 0) {
            return redirect()->route('kategori.index')->with('error',
                "Kategori \"{$kategori->nama_kategori}\" masih dipakai {$jumlahProduk} produk. Pindahkan produknya ke kategori lain dulu.");
        }

        $kategori->delete();

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
