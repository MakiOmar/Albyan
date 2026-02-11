<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeSectionSettingsController extends Controller
{
    public function index()
    {
        $this->authorize('admin_settings_personalization');

        removeContentLocale();

        $sections = HomeSection::with('category')->orderBy('order', 'asc')->get();
        $selectedSectionsName = $sections->pluck('name')->toArray();

        $data = [
            'pageTitle' => trans('admin/main.home_sections'),
            'sections' => $sections,
            'selectedSectionsName' => $selectedSectionsName,
            'name' => 'home_sections'
        ];

        return view('admin.settings.personalization', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_settings_personalization');

        $rules = ['name' => 'required'];
        $attributes = [];

        if ($request->get('name') === HomeSection::$category_courses) {
            $rules['category_id'] = 'required|exists:categories,id';
            $rules['category_courses_mode'] = 'nullable|in:recent,specific';
            if ($request->get('category_courses_mode') === 'specific') {
                $rules['category_courses_webinar_ids'] = 'nullable|array';
                $rules['category_courses_webinar_ids.*'] = 'exists:webinars,id';
            }
        }

        $validated = $request->validate($rules);

        if ($request->get('name') === HomeSection::$category_courses) {
            $categoryId = (int) $request->get('category_id');
            $mode = $request->get('category_courses_mode', 'recent');
            $webinarIds = $request->get('category_courses_webinar_ids', []);

            if ($mode === 'specific' && !empty($webinarIds)) {
                $webinarIds = array_map('intval', (array) $webinarIds);
                $validIds = Webinar::whereIn('id', $webinarIds)
                    ->where('category_id', $categoryId)
                    ->pluck('id')
                    ->toArray();
                $webinarIds = array_values(array_intersect($webinarIds, $validIds));
            } else {
                $webinarIds = [];
            }

            $value = $mode === 'specific' ? ['mode' => 'specific', 'webinar_ids' => $webinarIds] : ['mode' => 'recent'];

            HomeSection::create([
                'name' => HomeSection::$category_courses,
                'order' => HomeSection::query()->count() + 1,
                'category_id' => $categoryId,
                'value' => $value,
            ]);
        } else {
            HomeSection::updateOrCreate(
                ['name' => $request->get('name')],
                ['order' => HomeSection::query()->count() + 1]
            );
        }

        return redirect()->back();
    }

    public function delete($id)
    {
        $this->authorize('admin_settings_personalization');

        $section = HomeSection::findOrFail($id);

        $section->delete();

        $allSections = HomeSection::orderBy('order', 'asc')->get();

        $order = 1;
        foreach ($allSections as $allSection) {
            $allSection->update([
                'order' => $order
            ]);

            $order += 1;
        }

        return redirect()->back();
    }

    public function sort(Request $request)
    {
        $this->authorize('admin_settings_personalization');

        $data = $request->all();

        $validator = Validator::make($data, [
            'items' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $itemIds = explode(',', $data['items']);

        foreach ($itemIds as $order => $id) {
            HomeSection::where('id', $id)
                ->update(['order' => ($order + 1)]);
        }

        return response()->json([
            'title' => trans('public.request_success'),
            'msg' => trans('update.items_sorted_successful')
        ]);
    }
}
