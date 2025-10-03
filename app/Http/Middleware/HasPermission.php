<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HasPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = Auth::user();
       if ($user && ($user->role === 'admin' || (is_array($user->permissions) && in_array($permission, $user->permissions)))) {
    return $next($request);
}
        abort(403, 'Nemate dozvolu za ovu stranicu.');
    }
}
