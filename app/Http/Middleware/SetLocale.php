<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Bahasa pilihan pengunjung disimpan di session. Halaman Kelola selalu berbahasa Indonesia. */
class SetLocale
{
    public const LOCALES = ['id', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->routeIs('manage.*') ? 'id' : $request->session()->get('locale');

        app()->setLocale(in_array($locale, self::LOCALES, true) ? $locale : config('app.locale'));

        return $next($request);
    }
}
