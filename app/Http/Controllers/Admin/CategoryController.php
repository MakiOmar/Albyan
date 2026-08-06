<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Translation\CategoryTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        removeContentLocale();

        $this->authorize('admin_categories_list');

        $categories = Category::where('parent_id', null)
            ->with([
                'subCategories'
            ])
            ->orderBy('id', 'desc')
            ->paginate(10);

        $data = [
            'pageTitle' => trans('admin/pages/categories.categories_list_page_title'),
            'categories' => $categories
        ];

        return view('admin.categories.lists', $data);
    }

    public function create()
    {
        $this->authorize('admin_categories_create');


        $data = [
            'pageTitle' => trans('admin/main.category_new_page_title'),
        ];

        return view('admin.categories.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_categories_create');

        $locale = mb_strtolower((string) $request->input('locale', app()->getLocale()));

        $this->validate($request, [
            'title' => 'required|min:3|max:128',
            'slug' => [
                'nullable',
                'max:255',
                Rule::unique('category_translations', 'slug')->where(fn ($q) => $q->where('locale', $locale)),
            ],
            'description' => 'nullable|string|max:1000',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
        ]);

        $data = $request->all();

        if (!empty($data['order'])) {
            $order = $data['order'];
        } else {
            $order = Category::whereNull('parent_id')->count() + 1;
        }

        $slug = !empty($data['slug'])
            ? $data['slug']
            : Category::makeLocalizedSlug($data['title'], $locale);

        // Do not put slug in Eloquent fill — it is a translated attribute (Astrotomic).
        $category = new Category();
        $category->icon = !empty($data['icon']) ? $data['icon'] : null;
        $category->order = $order;
        // Parent column is NOT NULL; write via attributes to bypass Astrotomic translation mapping.
        $category->attributes['slug'] = $slug;
        $category->save();

        CategoryTranslation::updateOrCreate([
            'category_id' => $category->id,
            'locale' => $locale,
        ], [
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
        ]);

        // Mirror parent slug only for the default site locale.
        if ($locale === getDefaultLocaleCode()) {
            DB::table('categories')->where('id', $category->id)->update(['slug' => $slug]);
        }

        $hasSubCategories = (!empty($request->get('has_sub')) and $request->get('has_sub') == 'on');
        $this->setSubCategory($category, $request->get('sub_categories'), $hasSubCategories, $locale);

        cache()->forget(Category::$cacheKey);

        removeContentLocale();

        return redirect(getAdminPanelUrl() . '/categories');
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_categories_edit');

        $category = Category::findOrFail($id);
        $subCategories = Category::where('parent_id', $category->id)
            ->orderBy('order', 'asc')
            ->get();

        $locale = $request->get('locale', app()->getLocale());
        storeContentLocale($locale, $category->getTable(), $category->id);

        $data = [
            'pageTitle' => trans('admin/pages/categories.edit_page_title'),
            'category' => $category,
            'subCategories' => $subCategories
        ];

        return view('admin.categories.create', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_categories_edit');

        $category = Category::findOrFail($id);
        $locale = mb_strtolower((string) $request->input('locale', app()->getLocale()));

        $this->validate($request, [
            'title' => 'required|min:3|max:255',
            'slug' => [
                'nullable',
                'max:255',
                Rule::unique('category_translations', 'slug')
                    ->where(fn ($q) => $q->where('locale', $locale))
                    ->ignore($category->id, 'category_id'),
            ],
            'description' => 'nullable|string|max:1000',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
        ]);

        $data = $request->all();

        $slug = !empty($data['slug'])
            ? $data['slug']
            : Category::makeLocalizedSlug($data['title'], $locale, $category->id);

        // Never put slug in Eloquent update — Astrotomic would write the wrong locale.
        $category->update([
            'icon' => !empty($data['icon']) ? $data['icon'] : null,
            'order' => $data['order'] ?? $category->order,
        ]);

        CategoryTranslation::updateOrCreate([
            'category_id' => $category->id,
            'locale' => $locale,
        ], [
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
        ]);

        if ($locale === getDefaultLocaleCode()) {
            DB::table('categories')->where('id', $category->id)->update(['slug' => $slug]);
        }

        $hasSubCategories = (!empty($request->get('has_sub')) and $request->get('has_sub') == 'on');
        $this->setSubCategory($category, $request->get('sub_categories'), $hasSubCategories, $locale);


        cache()->forget(Category::$cacheKey);

        removeContentLocale();

        return redirect(getAdminPanelUrl() . '/categories');
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_categories_delete');

        $category = Category::where('id', $id)->first();
        $parent = !empty($category->parent_id) ? $category->parent_id : null;

        if (!empty($category)) {
            Category::where('parent_id', $category->id)
                ->delete();

            $category->delete();
        }

        cache()->forget(Category::$cacheKey);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => !empty($parent) ? trans('update.sub_category_successfully_deleted') : trans('update.category_successfully_deleted'),
            'status' => 'success'
        ];

        return !empty($parent) ? back()->with(['toast' => $toastData]) : redirect(getAdminPanelUrl() . '/categories')->with(['toast' => $toastData]);
    }

    public function search(Request $request)
    {
        $term = $request->get('term');

        $option = $request->get('option', null);

        $query = Category::whereTranslationLike('title', "%$term%");

        /*if (!empty($option)) {

        }*/

        $categories = $query->get();

        // Return id and title for Select2 and other consumers
        $result = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'title' => $category->title,
            ];
        });

        return response()->json($result->values(), 200);
    }

    public function setSubCategory(Category $category, $subCategories, $hasSubCategories, $locale)
    {
        $order = 1;
        $oldIds = [];
        $locale = mb_strtolower((string) $locale);
        $defaultLocale = getDefaultLocaleCode();

        if ($hasSubCategories and !empty($subCategories) and count($subCategories)) {
            foreach ($subCategories as $key => $subCategory) {
                $check = Category::where('id', $key)->first();

                if (is_numeric($key)) {
                    $oldIds[] = $key;
                }

                if (!empty($subCategory['title'])) {
                    $exceptId = !empty($check) ? $check->id : null;
                    $requestedSlug = !empty($subCategory['slug']) ? $subCategory['slug'] : null;

                    if (!empty($requestedSlug) && !Category::localizedSlugExists($requestedSlug, $locale, $exceptId)) {
                        $slug = $requestedSlug;
                    } else {
                        $slug = Category::makeLocalizedSlug($subCategory['title'], $locale, $exceptId);
                    }

                    if (!empty($check)) {
                        $check->update([
                            'order' => $order,
                            'icon' => $subCategory['icon'] ?? null,
                        ]);

                        CategoryTranslation::updateOrCreate([
                            'category_id' => $check->id,
                            'locale' => $locale,
                        ], [
                            'title' => $subCategory['title'],
                            'slug' => $slug,
                        ]);

                        if ($locale === $defaultLocale) {
                            DB::table('categories')->where('id', $check->id)->update(['slug' => $slug]);
                        }
                    } else {
                        $new = new Category();
                        $new->parent_id = $category->id;
                        $new->icon = $subCategory['icon'] ?? null;
                        $new->order = $order;
                        // Parent column is NOT NULL; bypass Astrotomic for the physical slug column.
                        $new->attributes['slug'] = $slug;
                        $new->save();

                        CategoryTranslation::updateOrCreate([
                            'category_id' => $new->id,
                            'locale' => $locale,
                        ], [
                            'title' => $subCategory['title'],
                            'slug' => $slug,
                        ]);

                        if ($locale === $defaultLocale) {
                            DB::table('categories')->where('id', $new->id)->update(['slug' => $slug]);
                        }

                        $oldIds[] = $new->id;
                    }

                    $order += 1;
                }
            }
        }

        Category::where('parent_id', $category->id)
            ->whereNotIn('id', $oldIds)
            ->delete();

        return true;
    }
}
