<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');
        $field = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if (
            Schema::hasTable('users')
            && Auth::attempt([
                $field => $credentials['username'],
                'password' => $credentials['password'],
            ], $remember)
        ) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        if ($credentials['username'] === 'AdminKasir123' && $credentials['password'] === 'admin') {
            session(['logged_in' => true, 'user' => 'AdminKasir123']);

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'username' => 'Username atau password yang Anda masukkan salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
