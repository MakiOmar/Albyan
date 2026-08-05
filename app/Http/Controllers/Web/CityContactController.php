<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Rules\AtLeastTwoWords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CityContactController extends Controller
{
    /**
     * Show the city contact form
     */
    public function showForm($citySlug)
    {
        $city = getCityBySlug($citySlug);

        if (!$city) {
            abort(404);
        }

        $formConfig = getLocalizedCityContactSection('form');

        return view('web.default.city_contact.form', [
            'city' => $city,
            'formConfig' => $formConfig,
            // Respect Admin → SEO → Contact index/noindex (was hardcoded noindex).
            'pageRobot' => getPageRobot('contact'),
            'pageTitle' => $city['name'] ?? null,
        ]);
    }

    /**
     * Handle form submission
     */
    public function submitForm(Request $request, $citySlug)
    {
        $city = getCityBySlug($citySlug);

        if (!$city) {
            abort(404);
        }

        $formConfig = getLocalizedCityContactSection('form');

        // Validate the form data
        $validator = Validator::make($request->all(), array_merge([
            'full_name' => ['required', 'string', 'max:255', new AtLeastTwoWords],
            'phone' => 'required|string|min:6|max:30',
            'email' => 'required|email|max:255',
        ], turnstile_validation_rules()), [
            'full_name.required' => 'الاسم الكامل مطلوب',
            'phone.required' => 'رقم الهاتف مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $formConfig['error_message'] ?: 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->sendContactEmail($city, $request->all());

            return response()->json([
                'success' => true,
                'message' => $formConfig['success_message'] ?: 'تم إرسال رسالتك بنجاح! سنقوم بالرد عليك قريباً.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $formConfig['error_message'] ?: 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.'
            ], 500);
        }
    }

    /**
     * Send contact email
     */
    private function sendContactEmail($city, $formData)
    {
        $emailConfig = getLocalizedCityContactSection('email');
        $subject = str_replace(
            ':city',
            $city['name'],
            $emailConfig['subject'] ?: 'رسالة جديدة من نموذج الاتصال - :city'
        );

        Mail::send($emailConfig['template'] ?: 'emails.city_contact_form', [
            'city' => $city,
            'formData' => $formData
        ], function ($message) use ($city, $subject) {
            $message->to($city['email'])
                    ->subject($subject);
        });
    }

    /**
     * Get all active cities for the floating bar
     */
    public function getActiveCities()
    {
        $cities = getActiveCities();
        return response()->json($cities);
    }

    /**
     * Get the complete JSON configuration (localized for current locale)
     */
    public function getConfig()
    {
        $config = getCityContactConfig() ?? getCityContactDefaultConfig();
        $locale = app()->getLocale();

        $config['cities'] = collect($config['cities'] ?? [])
            ->map(function ($city) use ($locale) {
                return localizeCityContactCity($city, $locale);
            })
            ->values()
            ->all();

        $config['form'] = getLocalizedCityContactSection('form', $locale);
        $config['email'] = getLocalizedCityContactSection('email', $locale);

        return response()->json($config);
    }

    /**
     * Show the cities index page
     */
    public function index()
    {
        $cities = getActiveCities();

        return view('web.default.city_contact.index', [
            'cities' => $cities,
            'pageRobot' => getPageRobot('contact'),
        ]);
    }

    /**
     * Show individual city page
     */
    public function show($slug)
    {
        $city = getCityBySlug($slug);

        if (!$city) {
            abort(404);
        }

        return view('web.default.city_contact.show', [
            'city' => $city,
            'pageRobot' => getPageRobot('contact'),
            'pageTitle' => $city['name'] ?? null,
        ]);
    }
}
