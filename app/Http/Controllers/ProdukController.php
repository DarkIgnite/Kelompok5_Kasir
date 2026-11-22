<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\VarianProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Produk::with(['kategori', 'varian']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('kode_produk', 'like', "%{$search}%")
                  ->orWhere('merek', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('id_kategori', $request->input('kategori'));
        }

        if ($request->filled('status') && in_array($request->input('status'), ['aktif', 'nonaktif'])) {
            $query->where('status', $request->input('status'));
        }

        $limit = $request->input('limit', 100);
        $produk = $query->orderBy('id_produk', 'asc')->paginate($limit)->withQueryString();
        $kategoriList = Kategori::all();

        return view('produk.index', compact('produk', 'kategoriList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategori = Kategori::all();
        return view('produk.create', compact('kategori'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Bersihkan formatting Rupiah pada input harga
        $hargaBeliRaw = preg_replace('/[^0-9]/', '', (string)$request->input('harga_beli'));
        $hargaJualRaw = preg_replace('/[^0-9]/', '', (string)$request->input('harga_jual'));

        $request->merge([
            'harga_beli' => $hargaBeliRaw ?: 0,
            'harga_jual' => $hargaJualRaw ?: 0,
        ]);

        $validated = $request->validate([
            'nama_produk' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'merek' => ['required', 'string', 'max:50'],
            'id_kategori' => ['required', 'exists:tbl_kategori,id_kategori'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'status.required' => 'Status produk wajib dipilih.',
            'merek.required' => 'Merek produk wajib diisi.',
            'id_kategori.required' => 'Kategori produk wajib dipilih.',
            'harga_beli.required' => 'Harga beli wajib diisi.',
            'harga_jual.required' => 'Harga jual wajib diisi.',
            'foto.image' => 'File harus berupa gambar (JPG, JPEG, PNG, WEBP).',
            'foto.max' => 'Ukuran file foto maksimal 2 MB.',
        ]);

        // Generate kode_produk otomatis
        $nextId = (Produk::max('id_produk') ?? 0) + 1;
        $kodeProduk = 'kodeprdk' . sprintf('%02d', $nextId);

        // Upload foto jika ada
        $gambarPath = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $fileName = time() . '_' . Str::slug($request->nama_produk) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/shoes'), $fileName);
            $gambarPath = 'images/shoes/' . $fileName;
        }

        Produk::create([
            'id_kategori' => $validated['id_kategori'],
            'kode_produk' => $kodeProduk,
            'nama_produk' => $validated['nama_produk'],
            'merek' => $validated['merek'],
            'deskripsi' => $request->input('deskripsi', ''),
            'harga_beli' => $validated['harga_beli'],
            'harga_jual' => $validated['harga_jual'],
            'gambar' => $gambarPath,
            'status' => $validated['status'],
        ]);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Produk $produk)
    {
        $produk->load(['kategori', 'varian']);

        return view('produk.show', compact('produk'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produk $produk)
    {
        $kategori = Kategori::all();
        $produk->load(['kategori', 'varian']);

        return view('produk.edit', compact('produk', 'kategori'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produk $produk)
    {
        // Bersihkan formatting Rupiah pada input harga
        $hargaBeliRaw = preg_replace('/[^0-9]/', '', (string)$request->input('harga_beli'));
        $hargaJualRaw = preg_replace('/[^0-9]/', '', (string)$request->input('harga_jual'));

        $request->merge([
            'harga_beli' => $hargaBeliRaw ?: $produk->harga_beli,
            'harga_jual' => $hargaJualRaw ?: $produk->harga_jual,
        ]);

        $validated = $request->validate([
            'nama_produk' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'merek' => ['required', 'string', 'max:50'],
            'id_kategori' => ['required', 'exists:tbl_kategori,id_kategori'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'status.required' => 'Status produk wajib dipilih.',
            'merek.required' => 'Merek produk wajib diisi.',
            'id_kategori.required' => 'Kategori produk wajib dipilih.',
            'harga_beli.required' => 'Harga beli wajib diisi.',
            'harga_jual.required' => 'Harga jual wajib diisi.',
            'foto.image' => 'File harus berupa gambar (JPG, JPEG, PNG, WEBP).',
            'foto.max' => 'Ukuran file foto maksimal 2 MB.',
        ]);

        $updateData = [
            'id_kategori' => $validated['id_kategori'],
            'nama_produk' => $validated['nama_produk'],
            'merek' => $validated['merek'],
            'harga_beli' => $validated['harga_beli'],
            'harga_jual' => $validated['harga_jual'],
            'status' => $validated['status'],
        ];

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $fileName = time() . '_' . Str::slug($request->nama_produk) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/shoes'), $fileName);
            $updateData['gambar'] = 'images/shoes/' . $fileName;
        } elseif ($request->input('hapus_foto') == '1') {
            $updateData['gambar'] = null;
        }

        $produk->update($updateData);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produk $produk)
    {
        $produk->delete();

        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Display a listing of Varian Produk.
     */
    public function varianIndex(Request $request)
    {
        $query = VarianProduk::with('produk');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhereHas('produk', function ($pq) use ($search) {
                      $pq->where('nama_produk', 'like', "%{$search}%");
                  });
            });
        }

        $limit = $request->input('limit', 100);
        $varian = $query->orderBy('id_varian', 'asc')->paginate($limit)->withQueryString();
        $produkList = Produk::all();

        return view('produk.varian', compact('varian', 'produkList'));
    }

    /**
     * Store a newly created Varian Produk.
     */
    public function varianStore(Request $request)
    {
        $validated = $request->validate([
            'id_produk' => ['required', 'exists:tbl_produk,id_produk'],
            'ukuran' => ['required', 'string', 'max:10'],
            'warna' => ['required', 'string', 'max:30'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'stok' => ['nullable', 'integer', 'min:0'],
        ]);

        $produk = Produk::findOrFail($validated['id_produk']);
        $warnaKode = strtoupper(substr($validated['warna'], 0, 3));
        $sku = $produk->kode_produk . '-' . $validated['ukuran'] . '-' . $warnaKode;

        VarianProduk::create([
            'id_produk' => $validated['id_produk'],
            'sku' => $sku,
            'ukuran' => $validated['ukuran'],
            'warna' => $validated['warna'],
            'stok' => $validated['stok'] ?? 0,
            'stok_minimum' => $validated['stok_minimum'],
        ]);

        return redirect()->route('varian.index')->with('success', 'Varian produk berhasil ditambahkan.');
    }

    /**
     * Update the specified Varian Produk.
     */
    public function varianUpdate(Request $request, VarianProduk $varian)
    {
        $validated = $request->validate([
            'id_produk' => ['required', 'exists:tbl_produk,id_produk'],
            'ukuran' => ['required', 'string', 'max:10'],
            'warna' => ['required', 'string', 'max:30'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'stok' => ['nullable', 'integer', 'min:0'],
        ]);

        $varian->update($validated);

        return redirect()->route('varian.index')->with('success', 'Varian produk berhasil diperbarui.');
    }

    /**
     * Remove the specified Varian Produk.
     */
    public function varianDestroy(VarianProduk $varian)
    {
        $varian->delete();

        return redirect()->route('varian.index')->with('success', 'Varian produk berhasil dihapus.');
    }
}
