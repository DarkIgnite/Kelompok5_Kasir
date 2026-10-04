<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transaksi = Transaksi::with(['pengguna', 'detail.varian.produk'])
            ->latest('tanggal_transaksi')
            ->paginate(15);

        return view('transaksi.index', compact('transaksi'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaksi $transaksi)
    {
        $transaksi->load(['pengguna', 'detail.varian.produk']);

        return view('transaksi.show', compact('transaksi'));
    }
}
