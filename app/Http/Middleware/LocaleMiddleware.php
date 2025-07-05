<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } else {
            // Set default locale based on browser language or a fallback
            $browserLang = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
            if (in_array($browserLang, ['en', 'de'])) {
                App::setLocale($browserLang);
            } else {
                App::setLocale('en'); // Fallback
            }
        }

        return $next($request);
    }
}
