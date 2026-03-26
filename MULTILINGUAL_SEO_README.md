# Multilingual SEO README (Rocket LMS)

## Current Multilingual Behavior (What the code does)

### Locale selection (HTML pages)
- Web (non-API) locale is determined in `app/Http/Middleware/UserLocale.php`.
- For logged-in users it prefers `auth()->user()->language`.
- For guests it prefers the `user_locale` cookie (`Cookie::get('user_locale')`).
- It validates the locale against `getGeneralSettings()['user_languages']`.
- The chosen locale is stored into the session: `Session::put('locale', ...)`.
- Views apply it in `app/Http/Middleware/Share.php` using `App::setLocale(session('locale'))`.

### Locale selection (API)
- API requests can set language via the `x-locale` header in `app/Http/Middleware/Api/SetLocale.php`.
- It also validates the locale against `user_languages`.

### Language switching UX
- The web UI switches language by posting to `POST /locale` (`app/Http/Controllers/Web/LocaleController.php`).
- The URL path itself is *not* language-prefixed (no `/en/...` vs `/ar/...` routing pattern found).

### Translated content + meta tags
- Translated values are loaded based on the current Laravel locale (Astrotomic Translatable + `getTranslateAttributeValue()`).
- The `<html lang="...">` attribute is set in layouts (e.g. `resources/views/web/default/layouts/app.blade.php` and `landing.blade.php`).
- Main SEO/meta tags are assembled in `resources/views/web/default/includes/metas.blade.php`.
- There is a DB-driven injection point for additional tags:
  - `metas.blade.php` calls `getSeoMetas('extra_meta_tags')`.
  - Admin UI edits this under `extra_meta_tags` (see `admin/settings/seo`).

### Robots
- `resources/views/web/default/includes/metas.blade.php` sets a default:
  - `pageRobot ?? 'NOODP, nofollow, noindex'`
- Additionally, `resources/views/web/default/layouts/app.blade.php` hard-codes:
  - `<meta name="robots" content="noindex,nofollow">`
- This means many pages may effectively be treated as `noindex` by search engines even if controllers try to provide `$pageRobot`.

### Sitemaps
- Sitemaps are generated in `app/Http/Controllers/SitemapController.php`.
- Sitemap URLs are generated without language awareness (URLs do not include a locale prefix).
- Routes are:
  - `/sitemap.xml`
  - `/sitemap-courses.xml`
  - `/sitemap-blog.xml`
  - plus paginated course sitemaps.

### Canonical / hreflang
- No `rel="canonical"` or `hreflang` generation was found in templates.

## SEO Compatibility Assessment (Is it “SEO compatible”?)

### 1) Multi-language indexing is not reliable today
Because locale is selected via cookie/session (and optionally `x-locale` header for APIs) rather than via the URL path:
- Crawlers typically do not store your `user_locale` cookie.
- Crawlers generally request the same URL repeatedly; your server will likely serve the default locale to them.
- Result: Google/Bing will usually index *only one language variant* per URL, not all languages.

### 2) Missing `hreflang`/`canonical` harms language targeting
Even if multiple languages were visible, without:
- `rel="alternate" hreflang="..."` links between language variants, and
- a per-variant `rel="canonical"`,
search engines may treat translations as duplicates or pick the wrong language for a given query/region.

### 3) Sitemaps are not language-aware
Sitemaps don’t include language-specific URL variants, so crawlers have no structured discovery path for translated versions.

### 4) Robots likely blocks indexing (major issue)
The hard-coded `noindex,nofollow` meta tag in `resources/views/web/default/layouts/app.blade.php` is a major blocker.
If this is present on most pages, multilingual SEO can’t work even for the default language.

## What Should We Change (Recommended SEO Plan)

### Phase 1 (Must): Fix indexing/robots first
1. Remove or conditionally disable the hard-coded robots tag in:
   - `resources/views/web/default/layouts/app.blade.php`
2. Ensure `metas.blade.php` is the single source of truth for `robots`.
3. Default should be `index, follow` on public pages that you want indexed.

### Phase 2 (Must): Make language part of the URL
Choose one approach:
1. Path prefix (most common):
   - `/en/...`, `/ar/...`, `/es/...`
2. Subdomains:
   - `en.example.com`, `ar.example.com`

Then:
- Update the language switcher to link to the correct localized URL variant.
- Update routing + middleware to set locale from `{locale}` path segment.

### Phase 3 (Must): Add canonical + hreflang
For each localized page, output:
- `rel="canonical"` pointing to the current language URL
- `link rel="alternate" hreflang="xx-YY" href="..."`

Best practice is to generate these dynamically per page using:
- current page type + slug/id
- available locales in your DB

### Phase 4 (Must): Generate per-locale sitemaps
Update `SitemapController` so sitemaps include localized URLs:
- `/sitemap.xml` should reference language-specific entries or per-locale sitemaps
- Add one of:
  - `sitemap-{lang}.xml`, or
  - a single sitemap with `/en/...` and `/ar/...` URLs

### Phase 5 (Good to have): Accept-Language redirect (nice UX)
After you have locale-in-URL:
- Use `Accept-Language` only for redirecting the user to the correct localized URL.
- Do not rely on cookie-only language selection for SEO.

## Implementation Notes (Relevant Files)
- Locale selection:
  - `app/Http/Middleware/UserLocale.php`
  - `app/Http/Middleware/Share.php`
  - `app/Http/Middleware/Api/SetLocale.php`
  - `app/Http/Controllers/Web/LocaleController.php`
- Meta/robots:
  - `resources/views/web/default/includes/metas.blade.php`
  - `resources/views/web/default/layouts/app.blade.php`
  - `resources/views/web/default/layouts/landing.blade.php`
- Sitemaps:
  - `app/Http/Controllers/SitemapController.php`
- Language switch UI:
  - `resources/views/web/default/includes/top_nav.blade.php`

## Quick Validation Checklist
After applying changes, verify:
1. `curl -I https://yourdomain.com/en/...` and `.../ar/...` return correct HTML and the correct `<html lang="">`.
2. HTML contains `hreflang` and `canonical`.
3. `robots` is not `noindex` for public pages.
4. Each language variant appears in language-aware sitemaps.
5. Google Search Console shows separate language URLs being crawled/indexed.

