<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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

        $formConfig = getCityContactConfig('form') ?? [
            'title' => 'تواصل معنا',
            'description' => 'يرجى ملء النموذج أدناه وسنقوم بالرد عليك في أقرب وقت ممكن',
            'success_message' => 'تم إرسال رسالتك بنجاح! سنقوم بالرد عليك قريباً.',
            'error_message' => 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.'
        ];

        return view('web.default.city_contact.form', compact('city', 'formConfig'));
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

        // Validate the form data
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
        ], [
            'full_name.required' => 'الاسم الكامل مطلوب',
            'phone.required' => 'رقم الهاتف مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => getCityContactConfig('form.error_message') ?? 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Send email
            $this->sendContactEmail($city, $request->all());

            return response()->json([
                'success' => true,
                'message' => getCityContactConfig('form.success_message') ?? 'تم إرسال رسالتك بنجاح! سنقوم بالرد عليك قريباً.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => getCityContactConfig('form.error_message') ?? 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.'
            ], 500);
        }
    }

    /**
     * Send contact email
     */
    private function sendContactEmail($city, $formData)
    {
        $subject = str_replace(':city', $city['name'], getCityContactConfig('email.subject') ?? 'رسالة جديدة من نموذج الاتصال - :city');
        
        Mail::send(getCityContactConfig('email.template') ?? 'emails.city_contact_form', [
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
     * Get the complete JSON configuration
     */
    public function getConfig()
    {
        $config = getCityContactConfig();
        return response()->json($config);
    }
} 