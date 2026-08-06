<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\Product;
use App\Models\UpcomingCourse;
use App\Models\Webinar;
use Illuminate\Http\Request;

/**
 * 301 unprefixed front URLs into /{locale}/...
 */
class LegacyLocaleRedirectController extends Controller
{
    public function __invoke(Request $request)
    {
        $path = '/' . ltrim($request->path(), '/');
        $locale = $this->resolveLocale($path);

        $target = localizedPath($path, $locale);
        $query = $request->getQueryString();
        if (!empty($query)) {
            $target .= '?' . $query;
        }

        return redirect($target, 301);
    }

    private function resolveLocale(string $path): string
    {
        $segments = array_values(array_filter(explode('/', $path)));
        if (empty($segments)) {
            return getDefaultLocaleCode();
        }

        $map = [
            'course' => [1, Webinar::class],
            'categories' => [1, Category::class],
            'blog' => [1, Blog::class],
            'products' => [1, Product::class],
            'bundles' => [1, Bundle::class],
            'upcoming_courses' => [1, UpcomingCourse::class],
        ];

        $first = mb_strtolower($segments[0]);

        // /blog/categories/{slug}
        if ($first === 'blog' && isset($segments[1]) && $segments[1] === 'categories' && !empty($segments[2])) {
            return resolveLocaleForContentSlug(BlogCategory::class, $segments[2]);
        }

        // /course/learning/{slug}
        if ($first === 'course' && isset($segments[1]) && $segments[1] === 'learning' && !empty($segments[2])) {
            return resolveLocaleForContentSlug(Webinar::class, $segments[2]);
        }

        if (isset($map[$first]) && !empty($segments[$map[$first][0]])) {
            $slug = $segments[$map[$first][0]];
            // Skip non-slug list paths like /blog, /products, /categories
            if (!in_array($slug, ['categories', 'learning'], true)) {
                return resolveLocaleForContentSlug($map[$first][1], $slug);
            }
        }

        // Nested category: /categories/{parent}/{child}
        if ($first === 'categories' && !empty($segments[2])) {
            return resolveLocaleForContentSlug(Category::class, $segments[2]);
        }

        return getDefaultLocaleCode();
    }
}
