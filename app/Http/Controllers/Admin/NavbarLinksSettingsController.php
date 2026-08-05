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

        return redirect(getAdminPanelUrl() . '/additional_page/navbar_links?locale=' . urlencode($locale));
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

        if (empty($result)) {
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

            return redirect(getAdminPanelUrl() . '/additional_page/navbar_links?locale=' . urlencode($locale));
        }

        abort(404);
    }

    private function resolveLocale(Request $request): string
    {
        return mb_strtolower((string) $request->get('locale', getDefaultLocale()));
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
