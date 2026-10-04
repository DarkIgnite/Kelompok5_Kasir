<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produk = Produk::with(['kategori', 'varian'])->latest('id_produk')->paginate(15);

        return view('produk.index', compact('produk'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Produk $produk)
    {
        $produk->load(['kategori', 'varian']);

        return view('produk.show', compact('produk'));
    }
}
