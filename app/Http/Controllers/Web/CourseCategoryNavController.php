<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CourseCategoryNavController extends Controller
{
    /**
     * Public JSON feed for course category sub-navigation (WordPress, etc.).
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->filled('locale')) {
            App::setLocale($request->get('locale'));
        }

        $categories = Category::getCategories()
            ->map(function ($category) {
                $locale = app()->getLocale();

                return [
                    'id' => $category->id,
                    'title' => $category->title,
                    'slug' => $category->localizedSlug($locale),
                    'url' => $category->getUrl($locale),
                    'icon' => !empty($category->icon) ? url($category->icon) : null,
                    'order' => (int) ($category->order ?? 0),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'status' => 'retrieved',
            'message' => trans('api.public.retrieved'),
            'data' => [
                'count' => $categories->count(),
                'categories' => $categories,
            ],
        ], 200, [
            'Cache-Control' => 'public, max-age=300',
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
