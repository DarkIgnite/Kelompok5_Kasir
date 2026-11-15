<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PenggunaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pengguna = User::latest('id_pengguna')->paginate(15);

        return view('pengguna.index', compact('pengguna'));
    }
}
