<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

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

        // The language picker can submit either:
        // 1) a language code (e.g. EN, AR), or
        // 2) a country code (e.g. US, IQ) that we need to map to language via localeToCountryCode(..., true).
        // Accept both to prevent accidentally mapping EN -> invalid -> fallback (stays on Arabic).
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
        }

        $previousUrl = $request->get('previous_url');

        // When the language form doesn't include `previous_url`, fall back to the browser referer.
        // This lets us reliably redirect from `/` to `/{locale}/`.
        if (empty($previousUrl)) {
            $previousUrl = $request->headers->get('referer') ?: url()->previous();
        }

        if (!empty($previousUrl)) {
            // `user_languages` can be stored either as:
            // - a numeric array of locale codes: ['AR', 'EN']
            // - or an associative array: ['AR' => 'Arabic', 'EN' => 'English']
            // For locale-prefix stripping we only want the actual locale codes (two-letter).
            $supportedLocaleCodes = [];
            $supportedLocaleCandidates = array_merge(array_keys($userLanguages), array_values($userLanguages));
            foreach ($supportedLocaleCandidates as $candidate) {
                $candidate = mb_strtolower((string) $candidate);
                if (preg_match('/^[a-z]{2}$/', $candidate)) {
                    $supportedLocaleCodes[] = $candidate;
                }
            }
            $supportedLocaleCodes = array_values(array_unique($supportedLocaleCodes));

            $localeSegment = mb_strtolower($locale);

            $previousParts = parse_url((string) $previousUrl);
            $path = $previousParts['path'] ?? parse_url((string) $previousUrl, PHP_URL_PATH) ?? '/';
            if (empty($path)) {
                $path = '/';
            }
            $query = !empty($previousParts['query']) ? ('?' . $previousParts['query']) : '';

            // Normalize path
            if (empty($path) || $path[0] !== '/') {
                $path = '/' . ltrim((string) $path, '/');
            }

            // If path is already locale-prefixed, remove it so we can re-apply it for the new locale.
            $segments = array_values(array_filter(explode('/', $path)));
            if (!empty($segments)) {
                $firstSeg = mb_strtolower((string) $segments[0]);
                if (in_array($firstSeg, $supportedLocaleCodes, true)) {
                    $segments = array_values(array_slice($segments, 1));
                    $path = '/' . implode('/', $segments);
                    $path = rtrim($path, '/') ?: '/';
                }
            }

            $isCourse = (bool) preg_match('#^/course(/|$)#', $path);
            $isBlogDetail = (bool) preg_match('#^/blog/.+#', $path) && !preg_match('#^/blog/?$#', $path);
            $isBlogCategory = (bool) preg_match('#^/blog/(categories|category)/#', $path);
            $isSlugBasedBlog = $isBlogDetail || $isBlogCategory;

            // Only redirect locale into non-slug pages to avoid breaking translated slugs.
            $isNonSlugPage =
                ($path === '/' || $path === '/classes' || $path === '/reward-courses' || $path === '/instructors' || $path === '/organizations' || $path === '/about' || $path === '/contact' || $path === '/blog' || $path === '/blog/');

            // ID-based pages can still safely use locale prefix.
            $isProfilePage = (bool) preg_match('#^/users/\d+/profile$#', $path);

            if (!$isCourse && !$isSlugBasedBlog && ($isNonSlugPage || $isProfilePage)) {
                if ($path === '/' || $path === '') {
                    return redirect('/' . $localeSegment . '/'); // /{locale}/
                }

                return redirect('/' . $localeSegment . $path . $query);
            }

            // Keep the existing URL for slug-based pages (courses, blog details/categories, city contact, etc.).
            return redirect($previousUrl);
        }

        return redirect()->back();
    }
}
