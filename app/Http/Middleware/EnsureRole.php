<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pembatasan halaman per role sesuai DFD Level 2.
 * Pemakaian di route: ->middleware('role:admin') atau ->middleware('role:kasir').
 * Kalau role tidak cocok: request biasa diarahkan ke halaman utama role-nya, request AJAX dapat 403.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if ($user && $user->role === $role) {
            return $next($request);
        }

        if ($request->expectsJson() || ! $request->isMethod('GET')) {
            abort(403, 'Halaman ini khusus ' . ucfirst($role) . '.');
        }

        return redirect()->route($user?->homeRoute() ?? 'login')
            ->with('error', 'Halaman tersebut khusus ' . ucfirst($role) . '.');
    }
}
