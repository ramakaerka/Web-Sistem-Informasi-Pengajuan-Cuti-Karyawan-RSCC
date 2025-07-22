<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (str_starts_with($request->path(), 'livewire')) {
            return $next($request);
        }
        if ($request->is('livewire/*')) {
            return $next($request);
        }
        if (Auth::check() && Auth::user()->role === $role) {
            return $next($request);
        }
        return redirect('/login')->with('error', 'Anda tidak memiliki akses! Anda harus login terlebih dahulu.');
    }
}
