<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cookie;

class UserLocale
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $generalSettings = getGeneralSettings();

        $defaultLocale = getDefaultLocale();

        $locale = $defaultLocale;

        // SEO: if the URL contains a locale segment, prefer it over cookie/user language.
        $routeLocale = $request->route('locale');
        if (!empty($routeLocale)) {
            $locale = mb_strtoupper($routeLocale);
        } elseif (auth()->check()) {
            $user = auth()->user();
            $locale = !empty($user->language) ? $user->language : $defaultLocale;
        } else {
            $checkCookie = Cookie::get('user_locale');

            if (!empty($checkCookie)) {
                $locale = $checkCookie;
            }
        }

        $userLanguages = $generalSettings['user_languages'] ?? [];

        if (!in_array($locale, $userLanguages)) {
            $locale = $defaultLocale;
        }

        $normalized = mb_strtolower($locale);
        \Session::put('locale', $normalized);
        // Apply immediately so later middleware/views resolve locale-aware settings (e.g. site_name).
        app()->setLocale($normalized);

        return $next($request);
    }
}
