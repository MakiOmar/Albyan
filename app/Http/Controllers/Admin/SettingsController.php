<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\traits\DeviceLimitSettings;
use App\Http\Controllers\Admin\traits\FinancialCurrencySettings;
use App\Http\Controllers\Admin\traits\FinancialOfflineBankSettings;
use App\Http\Controllers\Admin\traits\FinancialUserBankSettings;
use App\Http\Controllers\Admin\traits\NavbarButtonSettings;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\NotificationTemplate;
use App\Models\OfflineBank;
use App\Models\PaymentChannel;
use App\Models\Setting;
use App\Models\Translation\SettingTranslation;
use App\Models\UserBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    use NavbarButtonSettings;
    use FinancialCurrencySettings;
    use FinancialOfflineBankSettings;
    use FinancialUserBankSettings;
    use DeviceLimitSettings;

    public function index()
    {
        removeContentLocale();

        $this->authorize('admin_settings');

        $data = [
            'pageTitle' => trans('admin/main.settings_title'),
        ];

        return view('admin.settings.index', $data);
    }

    public function page(Request $request, $page)
    {
        removeContentLocale();

        $this->authorize('admin_settings_' . $page);

        $settings = Setting::where('page', $page)
            ->get()
            ->keyBy('name');

        foreach ($settings as $setting) {
            $setting->value = json_decode($setting->value, true);
        }

        $data = [
            'pageTitle' => trans('admin/main.settings_title'),
            'settings' => $settings
        ];

        if ($page == 'notifications') {
            $data['notificationTemplates'] = NotificationTemplate::all();
        }

        if ($page == 'financial') {
            $paymentChannels = PaymentChannel::orderBy('created_at', 'desc')->paginate(10);
            $data['paymentChannels'] = $paymentChannels;

            if ($request->get('tab') == 'currency') {
                $data['currencies'] = Currency::query()
                    ->orderBy('order', 'asc')
                    ->get();
            }

            if ($request->get('tab') == 'offline_banks') {
                $data['offlineBanks'] = OfflineBank::query()
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            if ($request->get('tab') == 'user_banks') {
                $data['userBanks'] = UserBank::query()
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

        if ($page == 'seo') {
            $seoLocale = mb_strtolower((string) $request->get('locale', app()->getLocale()));
            $data['seoLocale'] = $seoLocale;

            // Load seo_metas JSON for the selected admin locale (not forced EN).
            $seoSetting = Setting::where('name', Setting::$seoMetasName)->first();
            $seoValues = [];
            if (!empty($seoSetting)) {
                $translation = SettingTranslation::where('setting_id', $seoSetting->id)
                    ->whereRaw('LOWER(locale) = ?', [$seoLocale])
                    ->orderByDesc('id')
                    ->first();
                if (!empty($translation) && !empty($translation->value)) {
                    $decoded = json_decode($translation->value, true);
                    if (is_array($decoded)) {
                        $seoValues = $decoded;
                    }
                }
            }
            $data['seoMetasValues'] = $seoValues;

            // Temporary live debug: raw DB snapshot for every locale (not cached / not merged).
            $data['seoMetasDebug'] = $this->buildSeoMetasDebugSnapshot($seoSetting?->id, [
                'source' => 'admin_seo_page',
                'selected_locale' => $seoLocale,
                'app_locale' => app()->getLocale(),
                'form_locale_hidden_will_be' => $seoLocale,
            ]);

            $schemaLocale = $seoLocale;
            $schemaValues = [];
            $schemaSetting = Setting::where('name', Setting::$schemaSettingsName)->first();
            if (!empty($schemaSetting)) {
                $translation = SettingTranslation::where('setting_id', $schemaSetting->id)
                    ->where('locale', $schemaLocale)
                    ->first();
                if (!empty($translation) && !empty($translation->value)) {
                    $decoded = json_decode($translation->value, true);
                    if (is_array($decoded)) {
                        $schemaValues = $decoded;
                    }
                }
            }
            $data['schemaValues'] = $schemaValues;
            $data['schemaLocale'] = $schemaLocale;
            $data['schemaDefaults'] = config('schema.defaults.' . $schemaLocale)
                ?? config('schema.defaults.en', []);
        }

        return view('admin.settings.' . $page, $data);
    }

    public function personalizationPage(Request $request, $name)
    {
        removeContentLocale();

        $this->authorize('admin_settings_personalization');

        $settings = Setting::where('name', $name)->first();

        $values = null;

        if (!empty($settings)) {
            $defaultLocal = getDefaultLocale();

            if (in_array($name, [Setting::$pageBackgroundName, Setting::$homeSectionsName, Setting::$themeFontsName, Setting::$themeColorsName])) {
                $defaultLocal = Setting::$defaultSettingsLocale;
            }

            $locale = $request->get('locale', mb_strtolower($defaultLocal));

            storeContentLocale($locale, $settings->getTable(), $settings->id);

            if (!empty($settings->value)) {
                $values = json_decode($settings->value, true);

                // Use the request/content locale — never $settings->locale (Translatable
                // exposes locale() as a non-relation method and Laravel 11 throws).
                $values['locale'] = mb_strtoupper($locale);
            }
        }

        // Special handling for course_card_styles - use config values if no database setting exists
        if ($name === 'course_card_styles' && empty($values)) {
            $config = config('course_card_styles');
            $values = [
                'default_style' => $config['default_style'] ?? 'gray_hover',
                'styles' => $config['styles'] ?? [],
                'settings' => $config['settings'] ?? []
            ];
        }

        $data = [
            'pageTitle' => trans('admin/main.settings_title'),
            'values' => $values,
            'name' => $name
        ];

        return view('admin.settings.personalization', $data);
    }

    public function store(Request $request, $name)
    {

        if (!empty($request->get('name'))) {
            $name = $request->get('name');
        }

        $tmpValues = $request->get('value', null);
        $adminPanelUrl = (!empty($tmpValues) and !empty($tmpValues['admin_panel_url'])) ? $tmpValues['admin_panel_url'] : null;

        if (!empty($tmpValues)) {
            $locale = $request->get('locale', Setting::$defaultSettingsLocale); // default is "en"

            $values = [];
            foreach ($tmpValues as $key => $val) {
                if (is_array($val)) {
                    $values[$key] = array_filter($val);
                } else {
                    $values[$key] = $val;
                }
            }

            if ($name == 'referral') {

                $validator = Validator::make($values, [
                    'affiliate_user_commission' => 'nullable|numeric',
                ]);

                $validator->validate();
            } elseif ($name == 'general') {
                if (empty($values['user_languages']) or !is_array($values['user_languages'])) {
                    $values['content_translate'] = false;
                }
            } elseif ($name == 'maintenance_settings') {
                if (!empty($values['end_date'])) {
                    $values['end_date'] = convertTimeToUTCzone($values['end_date'], null)->getTimestamp();
                }
            }

            $values = json_encode($values);
            $values = str_replace('record', rand(1, 600), $values);

            $settings = Setting::updateOrCreate(
                ['name' => $name],
                [
                    'page' => $request->get('page', 'other'),
                    'updated_at' => time(),
                ]
            );

            SettingTranslation::updateOrCreate(
                [
                    'setting_id' => $settings->id,
                    'locale' => mb_strtolower($locale)
                ],
                [
                    'value' => $values,
                ]
            );

            cache()->forget('settings.' . $name);

            if ($name === Setting::$generalOptionsName) {
                Cache::forget('rss-blog.xml');
                Artisan::call('sitemap:generate', ['type' => 'all']);
            }

            if ($name == 'general') {
                cache()->forget('settings.getDefaultLocale');
            }

            if ($name === Setting::$performanceSettingsName) {
                Cache::forget('settings.' . Setting::$performanceSettingsName);
            }

            // Homepage payload includes settings-driven hero/sections — drop shared cache.
            \App\Http\Controllers\Web\HomeController::clearHomePageCache();
        }

        if ($name == "security") { // after change admin panel url
            $url = !empty($adminPanelUrl) ? $adminPanelUrl : getAdminPanelUrl();
            $url .= '/settings/general';

            return redirect($url);
        }

        return back();
    }

    public function storeSeoMetas(Request $request)
    {
        $name = Setting::$seoMetasName;

        $this->authorize('admin_settings_seo');

        $data = $request->all();
        $locale = mb_strtolower((string) $request->get('locale', Setting::$defaultSettingsLocale));
        $newValues = $data['value'] ?? [];
        if (!is_array($newValues)) {
            $newValues = [];
        }
        $values = [];
        $settings = Setting::where('name', $name)->first();

        $before = $this->buildSeoMetasDebugSnapshot($settings?->id, [
            'source' => 'storeSeoMetas_before',
            'request_locale' => $locale,
            'app_locale' => app()->getLocale(),
            'submitted_keys' => array_keys($newValues),
            'submitted_home' => $newValues['home'] ?? null,
            'raw_locale_param' => $request->input('locale'),
            'all_locale_inputs' => $request->input('locale'),
        ]);
        Log::warning('[SEO_METAS_DEBUG] before save', $before);

        // Load existing values for THIS locale only (never copy from another locale).
        $existingTranslation = null;
        if (!empty($settings)) {
            $existingTranslation = SettingTranslation::where('setting_id', $settings->id)
                ->whereRaw('LOWER(locale) = ?', [$locale])
                ->orderByDesc('id')
                ->first();
            if (!empty($existingTranslation) && !empty($existingTranslation->value)) {
                $decoded = json_decode($existingTranslation->value, true);
                if (is_array($decoded)) {
                    $values = $decoded;
                }
            }
        }

        // Deep-merge only submitted page keys into this locale's JSON.
        foreach ($newValues as $newKey => $newValue) {
            $values[$newKey] = $newValue;
        }

        $settings = Setting::updateOrCreate(
            ['name' => $name],
            [
                'page' => 'seo',
                'updated_at' => time(),
            ]
        );

        $touchedTranslationId = null;
        $writeMode = null;

        // Update one locale row only; normalize casing and drop duplicate locale variants.
        if (!empty($existingTranslation) && (int) $existingTranslation->setting_id === (int) $settings->id) {
            $existingTranslation->locale = $locale;
            $existingTranslation->value = json_encode($values, JSON_UNESCAPED_UNICODE);
            $existingTranslation->save();
            $touchedTranslationId = $existingTranslation->id;
            $writeMode = 'update_existing_row';

            SettingTranslation::where('setting_id', $settings->id)
                ->whereRaw('LOWER(locale) = ?', [$locale])
                ->where('id', '!=', $existingTranslation->id)
                ->delete();
        } else {
            $row = SettingTranslation::updateOrCreate(
                [
                    'setting_id' => $settings->id,
                    'locale' => $locale,
                ],
                [
                    'value' => json_encode($values, JSON_UNESCAPED_UNICODE),
                ]
            );
            $touchedTranslationId = $row->id;
            $writeMode = 'update_or_create';
        }

        cache()->forget('settings.' . $name);
        foreach (['en', 'ar', 'es'] as $loc) {
            cache()->forget('settings.' . $name . '.locale.' . $loc);
        }
        // Bust all configured locale SEO caches (and home payloads that bake title/description).
        try {
            foreach (\App\Http\Controllers\Web\HomeController::homeCacheLocales() as $loc) {
                cache()->forget('settings.' . $name . '.locale.' . mb_strtolower($loc));
            }
            \App\Http\Controllers\Web\HomeController::clearHomePageCache();
        } catch (\Throwable $e) {
            // Non-fatal if home locales cannot be resolved during install.
        }

        Setting::$seoMetas = null;

        $after = $this->buildSeoMetasDebugSnapshot($settings->id, [
            'source' => 'storeSeoMetas_after',
            'request_locale' => $locale,
            'app_locale' => app()->getLocale(),
            'write_mode' => $writeMode,
            'touched_translation_id' => $touchedTranslationId,
            'submitted_keys' => array_keys($newValues),
            'submitted_home' => $newValues['home'] ?? null,
        ]);
        Log::warning('[SEO_METAS_DEBUG] after save', $after);

        // Detect accidental cross-locale home title changes for the flash panel.
        $changedLocales = [];
        $beforeByLocale = [];
        foreach (($before['rows'] ?? []) as $row) {
            $beforeByLocale[mb_strtolower((string) $row['locale'])] = $row['home_title'] ?? null;
        }
        foreach (($after['rows'] ?? []) as $row) {
            $loc = mb_strtolower((string) $row['locale']);
            $prev = $beforeByLocale[$loc] ?? null;
            $next = $row['home_title'] ?? null;
            if ($prev !== $next) {
                $changedLocales[] = $loc . ' (id ' . $row['id'] . ')';
            }
        }

        session()->flash('seo_metas_debug', [
            'target_locale' => $locale,
            'write_mode' => $writeMode,
            'touched_translation_id' => $touchedTranslationId,
            'changed_locales' => $changedLocales,
            'before' => $before,
            'after' => $after,
        ]);

        return redirect(getAdminPanelUrl() . '/settings/seo?locale=' . urlencode($locale));
    }

    /**
     * Temporary live debug helper: dump every seo_metas translation row home title/desc.
     */
    protected function buildSeoMetasDebugSnapshot(?int $settingId, array $meta = []): array
    {
        $rows = [];
        if (!empty($settingId)) {
            $translations = SettingTranslation::where('setting_id', $settingId)
                ->orderBy('locale')
                ->orderBy('id')
                ->get(['id', 'locale', 'value']);

            foreach ($translations as $translation) {
                $decoded = json_decode((string) $translation->value, true);
                $home = is_array($decoded) ? ($decoded['home'] ?? null) : null;
                $rows[] = [
                    'id' => $translation->id,
                    'locale' => $translation->locale,
                    'home_title' => is_array($home) ? (string) ($home['title'] ?? '') : null,
                    'home_description' => is_array($home)
                        ? mb_substr((string) ($home['description'] ?? ''), 0, 120)
                        : null,
                    'json_bytes' => strlen((string) $translation->value),
                    'page_keys' => is_array($decoded) ? array_keys($decoded) : [],
                ];
            }
        }

        return array_merge($meta, [
            'setting_id' => $settingId,
            'row_count' => count($rows),
            'rows' => $rows,
            'at' => now()->toDateTimeString(),
        ]);
    }

    public function storeSchemaSettings(Request $request)
    {
        $name = Setting::$schemaSettingsName;

        $this->authorize('admin_settings_seo');

        $locale = mb_strtolower((string) $request->get('locale', app()->getLocale()));
        $newValues = $request->input('value', []);
        if (!is_array($newValues)) {
            $newValues = [];
        }

        // Keep only known schema copy keys as trimmed strings.
        $allowed = [
            'legal_name',
            'alternate_names',
            'org_description',
            'logo_caption',
            'place_name',
            'admissions_contact_type',
            'whatsapp_contact_type',
            'home_webpage_name',
            'home_webpage_description',
            'breadcrumb_home_name',
            'online_instance_name_suffix',
            'onsite_instance_name_suffix',
            'course_workload_template',
            'learning_resource_type',
        ];

        $values = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $newValues)) {
                $values[$key] = is_string($newValues[$key]) ? trim($newValues[$key]) : '';
            }
        }

        $settings = Setting::updateOrCreate(
            ['name' => $name],
            [
                'page' => 'seo',
                'updated_at' => time(),
            ]
        );

        // Comment: write only the selected locale row; normalize casing / drop duplicates.
        $existingTranslation = SettingTranslation::where('setting_id', $settings->id)
            ->whereRaw('LOWER(locale) = ?', [$locale])
            ->orderByDesc('id')
            ->first();

        if (!empty($existingTranslation)) {
            $existingTranslation->locale = $locale;
            $existingTranslation->value = json_encode($values, JSON_UNESCAPED_UNICODE);
            $existingTranslation->save();

            SettingTranslation::where('setting_id', $settings->id)
                ->whereRaw('LOWER(locale) = ?', [$locale])
                ->where('id', '!=', $existingTranslation->id)
                ->delete();
        } else {
            SettingTranslation::create([
                'setting_id' => $settings->id,
                'locale' => $locale,
                'value' => json_encode($values, JSON_UNESCAPED_UNICODE),
            ]);
        }

        cache()->forget('settings.' . $name);
        foreach (['en', 'ar', 'es'] as $loc) {
            cache()->forget('settings.' . $name . '.locale.' . $loc);
        }
        try {
            foreach (\App\Http\Controllers\Web\HomeController::homeCacheLocales() as $loc) {
                cache()->forget('settings.' . $name . '.locale.' . mb_strtolower($loc));
            }
        } catch (\Throwable $e) {
            // Non-fatal during install if home locales cannot be resolved.
        }
        cache()->forget('llms_txt.ar');
        cache()->forget('llms_txt.en');
        cache()->forget('llms_txt.es');
        Setting::$schemaSettings = null;

        return redirect(getAdminPanelUrl() . '/settings/seo?locale=' . urlencode($locale) . '#schema');
    }

    public function editSocials($social_key)
    {
        removeContentLocale();

        $this->authorize('admin_settings_general');
        $settings = Setting::where('name', Setting::$socialsName)->first();

        if (!empty($settings)) {
            $values = json_decode($settings->value);

            foreach ($values as $key => $value) {
                if ($key == $social_key) {
                    $data = [
                        'pageTitle' => trans('admin/pages/setting.settings_socials'),
                        'social' => $value,
                        'socialKey' => $social_key,
                    ];

                    return view('admin.settings.general', $data);
                }
            }
        }

        abort(404);
    }

    public function deleteSocials($social_key, $locale = null)
    {
        $this->authorize('admin_settings_general');
        $settings = Setting::where('name', Setting::$socialsName)->first();

        if (empty($locale)) {
            $locale = Setting::$defaultSettingsLocale;
        }

        if (!empty($settings)) {
            $values = json_decode($settings->value);
            foreach ($values as $key => $value) {
                if ($key == $social_key) {
                    unset($values->$social_key);
                }
            }

            $settings = Setting::updateOrCreate(
                ['name' => Setting::$socialsName],
                [
                    'page' => 'general',
                    'updated_at' => time(),
                ]
            );

            SettingTranslation::updateOrCreate(
                [
                    'setting_id' => $settings->id,
                    'locale' => mb_strtolower($locale)
                ],
                [
                    'value' => json_encode($values),
                ]
            );

            cache()->forget('settings.' . Setting::$socialsName);

            return redirect(getAdminPanelUrl() . '/settings/general');
        }

        abort(404);
    }

    public function storeSocials(Request $request)
    {
        $this->authorize('admin_settings_general');
        $this->validate($request, [
            'value.*' => 'required',
        ]);

        $data = $request->all();
        $locale = $request->get('locale', Setting::$defaultSettingsLocale);
        $social = $data['social'];
        $values = [];

        $settings = Setting::where('name', Setting::$socialsName)->first();

        if ($social !== 'newSocial') {
            if (!empty($settings) and !empty($settings->value)) {
                $values = json_decode($settings->value);
                foreach ($values as $key => $value) {
                    if ($key == $social) {
                        $values->$key = $data['value'];
                    }
                }
            }
        } else {
            if (!empty($settings) and !empty($settings->value)) {
                $values = json_decode($settings->value);
            }
            $key = str_replace(' ', '_', $data['value']['title']);
            $newValue[$key] = $data['value'];
            $values = array_merge((array)$values, $newValue);
        }

        $settings = Setting::updateOrCreate(
            ['name' => Setting::$socialsName],
            [
                'page' => 'general',
                'updated_at' => time(),
            ]
        );

        SettingTranslation::updateOrCreate(
            [
                'setting_id' => $settings->id,
                'locale' => mb_strtolower($locale)
            ],
            [
                'value' => json_encode($values),
            ]
        );

        cache()->forget('settings.' . Setting::$socialsName);

        return redirect(getAdminPanelUrl() . '/settings/general');
    }

    public function storeCustomCssJs(Request $request)
    {
        $this->authorize('admin_settings_customization');

        $newValues = $request->get('value', null);
        $locale = $request->get('locale', Setting::$defaultSettingsLocale);
        $values = [];
        $settings = Setting::where('name', Setting::$customCssJsName)->first();

        if (!empty($settings) and !empty($settings->value)) {
            $values = json_decode($settings->value);
        }

        if (!empty($newValues) and !empty($values)) {
            foreach ($newValues as $newKey => $newValue) {
                foreach ($values as $key => $value) {
                    if ($key == $newKey) {
                        $values->$key = $newValue;
                        unset($newValues[$key]);
                    }
                }
            }
        }

        if (!empty($newValues)) {
            $values = array_merge((array)$values, $newValues);
        }

        if (!empty($values)) {
            $values = json_encode($values);

            $settings = Setting::updateOrCreate(
                ['name' => Setting::$customCssJsName],
                [
                    'page' => 'customization',
                    'updated_at' => time(),
                ]
            );

            SettingTranslation::updateOrCreate(
                [
                    'setting_id' => $settings->id,
                    'locale' => mb_strtolower($locale)
                ],
                [
                    'value' => $values,
                ]
            );

            cache()->forget('settings.' . Setting::$customCssJsName);

            return back();
        }
    }

    public function notificationsMetas(Request $request)
    {
        $this->authorize('admin_settings_notifications');
        $name = 'notifications';
        $values = $request->get('value', []);
        $locale = $request->get('locale', Setting::$defaultSettingsLocale);

        $settings = Setting::where('name', $name)->first();

        if (!empty($settings) and !empty($settings->value)) {
            $oldValues = json_decode($settings->value, true);

            $values = array_merge($oldValues, $values);
        }

        if (!empty($values)) {
            $values = array_filter($values);
            $values = json_encode($values);

            $settings = Setting::updateOrCreate(
                ['name' => $name],
                [
                    'page' => 'notifications',
                    'updated_at' => time(),
                ]
            );

            SettingTranslation::updateOrCreate(
                [
                    'setting_id' => $settings->id,
                    'locale' => mb_strtolower($locale)
                ],
                [
                    'value' => $values,
                ]
            );

            cache()->forget('settings.' . $name);
        }

        return back();
    }
}
