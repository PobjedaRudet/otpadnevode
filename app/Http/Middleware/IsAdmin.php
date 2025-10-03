<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);
        }
        // Umjesto 403 stranice – tiho preusmjeri na početnu uz poruku
        return redirect('/')->with('error', 'Nemate administratorska prava.');
    }
}
