<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CityContactController extends Controller
{
    public function index()
    {
        $cities = getActiveCities();
        $formConfig = getCityContactConfig('form') ?? [
            'title' => 'تواصل معنا',
            'description' => 'يرجى ملء النموذج أدناه وسنقوم بالرد عليك في أقرب وقت ممكن',
            'success_message' => 'تم إرسال رسالتك بنجاح! سنقوم بالرد عليك قريباً.',
            'error_message' => 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.'
        ];
        $emailConfig = getCityContactConfig('email') ?? [
            'subject' => 'رسالة جديدة من نموذج الاتصال - :city',
            'template' => 'emails.city_contact_form'
        ];
        
        return view('admin.city_contact.index', compact('cities', 'formConfig', 'emailConfig'));
    }

    public function updateConfig(Request $request)
    {
        $request->validate([
            'form.title' => 'required|string|max:255',
            'form.description' => 'required|string',
            'form.success_message' => 'required|string',
            'form.error_message' => 'required|string',
            'email.subject' => 'required|string|max:255',
            'email.template' => 'required|string|max:255',
        ]);

        $config = getCityContactConfig() ?? [
            'cities' => [],
            'form' => [
                'title' => 'تواصل معنا',
                'description' => 'يرجى ملء النموذج أدناه وسنقوم بالرد عليك في أقرب وقت ممكن',
                'success_message' => 'تم إرسال رسالتك بنجاح! سنقوم بالرد عليك قريباً.',
                'error_message' => 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.'
            ],
            'email' => [
                'subject' => 'رسالة جديدة من نموذج الاتصال - :city',
                'template' => 'emails.city_contact_form'
            ]
        ];

        // Update form settings
        $config['form']['title'] = $request->input('form.title');
        $config['form']['description'] = $request->input('form.description');
        $config['form']['success_message'] = $request->input('form.success_message');
        $config['form']['error_message'] = $request->input('form.error_message');

        // Update email settings
        $config['email']['subject'] = $request->input('email.subject');
        $config['email']['template'] = $request->input('email.template');

        // Save to JSON file
        saveCityContactConfig($config);

        return redirect()->back()->with('success', 'تم تحديث الإعدادات بنجاح');
    }

    public function addCity(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'flag' => 'nullable|string|max:255',
        ]);

        $config = getCityContactConfig() ?? [
            'cities' => [],
            'form' => [
                'title' => 'تواصل معنا',
                'description' => 'يرجى ملء النموذج أدناه وسنقوم بالرد عليك في أقرب وقت ممكن',
                'success_message' => 'تم إرسال رسالتك بنجاح! سنقوم بالرد عليك قريباً.',
                'error_message' => 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.'
            ],
            'email' => [
                'subject' => 'رسالة جديدة من نموذج الاتصال - :city',
                'template' => 'emails.city_contact_form'
            ]
        ];

        // Check if slug already exists
        $existingSlugs = collect($config['cities'])->pluck('slug')->toArray();
        if (in_array($request->input('slug'), $existingSlugs)) {
            return redirect()->back()->with('error', 'الرابط مستخدم بالفعل');
        }

        $newCity = [
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'email' => $request->input('email'),
            'flag' => $request->input('flag'),
            'is_active' => true,
        ];

        $config['cities'][] = $newCity;

        saveCityContactConfig($config);

        return redirect()->back()->with('success', 'تم إضافة المدينة بنجاح');
    }

    public function updateCity(Request $request, $slug)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'flag' => 'nullable|string|max:255',
                'is_active' => 'nullable|in:on,1,true',
            ]);

            $config = getCityContactConfig() ?? [
                'cities' => [],
                'form' => [
                    'title' => 'تواصل معنا',
                    'description' => 'يرجى ملء النموذج أدناه وسنقوم بالرد عليك في أقرب وقت ممكن',
                    'success_message' => 'تم إرسال رسالتك بنجاح! سنقوم بالرد عليك قريباً.',
                    'error_message' => 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.'
                ],
                'email' => [
                    'subject' => 'رسالة جديدة من نموذج الاتصال - :city',
                    'template' => 'emails.city_contact_form'
                ]
            ];

            // Find city by slug
            $cityIndex = null;
            foreach ($config['cities'] as $index => $city) {
                if ($city['slug'] === $slug) {
                    $cityIndex = $index;
                    break;
                }
            }

            if ($cityIndex !== null) {
                // Check if slug already exists (excluding current city)
                $existingSlugs = collect($config['cities'])->pluck('slug')->toArray();
                $currentSlug = $config['cities'][$cityIndex]['slug'] ?? '';
                $newSlug = $request->input('slug');

                // Remove current city's slug from the check (allow keeping the same slug)
                $otherSlugs = array_filter($existingSlugs, function($slug) use ($currentSlug) {
                    return $slug !== $currentSlug;
                });

                if (in_array($newSlug, $otherSlugs)) {
                    return redirect()->back()->with('error', 'الرابط مستخدم بالفعل');
                }

                $config['cities'][$cityIndex]['name'] = $request->input('name');
                $config['cities'][$cityIndex]['slug'] = $request->input('slug');
                $config['cities'][$cityIndex]['email'] = $request->input('email');
                $config['cities'][$cityIndex]['flag'] = $request->input('flag');
                $config['cities'][$cityIndex]['is_active'] = in_array($request->input('is_active'), ['on', '1', 'true']);

                $saveResult = saveCityContactConfig($config);

                if ($saveResult === false) {
                    return redirect()->back()->with('error', 'فشل في حفظ البيانات');
                }

                return redirect()->back()->with('success', 'تم تحديث المدينة بنجاح');
            }

            return redirect()->back()->with('error', 'المدينة غير موجودة');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث المدينة: ' . $e->getMessage());
        }
    }

    public function deleteCity($index)
    {
        $config = getCityContactConfig() ?? [
            'cities' => [],
            'form' => [
                'title' => 'تواصل معنا',
                'description' => 'يرجى ملء النموذج أدناه وسنقوم بالرد عليك في أقرب وقت ممكن',
                'success_message' => 'تم إرسال رسالتك بنجاح! سنقوم بالرد عليك قريباً.',
                'error_message' => 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.'
            ],
            'email' => [
                'subject' => 'رسالة جديدة من نموذج الاتصال - :city',
                'template' => 'emails.city_contact_form'
            ]
        ];

        if (isset($config['cities'][$index])) {
            unset($config['cities'][$index]);
            $config['cities'] = array_values($config['cities']); // Re-index array

            saveCityContactConfig($config);

            return redirect()->back()->with('success', 'تم حذف المدينة بنجاح');
        }

        return redirect()->back()->with('error', 'المدينة غير موجودة');
    }
} 