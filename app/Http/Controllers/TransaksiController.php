<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index()
    {
        return redirect()->route('kasir.index');
    }

    public function show($id)
    {
        return redirect()->route('kasir.show', $id);
    }
}
