<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->role === 'admin') {
            return $next($request); // lanjut ke route admin
        }

        // Redirect ke route utama pegawai
        return redirect()->route('absendong', ['id' => $user->id]);
    }
}

