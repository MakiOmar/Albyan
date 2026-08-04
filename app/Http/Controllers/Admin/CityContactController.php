<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CityContactController extends Controller
{
    public function index(Request $request)
    {
        $locale = mb_strtolower($request->get('locale', getDefaultLocale()));

        $userLanguages = getGeneralSettings('user_languages');
        $userLanguages = !empty($userLanguages) && is_array($userLanguages) ? getLanguages($userLanguages) : [];

        // Admin list shows all cities; resolve name/address for selected locale (no fallback so empty = needs translation)
        $rawCities = getAllCityContacts($locale, false);
        $cities = $rawCities->map(function ($city, $index) use ($locale) {
            $localized = localizeCityContactCity($city, $locale, false);
            // Keep raw maps for edit modal when content_translate is on
            $localized['_raw_name'] = $city['name'] ?? '';
            $localized['_raw_address'] = $city['address'] ?? '';
            $localized['_index'] = $index;

            return $localized;
        });

        $formConfig = getLocalizedCityContactSection('form', $locale, false);
        $emailConfig = getLocalizedCityContactSection('email', $locale, false);

        return view('admin.city_contact.index', compact(
            'cities',
            'formConfig',
            'emailConfig',
            'userLanguages',
            'locale'
        ));
    }

    public function updateConfig(Request $request)
    {
        $locale = mb_strtolower($request->get('locale', getDefaultLocale()));

        $rules = [
            'locale' => 'nullable|string|max:10',
        ];

        if ($request->has('form')) {
            $rules = array_merge($rules, [
                'form.title' => 'required|string|max:255',
                'form.description' => 'required|string',
                'form.success_message' => 'required|string',
                'form.error_message' => 'required|string',
            ]);
        }

        if ($request->has('email')) {
            $rules = array_merge($rules, [
                'email.subject' => 'required|string|max:255',
                'email.template' => 'required|string|max:255',
            ]);
        }

        $request->validate($rules);

        $config = getCityContactConfig() ?? getCityContactDefaultConfig();

        if ($request->has('form')) {
            $config['form'] = $config['form'] ?? [];
            $config['form']['title'] = setCityContactLocaleValue($config['form']['title'] ?? '', $locale, $request->input('form.title'));
            $config['form']['description'] = setCityContactLocaleValue($config['form']['description'] ?? '', $locale, $request->input('form.description'));
            $config['form']['success_message'] = setCityContactLocaleValue($config['form']['success_message'] ?? '', $locale, $request->input('form.success_message'));
            $config['form']['error_message'] = setCityContactLocaleValue($config['form']['error_message'] ?? '', $locale, $request->input('form.error_message'));
        }

        if ($request->has('email')) {
            $config['email'] = $config['email'] ?? [];
            $config['email']['subject'] = setCityContactLocaleValue($config['email']['subject'] ?? '', $locale, $request->input('email.subject'));
            $config['email']['template'] = $request->input('email.template');
        }

        saveCityContactConfig($config);

        return redirect()
            ->route('admin.city-contact.index', ['locale' => $locale])
            ->with('success', 'تم تحديث الإعدادات بنجاح');
    }

    public function addCity(Request $request)
    {
        $locale = mb_strtolower($request->get('locale', getDefaultLocale()));

        $request->validate([
            'locale' => 'nullable|string|max:10',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'flag' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string|max:500',
        ]);

        $config = getCityContactConfig() ?? getCityContactDefaultConfig();

        $existingSlugs = collect($config['cities'] ?? [])->pluck('slug')->toArray();
        if (in_array($request->input('slug'), $existingSlugs)) {
            return redirect()
                ->route('admin.city-contact.index', ['locale' => $locale])
                ->with('error', 'الرابط مستخدم بالفعل');
        }

        $newCity = [
            'name' => setCityContactLocaleValue([], $locale, $request->input('name')),
            'slug' => $request->input('slug'),
            'email' => $request->input('email'),
            'flag' => $request->input('flag') ?: null,
            'phone' => $request->input('phone') ?: null,
            'whatsapp' => $request->input('whatsapp') ?: null,
            'latitude' => $request->input('latitude') ?: null,
            'longitude' => $request->input('longitude') ?: null,
            'address' => setCityContactLocaleValue([], $locale, $request->input('address') ?: ''),
            'is_active' => true,
        ];

        $config['cities'][] = $newCity;

        saveCityContactConfig($config);

        return redirect()
            ->route('admin.city-contact.index', ['locale' => $locale])
            ->with('success', 'تم إضافة المدينة بنجاح');
    }

    public function updateCity(Request $request, $slug)
    {
        try {
            $locale = mb_strtolower($request->get('locale', getDefaultLocale()));

            $request->validate([
                'locale' => 'nullable|string|max:10',
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'flag' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:255',
                'whatsapp' => 'nullable|string|max:255',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'address' => 'nullable|string|max:500',
                'is_active' => 'nullable|in:on,1,true',
            ]);

            $config = getCityContactConfig() ?? getCityContactDefaultConfig();

            $cityIndex = null;
            foreach ($config['cities'] as $index => $city) {
                if ($city['slug'] === $slug) {
                    $cityIndex = $index;
                    break;
                }
            }

            if ($cityIndex !== null) {
                $existingSlugs = collect($config['cities'])->pluck('slug')->toArray();
                $currentSlug = $config['cities'][$cityIndex]['slug'] ?? '';
                $newSlug = $request->input('slug');

                $otherSlugs = array_filter($existingSlugs, function ($existingSlug) use ($currentSlug) {
                    return $existingSlug !== $currentSlug;
                });

                if (in_array($newSlug, $otherSlugs)) {
                    return redirect()
                        ->route('admin.city-contact.index', ['locale' => $locale])
                        ->with('error', 'الرابط مستخدم بالفعل');
                }

                $config['cities'][$cityIndex]['name'] = setCityContactLocaleValue(
                    $config['cities'][$cityIndex]['name'] ?? '',
                    $locale,
                    $request->input('name')
                );
                $config['cities'][$cityIndex]['slug'] = $request->input('slug');
                $config['cities'][$cityIndex]['email'] = $request->input('email');
                $config['cities'][$cityIndex]['flag'] = $request->input('flag') ?: null;
                $config['cities'][$cityIndex]['phone'] = $request->input('phone') ?: null;
                $config['cities'][$cityIndex]['whatsapp'] = $request->input('whatsapp') ?: null;
                $config['cities'][$cityIndex]['latitude'] = $request->input('latitude') ?: null;
                $config['cities'][$cityIndex]['longitude'] = $request->input('longitude') ?: null;
                $config['cities'][$cityIndex]['address'] = setCityContactLocaleValue(
                    $config['cities'][$cityIndex]['address'] ?? '',
                    $locale,
                    $request->input('address') ?: ''
                );
                $config['cities'][$cityIndex]['is_active'] = in_array($request->input('is_active'), ['on', '1', 'true']);

                $saveResult = saveCityContactConfig($config);

                if ($saveResult === false) {
                    return redirect()
                        ->route('admin.city-contact.index', ['locale' => $locale])
                        ->with('error', 'فشل في حفظ البيانات');
                }

                return redirect()
                    ->route('admin.city-contact.index', ['locale' => $locale])
                    ->with('success', 'تم تحديث المدينة بنجاح');
            }

            return redirect()
                ->route('admin.city-contact.index', ['locale' => $locale])
                ->with('error', 'المدينة غير موجودة');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث المدينة: ' . $e->getMessage());
        }
    }

    public function deleteCity(Request $request, $index)
    {
        $locale = mb_strtolower($request->get('locale', getDefaultLocale()));
        $config = getCityContactConfig() ?? getCityContactDefaultConfig();

        if (isset($config['cities'][$index])) {
            unset($config['cities'][$index]);
            $config['cities'] = array_values($config['cities']);

            saveCityContactConfig($config);

            return redirect()
                ->route('admin.city-contact.index', ['locale' => $locale])
                ->with('success', 'تم حذف المدينة بنجاح');
        }

        return redirect()
            ->route('admin.city-contact.index', ['locale' => $locale])
            ->with('error', 'المدينة غير موجودة');
    }
}
