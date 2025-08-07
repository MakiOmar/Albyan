<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CityContactController extends Controller
{
    public function index()
    {
        $cities = getActiveCities();
        
        return view('web.default.city_contact.index', compact('cities'));
    }

    public function show($slug)
    {
        $config = getCityContactConfig();
        $city = null;
        
        if (!empty($config['cities'])) {
            foreach ($config['cities'] as $cityData) {
                if ($cityData['slug'] === $slug && $cityData['is_active']) {
                    $city = $cityData;
                    break;
                }
            }
        }
        
        if (!$city) {
            abort(404);
        }
        
        $formConfig = getCityContactConfig('form') ?? [
            'title' => 'تواصل معنا',
            'description' => 'يرجى ملء النموذج أدناه وسنقوم بالرد عليك في أقرب وقت ممكن',
            'success_message' => 'تم إرسال رسالتك بنجاح! سنقوم بالرد عليك قريباً.',
            'error_message' => 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.'
        ];
        
        return view('web.default.city_contact.show', compact('city', 'formConfig'));
    }

    public function getConfig()
    {
        $config = getCityContactConfig();
        return response()->json($config);
    }
}
