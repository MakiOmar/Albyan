<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function setLocale(Request $request)
    {
        $this->validate($request, [
            'locale' => 'required'
        ]);

        $generalSettings = getGeneralSettings();
        $userLanguages = $generalSettings['user_languages'] ?? [];

        $localeInput = mb_strtoupper((string) $request->get('locale'));

        // The language picker can submit either a language code or a country code.
        if (in_array($localeInput, $userLanguages, true)) {
            $locale = $localeInput;
        } else {
            $locale = localeToCountryCode($localeInput, true);
        }

        $locale = mb_strtoupper((string) $locale);

        if (in_array($locale, $userLanguages)) {
            if (auth()->check()) {
                $user = auth()->user();
                $user->update([
                    'language' => $locale
                ]);
            } else {
                Cookie::queue('user_locale', $locale, 30 * 24 * 60);
            }

            // Admin (and front) UI language is driven by session; keep it in sync.
            Session::put('locale', mb_strtolower($locale));
        }

        $previousUrl = $request->get('previous_url');

        if (empty($previousUrl)) {
            $previousUrl = $request->headers->get('referer') ?: url()->previous();
        }

        if (empty($previousUrl)) {
            return redirect('/' . mb_strtolower($locale));
        }

        $supportedLocaleCodes = getSupportedLocaleCodes();
        $localeSegment = mb_strtolower($locale);

        $previousParts = parse_url((string) $previousUrl);
        $path = $previousParts['path'] ?? '/';
        if (empty($path)) {
            $path = '/';
        }
        $query = !empty($previousParts['query']) ? ('?' . $previousParts['query']) : '';

        if ($path[0] !== '/') {
            $path = '/' . ltrim((string) $path, '/');
        }

        // Detect current locale from URL, then strip it.
        $segments = array_values(array_filter(explode('/', $path)));
        $currentLocale = getDefaultLocaleCode();
        if (!empty($segments) && in_array(mb_strtolower($segments[0]), $supportedLocaleCodes, true)) {
            $currentLocale = mb_strtolower($segments[0]);
            $segments = array_values(array_slice($segments, 1));
            $path = '/' . implode('/', $segments);
            $path = rtrim($path, '/') ?: '/';
        }

        // Admin panel lives outside /{locale}/... — never prefix it or filters break as a front 404.
        $adminPrefix = mb_strtolower((string) getAdminPanelUrlPrefix());
        $bareSegments = array_values(array_filter(explode('/', $path)));
        if (!empty($bareSegments) && mb_strtolower($bareSegments[0]) === $adminPrefix) {
            return redirect($path . $query);
        }

        return redirect(buildLocalizedSwitchPath($path, $currentLocale, $localeSegment) . $query);
    }
}
