<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Rules\AtLeastTwoWords;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $contactSettings = getContactPageSettings();

        $seoSettings = getSeoMetas('contact');
        $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('site.contact_page_title');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('site.contact_page_title');
        $pageRobot = getPageRobot('contact');

        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'contactSettings' => $contactSettings
        ];

        return view('web.default.pages.contact', $data);
    }

    public function store(Request $request)
    {
        $rules = array_merge([
            'name' => ['required', 'string', 'max:255', new AtLeastTwoWords],
            'email' => 'required|email|max:255',
            'phone' => 'required|string|min:6|max:40',
            'subject' => 'required|string|min:2|max:255',
            'message' => 'required|string|min:100|max:10000',
        ], turnstile_validation_rules());

        $data = $request->validate($rules);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'created_at' => time(),
        ];

        Contact::create($payload);

        $notifyOptions = [
            '[c.u.title]' => $payload['subject'],
            '[u.name]' => $payload['name'],
            '[time.date]' => dateTimeFormat(time(), 'j M Y H:i'),
            '[c.u.message]' => $payload['message'],
        ];

        sendNotification('contact_message_submission_for_admin', $notifyOptions, 1);

        sendNotificationToEmail('contact_message_submission', $notifyOptions, $payload['email']);

        return back()->with(['msg' => trans('site.contact_store_success')]);
    }
}
