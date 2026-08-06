<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\FeatureWebinar;
use App\Models\Sale;
use App\Models\Ticket;
use App\Models\Translation\CategoryTranslation;
use App\Models\Webinar;
use App\Models\WebinarFilterOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{
    /**
     * Public all-categories listing (Vitazonei-style hub).
     */
    public function all()
    {
        $categories = Category::getCategories();

        // Count active public programs per parent (including subcategories)
        $categories = $categories->map(function ($category) {
            $ids = [$category->id];
            if (!empty($category->subCategories) && $category->subCategories->count()) {
                $ids = array_merge($ids, $category->subCategories->pluck('id')->toArray());
            }
            $category->programs_count = Webinar::query()
                ->where('status', Webinar::$active)
                ->where('private', false)
                ->whereIn('category_id', $ids)
                ->count();

            return $category;
        });

        // Use categories SEO settings when present; otherwise translation fallbacks (do not write SEO)
        $seoSettings = getSeoMetas('categories');
        $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('update.all_categories_page_title');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('update.all_categories_page_hint');
        $pageRobot = getPageRobot('categories');

        return view(getTemplate() . '.pages.all_categories', [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'categories' => $categories,
        ]);
    }

    public function index(Request $request, $categoryTitle, $subCategoryTitle = null)
    {

        // Locale may be present as a route param; resolve slugs from named params.
        $categorySlug = $categoryTitle;
        $subCategorySlug = $subCategoryTitle;

        if (!empty($categorySlug)) {

            $categoryQuery = Category::query()->whereLocalizedSlug($categorySlug);

            if (!empty($subCategorySlug)) {
                $categoryQuery = Category::query()->whereLocalizedSlug($subCategorySlug);
            }

            $category = $categoryQuery->withCount('webinars')
                ->with(['filters' => function ($query) {
                    $query->with('options');
                }])->first();

            if (!empty($category)) {
                $categoryIds = [$category->id];

                if (!empty($category->subCategories) and count($category->subCategories)) {
                    $categoryIds = array_merge($categoryIds, $category->subCategories->pluck('id')->toArray());
                }

                $featureWebinars = FeatureWebinar::whereIn('page', ['categories', 'home_categories'])
                    ->where('status', 'publish')
                    ->whereHas('webinar', function ($q) use ($categoryIds) {
                        $q->where('status', Webinar::$active);
                        $q->whereHas('category', function ($q) use ($categoryIds) {
                            $q->whereIn('id', $categoryIds);
                        });
                    })
                    ->with(['webinar' => function ($query) {
                        $query->with(['teacher' => function ($qu) {
                            $qu->select('id', 'full_name', 'avatar');
                        }, 'reviews', 'tickets', 'feature']);
                    }])
                    ->orderBy('updated_at', 'desc')
                    ->get();


                $webinarsQuery = Webinar::where('webinars.status', 'active')
                    ->where('private', false)
                    ->whereIn('category_id', $categoryIds);

                $classesController = new ClassesController();
                $moreOptions = $request->get('moreOptions');
                $tableName = 'webinars';

                if (!empty($moreOptions) and is_array($moreOptions) and in_array('bundles', $moreOptions)) {
                    $webinarsQuery = Bundle::where('bundles.status', 'active')
                        ->whereIn('category_id', $categoryIds);

                    $tableName = 'bundles';
                    $classesController->tableName = 'bundles';
                    $classesController->columnId = 'bundle_id';
                }

                $webinarsQuery = $classesController->handleFilters($request, $webinarsQuery);

                $sort = $request->get('sort', null);

                if (empty($sort)) {
                    $webinarsQuery = $webinarsQuery->orderBy("{$tableName}.created_at", 'desc');
                }

                $webinars = $webinarsQuery->with(['tickets'])
                    ->paginate(6);

                $seoSettings = getSeoMetas('categories');
                $pageTitle = !empty($category->seo_title)
                    ? $category->seo_title
                    : (!empty($seoSettings['title']) ? $seoSettings['title'] : $category->title);
                $pageDescription = !empty($category->seo_description)
                    ? $category->seo_description
                    : (!empty($seoSettings['description']) ? $seoSettings['description'] : $category->title);
                $pageRobot = getPageRobot('categories');

                $data = [
                    'pageTitle' => $pageTitle,
                    'pageDescription' => $pageDescription,
                    'pageRobot' => $pageRobot,
                    'category' => $category,
                    'webinars' => $webinars,
                    'featureWebinars' => $featureWebinars,
                    'webinarsCount' => $webinars->total(),
                    'sortFormAction' => $category->getUrl(),
                ];

                return view(getTemplate() . '.pages.categories', $data);
            }
        }

        abort(404);
    }
}
