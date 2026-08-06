<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\Translation\BlogCategoryTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlogCategoriesController extends Controller
{
    public function index()
    {
        $this->authorize('admin_blog_categories');
        removeContentLocale();

        $blogCategories = BlogCategory::withCount('blog')->get();

        $data = [
            'pageTitle' => trans('admin/pages/blog.blog_categories'),
            'blogCategories' => $blogCategories
        ];

        return view('admin.blog.categories', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_blog_categories_create');

        $this->validate($request, [
            'title' => 'required|string',
        ]);

        $data = $request->all();
        $locale = mb_strtolower((string) ($data['locale'] ?? app()->getLocale()));
        $slug = BlogCategory::makeLocalizedSlug($data['title'], $locale);

        $category = new BlogCategory();
        // Parent slug is NOT NULL; bypass Astrotomic translation mapping.
        $category->attributes['slug'] = $slug;
        $category->save();

        BlogCategoryTranslation::query()->updateOrCreate([
            'blog_category_id' => $category->id,
            'locale' => $locale,
        ], [
            'title' => $data['title'],
            'slug' => $slug,
        ]);

        if ($locale === getDefaultLocaleCode()) {
            DB::table('blog_categories')->where('id', $category->id)->update(['slug' => $slug]);
        }

        return redirect(getAdminPanelUrl() . '/blog/categories');
    }

    public function edit(Request $request, $category_id)
    {
        $this->authorize('admin_blog_categories_edit');

        $editCategory = BlogCategory::findOrFail($category_id);

        $locale = $request->get('locale', app()->getLocale());
        storeContentLocale($locale, $editCategory->getTable(), $editCategory->id);

        $data = [
            'pageTitle' => trans('admin/pages/blog.blog_categories'),
            'editCategory' => $editCategory
        ];

        return view('admin.blog.categories', $data);
    }

    public function update(Request $request, $category_id)
    {
        $this->authorize('admin_blog_categories_edit');

        $this->validate($request, [
            'title' => 'required',
        ]);

        $category = BlogCategory::findOrFail($category_id);

        $data = $request->all();
        $locale = mb_strtolower((string) ($data['locale'] ?? app()->getLocale()));

        $existingSlug = BlogCategoryTranslation::query()
            ->where('blog_category_id', $category->id)
            ->where('locale', $locale)
            ->value('slug');

        $slug = !empty($existingSlug)
            ? $existingSlug
            : BlogCategory::makeLocalizedSlug($data['title'], $locale, $category->id);

        BlogCategoryTranslation::query()->updateOrCreate([
            'blog_category_id' => $category->id,
            'locale' => $locale,
        ], [
            'title' => $data['title'],
            'slug' => $slug,
        ]);

        if ($locale === getDefaultLocaleCode()) {
            DB::table('blog_categories')->where('id', $category->id)->update(['slug' => $slug]);
        }

        return redirect(getAdminPanelUrl() . '/blog/categories');
    }

    public function delete($category_id)
    {
        $this->authorize('admin_blog_categories_delete');

        $editCategory = BlogCategory::findOrFail($category_id);

        $editCategory->delete();

        return redirect(getAdminPanelUrl() . '/blog/categories');
    }
}
