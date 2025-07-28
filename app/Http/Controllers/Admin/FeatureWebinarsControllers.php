<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FeatureWebinarsExport;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FeatureWebinar;
use App\Models\Translation\FeatureWebinarTranslation;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FeatureWebinarsControllers extends Controller
{
    public function index(Request $request)
{
    $this->authorize('admin_feature_webinars');

    removeContentLocale();

    $categories = Category::whereNull('parent_id')
        ->with('subCategories')
        ->get();

    // Base query with eager loading
    $query = FeatureWebinar::with([
        'webinar' => function ($query) {
            $query->select(['id', 'teacher_id', 'category_id', 'slug', 'status'])
                ->with([
                    'category',
                    'teacher' => function ($query) {
                        $query->select(['id', 'full_name']);
                    }
                ]);
        }
    ]);

    // Apply filters with debug logging
    try {
        $query = $this->filters($query, $request);
    } catch (\Throwable $e) {
        \Log::error('Error in filters(): ' . $e->getMessage());
    }

    // Paginate with query string preserved
    $features = $query->orderBy('updated_at', 'desc')
        ->paginate(10)
        ->appends($request->query());

    // Optional: dump to test live data
    // dd($features->currentPage(), $features->lastPage(), $features->total(), $features->items());

    $data = [
        'pageTitle' => trans('admin/pages/webinars.feature_webinars'),
        'categories' => $categories,
        'features' => $features,
    ];

    return view('admin.webinars.feature.lists', $data);
}


    private function filters($query, $request)
{
    // Do NOT read page here, it's for pagination only
    $status = $request->get('status', null);
    $category_id = $request->get('category_id', null);
    $webinar_title = $request->get('webinar_title', null);

    if (!empty($status)) {
        $query->where('status', $status);
    }

    if (!empty($category_id)) {
        $query->whereHas('webinar', function ($q) use ($category_id) {
            $q->whereHas('category', function ($q) use ($category_id) {
                $q->where('id', $category_id);
            });
        });
    }

    if (!empty($webinar_title)) {
        $query->whereHas('webinar', function ($q) use ($webinar_title) {
            $q->whereTranslationLike('title', '%' . $webinar_title . '%');
        });
    }

    return $query;
}


    public function create()
    {
        $this->authorize('admin_feature_webinars_create');

        removeContentLocale();

        $data = [
            'pageTitle' => trans('public.create') . ' ' . trans('admin/pages/webinars.feature_webinars'),
        ];

        return view('admin.webinars.feature.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_feature_webinars_create');

        $this->validate($request, [
            'webinar_id' => 'required|unique:feature_webinars,webinar_id'
        ]);

        $data = $request->all();

        $feature = FeatureWebinar::create([
            'webinar_id' => $data['webinar_id'],
            'page' => $data['page'],
            'status' => $data['status'] ?? 'pending',
            'updated_at' => time()
        ]);

        if (!empty($feature)) {
            FeatureWebinarTranslation::updateOrCreate([
                'feature_webinar_id' => $feature->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'description' => $data['description'],
            ]);
        }

        return redirect(getAdminPanelUrl().'/webinars/features');
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_feature_webinars_create');

        $feature = FeatureWebinar::findOrFail($id);

        $locale = $request->get('locale', app()->getLocale());
        storeContentLocale($locale, $feature->getTable(), $feature->id);

        $data = [
            'pageTitle' => trans('public.edit') . ' ' . trans('admin/pages/webinars.feature_webinars'),
            'feature' => $feature
        ];

        return view('admin.webinars.feature.create', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_feature_webinars_create');

        $this->validate($request, [
            'webinar_id' => 'required|unique:feature_webinars,webinar_id,' . $id,
        ]);

        $data = $request->all();
        $feature = FeatureWebinar::findOrFail($id);

        $feature->update([
            'webinar_id' => $data['webinar_id'],
            'page' => $data['page'],
            'status' => $data['status'] ?? 'pending',
            'updated_at' => time()
        ]);

        FeatureWebinarTranslation::updateOrCreate([
            'feature_webinar_id' => $feature->id,
            'locale' => mb_strtolower($data['locale']),
        ], [
            'description' => $data['description'],
        ]);

        removeContentLocale();

        return back();
    }

    public function toggle($feature_id, $toggle)
    {
        $this->authorize('admin_feature_webinars');

        $feature = FeatureWebinar::findOrFail($feature_id);

        if (in_array($toggle, ['pending', 'publish'])) {
            $feature->update([
                'status' => $toggle
            ]);
        } elseif ($toggle == 'delete') {
            $feature->delete();
        }

        return back();
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('admin_feature_webinars_export_excel');

        $query = FeatureWebinar::with([
            'webinar' => function ($query) {
                $query->select(['id', 'teacher_id', 'category_id', 'title', 'slug', 'status'])
                    ->with(['category', 'teacher' => function ($query) {
                        $query->select(['id', 'full_name']);
                    }]);
            }
        ]);

        $query = $this->filters($query, $request);

        $features = $query->orderBy('updated_at', 'desc')
            ->get();

        $export = new FeatureWebinarsExport($features);

        return Excel::download($export, 'feature_webinars.xlsx');
    }
}
