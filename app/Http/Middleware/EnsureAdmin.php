<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('is_admin') === true) {
            return $next($request);
        }

        // Setelah masuk, admin dikembalikan ke halaman yang tadi dibuka.
        return $request->isMethod('GET')
            ? redirect()->guest(route('manage.login'))
            : to_route('manage.login');
    }
}
