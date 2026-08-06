<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Translation\SettingTranslation;
use Illuminate\Http\Request;

class NavbarLinksSettingsController extends Controller
{
    public function index(Request $request)
    {
        $name = Setting::$navbarLinkName;
        $this->authorize('admin_additional_pages_' . $name);

        $settings = Setting::firstOrCreate(
            ['name' => $name],
            [
                'page' => 'other',
                'updated_at' => time(),
            ]
        );

        $locale = $this->resolveLocale($request);
        storeContentLocale($locale, $settings->getTable(), $settings->id);

        $items = $this->itemsForLocale($settings->id, $locale);
        $localesWithItems = $this->localesWithItems($settings->id);

        // Admin edit-locale path used to return empty when the selected locale had no
        // translation row, even if another locale (e.g. en) still powered the front.
        $itemsSourceLocale = $locale;
        if (empty($items) && !empty($localesWithItems)) {
            $itemsSourceLocale = $localesWithItems[0];
            $items = $this->itemsForLocale($settings->id, $itemsSourceLocale);
        }

        $data = [
            'pageTitle' => trans('admin/main.additional_pages_title'),
            'items' => $items,
            'selectedLocal' => $locale,
            'itemsSourceLocale' => $itemsSourceLocale,
            'localesWithItems' => $localesWithItems,
            'navbarDebug' => $this->buildDebugPayload($request, $settings, $locale, $items, $itemsSourceLocale, $localesWithItems),
        ];

        return view('admin.additional_pages.' . $name, $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_additional_pages_navbar_links');
        $this->validate($request, [
            'value.*' => 'required',
        ]);

        $data = $request->all();
        $locale = $this->resolveLocale($request);
        $navbar_link = $data['navbar_link'];

        $settings = Setting::updateOrCreate(
            ['name' => Setting::$navbarLinkName],
            [
                'page' => 'other',
                'updated_at' => time(),
            ]
        );

        // Always merge against the same locale being saved (not session/app locale)
        $values = $this->itemsForLocale($settings->id, $locale);

        // First save into an empty locale: copy from another locale that still has links
        if (empty($values)) {
            foreach ($this->localesWithItems($settings->id) as $fallbackLocale) {
                $values = $this->itemsForLocale($settings->id, $fallbackLocale);
                if (!empty($values)) {
                    break;
                }
            }
        }

        $key = ($navbar_link !== 'newLink') ? $navbar_link : random_str(6);
        $values[$key] = $data['value'];

        SettingTranslation::updateOrCreate(
            [
                'setting_id' => $settings->id,
                'locale' => $locale,
            ],
            [
                'value' => json_encode($values),
            ]
        );

        removeContentLocale();
        cache()->forget('settings.' . Setting::$navbarLinkName);

        return redirect(getAdminPanelUrl() . '/additional_page/navbar_links?locale=' . urlencode($locale) . $this->debugQuerySuffix($request));
    }

    public function edit(Request $request, $link_key)
    {
        $this->authorize('admin_additional_pages_navbar_links');

        $settings = Setting::where('name', Setting::$navbarLinkName)->first();
        if (empty($settings)) {
            abort(404);
        }

        $locale = $this->resolveLocale($request);
        storeContentLocale($locale, $settings->getTable(), $settings->id);

        $items = $this->itemsForLocale($settings->id, $locale);
        $localesWithItems = $this->localesWithItems($settings->id);

        $itemsSourceLocale = $locale;
        if (empty($items) && !empty($localesWithItems)) {
            $itemsSourceLocale = $localesWithItems[0];
            $items = $this->itemsForLocale($settings->id, $itemsSourceLocale);
        }

        $result = null;
        if (!empty($items[$link_key]) && is_array($items[$link_key])) {
            $result = (object) $items[$link_key];
        }

        $navbarDebug = $this->buildDebugPayload(
            $request,
            $settings,
            $locale,
            $items,
            $itemsSourceLocale,
            $localesWithItems,
            [
                'action' => 'edit',
                'link_key' => $link_key,
                'link_key_found' => !empty($result),
                'available_keys' => array_keys($items),
            ]
        );

        // With ?debug=1, stay on the page instead of 404 so we can inspect keys/locale
        if (empty($result) && !$this->wantsDebug($request)) {
            abort(404);
        }

        $data = [
            'pageTitle' => trans('admin/pages/setting.settings_navbar_links'),
            'navbar_link' => $result,
            'navbarLinkKey' => $link_key,
            'items' => $items,
            'selectedLocal' => $locale,
            'itemsSourceLocale' => $itemsSourceLocale,
            'localesWithItems' => $localesWithItems,
            'navbarDebug' => $navbarDebug,
        ];

        return view('admin.additional_pages.navbar_links', $data);
    }

    public function delete(Request $request, $link_key)
    {
        $this->authorize('admin_additional_pages_navbar_links');
        $settings = Setting::where('name', Setting::$navbarLinkName)->first();

        if (empty($settings)) {
            abort(404);
        }

        $locale = $this->resolveLocale($request);

        if (!empty($settings->translations)) {
            foreach ($settings->translations as $translation) {
                $values = json_decode($translation->value, true);
                if (!is_array($values) || !array_key_exists($link_key, $values)) {
                    continue;
                }

                unset($values[$link_key]);

                $settings->update([
                    'updated_at' => time(),
                ]);

                $translation->update([
                    'value' => json_encode($values),
                ]);
            }

            cache()->forget('settings.' . Setting::$navbarLinkName);

            return redirect(getAdminPanelUrl() . '/additional_page/navbar_links?locale=' . urlencode($locale) . $this->debugQuerySuffix($request));
        }

        abort(404);
    }

    private function resolveLocale(Request $request): string
    {
        return mb_strtolower((string) $request->get('locale', getDefaultLocale()));
    }

    private function wantsDebug(Request $request): bool
    {
        return $request->query('debug') === '1' || $request->query('debug') === 1;
    }

    private function debugQuerySuffix(Request $request): string
    {
        return $this->wantsDebug($request) ? '&debug=1' : '';
    }

    /**
     * Temporary live diagnostics for ?debug=1 on this admin page.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>|null
     */
    private function buildDebugPayload(
        Request $request,
        Setting $settings,
        string $locale,
        array $items,
        string $itemsSourceLocale,
        array $localesWithItems,
        array $extra = []
    ): ?array {
        if (!$this->wantsDebug($request)) {
            return null;
        }

        $rows = SettingTranslation::where('setting_id', $settings->id)
            ->get(['id', 'locale', 'value']);

        $translationRows = [];
        foreach ($rows as $row) {
            $decoded = json_decode((string) $row->value, true);
            $decodedKeys = is_array($decoded) ? array_keys($decoded) : [];
            $parsedItems = $this->decodeItems($row->value);

            $translationRows[] = [
                'id' => $row->id,
                'locale_raw' => $row->locale,
                'locale_lower' => mb_strtolower((string) $row->locale),
                'value_bytes' => strlen((string) $row->value),
                'json_error' => json_last_error_msg(),
                'top_level_keys' => $decodedKeys,
                'decoded_item_keys' => array_keys($parsedItems),
                'decoded_item_count' => count($parsedItems),
                'value_preview' => mb_substr((string) $row->value, 0, 500),
            ];
        }

        $frontHelperCount = 0;
        $frontHelperError = null;
        try {
            $frontLinks = getNavbarLinks();
            $frontHelperCount = is_array($frontLinks) ? count($frontLinks) : 0;
        } catch (\Throwable $e) {
            $frontHelperError = $e->getMessage();
        }

        $cached = cache()->get('settings.' . Setting::$navbarLinkName);

        return array_merge([
            'enabled' => true,
            'route' => $request->path(),
            'query' => $request->query(),
            'app_locale' => app()->getLocale(),
            'default_locale' => getDefaultLocale(),
            'selected_locale' => $locale,
            'items_source_locale' => $itemsSourceLocale,
            'locales_with_items' => $localesWithItems,
            'setting_id' => $settings->id,
            'setting_name' => $settings->name,
            'setting_page' => $settings->page ?? null,
            'items_count' => count($items),
            'item_keys' => array_keys($items),
            'items_snapshot' => $items,
            'translation_row_count' => count($translationRows),
            'translation_rows' => $translationRows,
            'front_getNavbarLinks_count' => $frontHelperCount,
            'front_getNavbarLinks_error' => $frontHelperError,
            'cache_settings_navbar_links_present' => $cached !== null,
            'cache_settings_type' => is_object($cached) ? get_class($cached) : gettype($cached),
            'content_locale_session' => getContentLocale(),
        ], $extra);
    }

    /**
     * @return array<string, array{title?: string, link?: string, order?: mixed}>
     */
    private function itemsForLocale(int $settingId, string $locale): array
    {
        $locale = mb_strtolower($locale);

        $translation = SettingTranslation::where('setting_id', $settingId)
            ->whereRaw('LOWER(locale) = ?', [$locale])
            ->first();

        return $this->decodeItems($translation?->value);
    }

    /**
     * @return list<string>
     */
    private function localesWithItems(int $settingId): array
    {
        $locales = [];

        $rows = SettingTranslation::where('setting_id', $settingId)->get(['locale', 'value']);
        foreach ($rows as $row) {
            if (!empty($this->decodeItems($row->value))) {
                $locales[] = mb_strtolower((string) $row->locale);
            }
        }

        return array_values(array_unique($locales));
    }

    /**
     * @return array<string, array{title?: string, link?: string, order?: mixed}>
     */
    private function decodeItems(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }

        // Keep only link entries (keyed maps with title/link); drop noise keys
        $items = [];
        foreach ($decoded as $key => $value) {
            if (!is_array($value)) {
                continue;
            }
            if (!array_key_exists('title', $value) && !array_key_exists('link', $value)) {
                continue;
            }
            $items[(string) $key] = $value;
        }

        return $items;
    }
}
