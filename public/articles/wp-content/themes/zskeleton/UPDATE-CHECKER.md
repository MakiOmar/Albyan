# Theme Update Checker Guide

This theme ships with the [`plugin-update-checker`](plugin-update-checker/) library to deliver self-hosted updates for themes. Follow these steps to replicate or adjust the setup.

## How It’s Wired Today

- The library is bundled in `plugin-update-checker/` and loaded from `functions.php`:
  
```76:83:functions.php
require_once ANONY_THEME_DIR . '/plugin-update-checker/plugin-update-checker.php';
Puc_v4_Factory::buildUpdateChecker(
	'https://raw.githubusercontent.com/MakiOmar/smartpage/master/plugin-update-checker/examples/theme.json',
	__FILE__,
	'anony-smartpage'
);
```

- Update metadata lives in `plugin-update-checker/examples/theme.json` and points to:
  - `version`: current theme version (`style.css` also carries this).
  - `details_url`: release notes HTML (`version-details.html`).
  - `download_url`: a ZIP of the latest theme build.

## Implementing in Another Theme

1. Copy `plugin-update-checker/` into the theme root.
2. Require the loader early in `functions.php` (outside admin-only hooks), then create the checker:
   ```php
   require_once get_template_directory() . '/plugin-update-checker/plugin-update-checker.php';
   Puc_v4_Factory::buildUpdateChecker(
     'https://example.com/path/to/theme.json', // public JSON with version info
     __FILE__,                                  // any file within the theme
     'your-theme-slug'                          // usually the theme folder name
   );
   ```
3. Host a JSON file (see below) at a public, cacheable URL.

### JSON Template (themes)

```json
{
  "version": "1.2.3",
  "details_url": "https://example.com/theme-1.2.3-notes.html",
  "download_url": "https://example.com/theme-1.2.3.zip"
}
```

## Releasing a New Version (current workflow)

1. Bump `Version` in `style.css` and in `plugin-update-checker/examples/theme.json`.
2. Update `details_url` to point at the latest release notes page (we use `version-details.html`).
3. Update `download_url` to a ZIP of the new build (e.g., GitHub branch/release archive).
4. Deploy/commit so the JSON is accessible at the URL referenced in `functions.php`.
5. (Optional) In WordPress, install the Debug Bar plugin and open **Debug → PUC (anony-smartpage) → Check Now** to trigger an immediate check; otherwise WordPress will pick it up within ~12 hours.

## Troubleshooting Tips

- The second argument to `buildUpdateChecker` must be an absolute path inside the theme; `__FILE__` is safe when called from `functions.php`.
- Keep the slug stable; if you rename the theme directory, update the slug accordingly.
- If updates don’t show, confirm the JSON URL is reachable and not cached by a stale CDN response.

