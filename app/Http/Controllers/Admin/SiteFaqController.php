<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteFaq;
use App\Models\Translation\SiteFaqTranslation;
use Illuminate\Http\Request;

class SiteFaqController extends Controller
{
    public function index()
    {
        $this->authorize('admin_site_faqs_list');

        removeContentLocale();

        $siteFaqs = SiteFaq::query()->orderBy('order')->paginate(10);

        $data = [
            'pageTitle' => trans('admin/main.site_faqs'),
            'siteFaqs' => $siteFaqs,
        ];

        return view('admin.site_faqs.lists', $data);
    }

    public function create()
    {
        $this->authorize('admin_site_faqs_create');

        removeContentLocale();

        $userLanguages = getGeneralSettings('user_languages');
        $userLanguages = !empty($userLanguages) && is_array($userLanguages) ? getLanguages($userLanguages) : [];

        $data = [
            'pageTitle' => trans('admin/main.new_site_faq'),
            'userLanguages' => $userLanguages,
        ];

        return view('admin.site_faqs.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_site_faqs_create');

        $this->validate($request, [
            'title' => 'required|string|max:255',
            'answer' => 'required|string',
            'status' => 'required|in:active,disable',
        ]);

        $order = (int) SiteFaq::max('order') + 1;

        $siteFaq = SiteFaq::create([
            'order' => $order,
            'status' => $request->get('status', 'active'),
        ]);

        if ($siteFaq) {
            SiteFaqTranslation::updateOrCreate(
                [
                    'site_faq_id' => $siteFaq->id,
                    'locale' => mb_strtolower($request->get('locale', app()->getLocale())),
                ],
                [
                    'title' => $request->get('title'),
                    'answer' => $request->get('answer'),
                ]
            );
        }

        return redirect(getAdminPanelUrl() . '/site-faqs');
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_site_faqs_edit');

        $siteFaq = SiteFaq::findOrFail($id);

        $locale = $request->get('locale', app()->getLocale());
        storeContentLocale($locale, $siteFaq->getTable(), $siteFaq->id);

        $userLanguages = getGeneralSettings('user_languages');
        $userLanguages = !empty($userLanguages) && is_array($userLanguages) ? getLanguages($userLanguages) : [];

        $data = [
            'pageTitle' => trans('admin/main.edit_site_faq'),
            'siteFaq' => $siteFaq,
            'userLanguages' => $userLanguages,
        ];

        return view('admin.site_faqs.create', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_site_faqs_edit');

        $this->validate($request, [
            'title' => 'required|string|max:255',
            'answer' => 'required|string',
            'status' => 'required|in:active,disable',
        ]);

        $siteFaq = SiteFaq::findOrFail($id);

        $siteFaq->update([
            'status' => $request->get('status'),
        ]);

        SiteFaqTranslation::updateOrCreate(
            [
                'site_faq_id' => $siteFaq->id,
                'locale' => mb_strtolower($request->get('locale', app()->getLocale())),
            ],
            [
                'title' => $request->get('title'),
                'answer' => $request->get('answer'),
            ]
        );

        removeContentLocale();

        return redirect(getAdminPanelUrl() . '/site-faqs');
    }

    public function delete($id)
    {
        $this->authorize('admin_site_faqs_delete');

        $siteFaq = SiteFaq::findOrFail($id);
        $siteFaq->delete();

        return redirect(getAdminPanelUrl() . '/site-faqs');
    }
}
