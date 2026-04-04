<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Webinar;
use App\Models\UpcomingCourse;
use App\Models\Category;
use App\Models\BlogCategory;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /** Google sitemap protocol: max URLs per urlset file */
    private const MAX_URLS_PER_SITEMAP = 50000;

    /** Max child sitemap entries in one sitemap index file */
    private const MAX_SITEMAPS_PER_INDEX = 50000;

    /**
     * Legacy monolithic sitemap (all URLs in one urlset). Kept for backward compatibility.
     */
    public function index()
    {
        try {
            $xml = Cache::remember('sitemap.xml', 86400, function () {
                $urls = [];

                $urls = array_merge($urls, $this->getStaticPages());
                $urls = array_merge($urls, $this->getPublishedCourses());
                $urls = array_merge($urls, $this->getPublishedBlogPosts());
                $urls = array_merge($urls, $this->getPublishedUpcomingCourses());
                $urls = array_merge($urls, $this->getCategories());
                $urls = array_merge($urls, $this->getBlogCategories());
                $urls = array_merge($urls, $this->getInstructors());

                return $this->generateUrlsetXml($urls);
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Sitemap generation error: ' . $e->getMessage());
            return response($this->generateErrorXml($e->getMessage()), 500)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        }
    }

    /**
     * Master sitemap index — lists all child sitemaps (recommended for crawlers).
     */
    public function sitemapIndexMain()
    {
        try {
            $xml = Cache::remember('sitemap_index.xml', 86400, function () {
                $entries = $this->buildMasterSitemapIndexEntries();
                $chunks = array_chunk($entries, self::MAX_SITEMAPS_PER_INDEX);
                // Single index file expected for normal sites; spec allows splitting if ever needed
                return $this->generateSitemapIndexXml($chunks[0] ?? []);
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Sitemap index generation error: ' . $e->getMessage());
            return response($this->generateErrorXml($e->getMessage()), 500)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        }
    }

    public function pages()
    {
        try {
            $xml = Cache::remember('sitemap-pages.xml', 86400, function () {
                return $this->generateUrlsetXml($this->getStaticPages());
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Sitemap pages generation error: ' . $e->getMessage());
            return response($this->generateErrorXml($e->getMessage()), 500)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        }
    }

    public function categoriesSitemap()
    {
        try {
            $xml = Cache::remember('sitemap-categories.xml', 86400, function () {
                return $this->generateUrlsetXml($this->getCategories());
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Sitemap categories generation error: ' . $e->getMessage());
            return response($this->generateErrorXml($e->getMessage()), 500)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        }
    }

    public function blogCategoriesSitemap()
    {
        try {
            $xml = Cache::remember('sitemap-blog-categories.xml', 86400, function () {
                return $this->generateUrlsetXml($this->getBlogCategories());
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Sitemap blog categories generation error: ' . $e->getMessage());
            return response($this->generateErrorXml($e->getMessage()), 500)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        }
    }

    public function instructorsSitemap()
    {
        try {
            $xml = Cache::remember('sitemap-instructors.xml', 86400, function () {
                return $this->generateUrlsetXml($this->getInstructors());
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Sitemap instructors generation error: ' . $e->getMessage());
            return response($this->generateErrorXml($e->getMessage()), 500)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        }
    }

    private function getStaticPages()
    {
        $staticPages = [
            '/' => ['priority' => 1.0, 'changeFreq' => 'daily'],
            '/classes' => ['priority' => 0.9, 'changeFreq' => 'daily'],
            '/blog' => ['priority' => 0.8, 'changeFreq' => 'daily'],
            '/instructors' => ['priority' => 0.7, 'changeFreq' => 'weekly'],
            '/organizations' => ['priority' => 0.7, 'changeFreq' => 'weekly'],
            '/reward-courses' => ['priority' => 0.65, 'changeFreq' => 'daily'],
            '/about' => ['priority' => 0.6, 'changeFreq' => 'monthly'],
            '/contact' => ['priority' => 0.6, 'changeFreq' => 'monthly'],
        ];

        $urls = [];
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());
        $locales = $this->getSupportedLocaleCodes();

        foreach ($staticPages as $url => $settings) {
            $path = trim($url, '/');

            foreach ($locales as $localeCode) {
                $loc = $baseUrl . '/' . $localeCode;
                if (!empty($path)) {
                    $loc .= '/' . $path;
                }

                $urls[] = [
                    'loc' => $loc,
                    'lastmod' => now()->toAtomString(),
                    'priority' => $settings['priority'],
                    'changefreq' => $settings['changeFreq'],
                ];
            }
        }

        return $urls;
    }

    private function webinarSitemapQuery()
    {
        return Webinar::where('status', Webinar::$active)
            ->where('type', '!=', 'text_lesson')
            ->orderBy('updated_at', 'desc');
    }

    private function getPublishedCourses()
    {
        $urls = [];
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());

        $this->webinarSitemapQuery()->chunk(100, function ($courses) use (&$urls, $baseUrl) {
            foreach ($courses as $course) {
                $urls[] = [
                    'loc' => $baseUrl . '/course/' . $course->slug,
                    'lastmod' => $course->updated_at ? Carbon::parse($course->updated_at)->toAtomString() : now()->toAtomString(),
                    'priority' => 0.8,
                    'changefreq' => 'weekly',
                ];
            }
        });

        return $urls;
    }

    private function getPublishedCoursesSlice(int $offset, int $limit): array
    {
        $urls = [];
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());

        $courses = $this->webinarSitemapQuery()
            ->skip($offset)
            ->take($limit)
            ->get();

        foreach ($courses as $course) {
            $urls[] = [
                'loc' => $baseUrl . '/course/' . $course->slug,
                'lastmod' => $course->updated_at ? Carbon::parse($course->updated_at)->toAtomString() : now()->toAtomString(),
                'priority' => 0.8,
                'changefreq' => 'weekly',
            ];
        }

        return $urls;
    }

    private function blogSitemapQuery()
    {
        return Blog::where('status', 'publish')->orderBy('updated_at', 'desc');
    }

    private function getPublishedBlogPosts()
    {
        $urls = [];
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());

        $this->blogSitemapQuery()->chunk(100, function ($blogPosts) use (&$urls, $baseUrl) {
            foreach ($blogPosts as $post) {
                $urls[] = [
                    'loc' => $baseUrl . '/blog/' . $post->slug,
                    'lastmod' => $post->updated_at ? Carbon::parse($post->updated_at)->toAtomString() : now()->toAtomString(),
                    'priority' => 0.7,
                    'changefreq' => 'monthly',
                ];
            }
        });

        return $urls;
    }

    private function getPublishedBlogPostsSlice(int $offset, int $limit): array
    {
        $urls = [];
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());

        $posts = $this->blogSitemapQuery()
            ->skip($offset)
            ->take($limit)
            ->get();

        foreach ($posts as $post) {
            $urls[] = [
                'loc' => $baseUrl . '/blog/' . $post->slug,
                'lastmod' => $post->updated_at ? Carbon::parse($post->updated_at)->toAtomString() : now()->toAtomString(),
                'priority' => 0.7,
                'changefreq' => 'monthly',
            ];
        }

        return $urls;
    }

    private function upcomingSitemapQuery()
    {
        return UpcomingCourse::where('status', UpcomingCourse::$active)
            ->orderBy('created_at', 'desc');
    }

    private function getPublishedUpcomingCourses()
    {
        $urls = [];
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());

        $this->upcomingSitemapQuery()->chunk(100, function ($upcomingCourses) use (&$urls, $baseUrl) {
            foreach ($upcomingCourses as $course) {
                $urls[] = [
                    'loc' => $baseUrl . '/upcoming-course/' . $course->slug,
                    'lastmod' => $course->created_at ? Carbon::parse($course->created_at)->toAtomString() : now()->toAtomString(),
                    'priority' => 0.6,
                    'changefreq' => 'weekly',
                ];
            }
        });

        return $urls;
    }

    private function getPublishedUpcomingCoursesSlice(int $offset, int $limit): array
    {
        $urls = [];
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());

        $items = $this->upcomingSitemapQuery()
            ->skip($offset)
            ->take($limit)
            ->get();

        foreach ($items as $course) {
            $urls[] = [
                'loc' => $baseUrl . '/upcoming-course/' . $course->slug,
                'lastmod' => $course->created_at ? Carbon::parse($course->created_at)->toAtomString() : now()->toAtomString(),
                'priority' => 0.6,
                'changefreq' => 'weekly',
            ];
        }

        return $urls;
    }

    private function getCategories()
    {
        $urls = [];
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());

        $categories = Category::all();

        foreach ($categories as $category) {
            $urls[] = [
                'loc' => $baseUrl . '/category/' . $category->slug,
                'lastmod' => now()->toAtomString(),
                'priority' => 0.6,
                'changefreq' => 'weekly',
            ];
        }

        return $urls;
    }

    private function getBlogCategories()
    {
        $urls = [];
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());

        $blogCategories = BlogCategory::all();

        foreach ($blogCategories as $category) {
            $urls[] = [
                'loc' => $baseUrl . '/blog/category/' . $category->slug,
                'lastmod' => now()->toAtomString(),
                'priority' => 0.5,
                'changefreq' => 'weekly',
            ];
        }

        return $urls;
    }

    private function getInstructors()
    {
        $urls = [];
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());
        $locales = $this->getSupportedLocaleCodes();

        $instructors = User::whereHas('webinars', function ($query) {
            $query->where('status', Webinar::$active);
        })
            ->where('role_name', 'instructor')
            ->get();

        foreach ($instructors as $instructor) {
            foreach ($locales as $localeCode) {
                $urls[] = [
                    'loc' => $baseUrl . '/' . $localeCode . '/users/' . $instructor->id . '/profile',
                    'lastmod' => now()->toAtomString(),
                    'priority' => 0.6,
                    'changefreq' => 'weekly',
                ];
            }
        }

        return $urls;
    }

    private function baseUrl(): string
    {
        return rtrim(config('app.url', request()->getSchemeAndHttpHost()), '/');
    }

    /**
     * Child sitemap locations for the master index (no duplicate URL sets).
     */
    private function buildMasterSitemapIndexEntries(): array
    {
        $base = $this->baseUrl();
        $now = now()->toAtomString();
        $entries = [];

        $entries[] = ['loc' => $base . '/sitemap-pages.xml', 'lastmod' => $now];

        $courseCount = $this->webinarSitemapQuery()->count();
        if ($courseCount <= self::MAX_URLS_PER_SITEMAP) {
            $entries[] = ['loc' => $base . '/sitemap-courses.xml', 'lastmod' => $now];
        } else {
            $pages = (int) ceil($courseCount / self::MAX_URLS_PER_SITEMAP);
            for ($p = 1; $p <= $pages; $p++) {
                $entries[] = ['loc' => $base . '/sitemap-courses-page-' . $p . '.xml', 'lastmod' => $now];
            }
        }

        $blogCount = $this->blogSitemapQuery()->count();
        if ($blogCount <= self::MAX_URLS_PER_SITEMAP) {
            $entries[] = ['loc' => $base . '/sitemap-blog.xml', 'lastmod' => $now];
        } else {
            $pages = (int) ceil($blogCount / self::MAX_URLS_PER_SITEMAP);
            for ($p = 1; $p <= $pages; $p++) {
                $entries[] = ['loc' => $base . '/sitemap-blog-page-' . $p . '.xml', 'lastmod' => $now];
            }
        }

        $upcomingCount = $this->upcomingSitemapQuery()->count();
        if ($upcomingCount <= self::MAX_URLS_PER_SITEMAP) {
            $entries[] = ['loc' => $base . '/sitemap-upcoming-courses.xml', 'lastmod' => $now];
        } else {
            $pages = (int) ceil($upcomingCount / self::MAX_URLS_PER_SITEMAP);
            for ($p = 1; $p <= $pages; $p++) {
                $entries[] = ['loc' => $base . '/sitemap-upcoming-courses-page-' . $p . '.xml', 'lastmod' => $now];
            }
        }

        $entries[] = ['loc' => $base . '/sitemap-categories.xml', 'lastmod' => $now];
        $entries[] = ['loc' => $base . '/sitemap-blog-categories.xml', 'lastmod' => $now];
        $entries[] = ['loc' => $base . '/sitemap-instructors.xml', 'lastmod' => $now];

        return $entries;
    }

    public function courses()
    {
        try {
            $total = $this->webinarSitemapQuery()->count();
            if ($total > self::MAX_URLS_PER_SITEMAP) {
                return redirect()->route('sitemap.courses.paginated', ['page' => 1], 301)
                    ->header('Content-Type', 'application/xml; charset=utf-8');
            }

            $xml = Cache::remember('sitemap-courses.xml', 86400, function () {
                $urls = $this->getPublishedCourses();
                return $this->generateUrlsetXml($urls);
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Courses sitemap generation error: ' . $e->getMessage());
            return response($this->generateErrorXml($e->getMessage()), 500)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        }
    }

    public function blog()
    {
        try {
            $total = $this->blogSitemapQuery()->count();
            if ($total > self::MAX_URLS_PER_SITEMAP) {
                return redirect()->route('sitemap.blog.paginated', ['page' => 1], 301)
                    ->header('Content-Type', 'application/xml; charset=utf-8');
            }

            $xml = Cache::remember('sitemap-blog.xml', 86400, function () {
                $urls = $this->getPublishedBlogPosts();
                return $this->generateUrlsetXml($urls);
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Blog sitemap generation error: ' . $e->getMessage());
            return response($this->generateErrorXml($e->getMessage()), 500)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        }
    }

    public function upcomingCourses()
    {
        try {
            $total = $this->upcomingSitemapQuery()->count();
            if ($total > self::MAX_URLS_PER_SITEMAP) {
                return redirect()->route('sitemap.upcoming.paginated', ['page' => 1], 301)
                    ->header('Content-Type', 'application/xml; charset=utf-8');
            }

            $xml = Cache::remember('sitemap-upcoming-courses.xml', 86400, function () {
                $urls = $this->getPublishedUpcomingCourses();
                return $this->generateUrlsetXml($urls);
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Upcoming courses sitemap generation error: ' . $e->getMessage());
            return response($this->generateErrorXml($e->getMessage()), 500)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        }
    }

    public function coursesPaginated($page = 1)
    {
        try {
            $page = max(1, (int) $page);
            $total = $this->webinarSitemapQuery()->count();
            $totalPages = max(1, (int) ceil($total / self::MAX_URLS_PER_SITEMAP));

            if ($page > $totalPages || ($total === 0 && $page > 1)) {
                abort(404);
            }

            $cacheKey = 'sitemap-courses-page-' . $page . '.xml';

            $xml = Cache::remember($cacheKey, 86400, function () use ($page) {
                $offset = ($page - 1) * self::MAX_URLS_PER_SITEMAP;
                $urls = $this->getPublishedCoursesSlice($offset, self::MAX_URLS_PER_SITEMAP);
                return $this->generateUrlsetXml($urls);
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Paginated courses sitemap generation error: ' . $e->getMessage());
            return response($this->generateErrorXml($e->getMessage()), 500)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        }
    }

    public function blogPaginated($page = 1)
    {
        try {
            $page = max(1, (int) $page);
            $total = $this->blogSitemapQuery()->count();
            $totalPages = max(1, (int) ceil($total / self::MAX_URLS_PER_SITEMAP));

            if ($page > $totalPages || ($total === 0 && $page > 1)) {
                abort(404);
            }

            $cacheKey = 'sitemap-blog-page-' . $page . '.xml';

            $xml = Cache::remember($cacheKey, 86400, function () use ($page) {
                $offset = ($page - 1) * self::MAX_URLS_PER_SITEMAP;
                $urls = $this->getPublishedBlogPostsSlice($offset, self::MAX_URLS_PER_SITEMAP);
                return $this->generateUrlsetXml($urls);
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Paginated blog sitemap generation error: ' . $e->getMessage());
            return response($this->generateErrorXml($e->getMessage()), 500)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        }
    }

    public function upcomingCoursesPaginated($page = 1)
    {
        try {
            $page = max(1, (int) $page);
            $total = $this->upcomingSitemapQuery()->count();
            $totalPages = max(1, (int) ceil($total / self::MAX_URLS_PER_SITEMAP));

            if ($page > $totalPages || ($total === 0 && $page > 1)) {
                abort(404);
            }

            $cacheKey = 'sitemap-upcoming-courses-page-' . $page . '.xml';

            $xml = Cache::remember($cacheKey, 86400, function () use ($page) {
                $offset = ($page - 1) * self::MAX_URLS_PER_SITEMAP;
                $urls = $this->getPublishedUpcomingCoursesSlice($offset, self::MAX_URLS_PER_SITEMAP);
                return $this->generateUrlsetXml($urls);
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Paginated upcoming courses sitemap generation error: ' . $e->getMessage());
            return response($this->generateErrorXml($e->getMessage()), 500)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        }
    }

    /**
     * Courses-only sitemap index (child files only — valid sitemapindex XML).
     */
    public function coursesIndex()
    {
        try {
            $xml = Cache::remember('sitemap-courses-index.xml', 86400, function () {
                $base = $this->baseUrl();
                $now = now()->toAtomString();
                $entries = [];

                $total = $this->webinarSitemapQuery()->count();
                if ($total <= self::MAX_URLS_PER_SITEMAP) {
                    $entries[] = ['loc' => $base . '/sitemap-courses.xml', 'lastmod' => $now];
                } else {
                    $pages = (int) ceil($total / self::MAX_URLS_PER_SITEMAP);
                    for ($p = 1; $p <= $pages; $p++) {
                        $entries[] = ['loc' => $base . '/sitemap-courses-page-' . $p . '.xml', 'lastmod' => $now];
                    }
                }

                return $this->generateSitemapIndexXml($entries);
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Courses index sitemap generation error: ' . $e->getMessage());
            return response($this->generateErrorXml($e->getMessage()), 500)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        }
    }

    private function getSupportedLocaleCodes(): array
    {
        $supportedLocalesMap = getUserLanguagesLists();

        $codes = array_values(array_unique(array_map(function ($code) {
            return mb_strtolower($code);
        }, array_keys($supportedLocalesMap))));

        if (!empty($codes)) {
            return $codes;
        }

        $default = mb_strtolower(getDefaultLocale());
        return !empty($default) ? [$default] : ['en'];
    }

    /**
     * Browser-friendly HTML table (Yoast-style) via XSLT; crawlers ignore the stylesheet.
     */
    private function xslProcessingInstruction(string $xslBasename): string
    {
        $href = $this->baseUrl() . '/' . $xslBasename;

        return '<?xml-stylesheet type="text/xsl" href="' . htmlspecialchars($href, ENT_XML1, 'UTF-8') . '"?>' . "\n";
    }

    private function generateSitemapIndexXml(array $sitemaps): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= $this->xslProcessingInstruction('sitemap-index.xsl');
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($sitemaps as $item) {
            $xml .= '  <sitemap>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($item['loc'], ENT_XML1, 'UTF-8') . '</loc>' . "\n";
            if (!empty($item['lastmod'])) {
                $xml .= '    <lastmod>' . htmlspecialchars($item['lastmod'], ENT_XML1, 'UTF-8') . '</lastmod>' . "\n";
            }
            $xml .= '  </sitemap>' . "\n";
        }

        $xml .= '</sitemapindex>';

        return $xml;
    }

    private function generateUrlsetXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= $this->xslProcessingInstruction('sitemap-urlset.xsl');
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $urlData) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($urlData['loc'], ENT_XML1, 'UTF-8') . '</loc>' . "\n";

            if (isset($urlData['lastmod'])) {
                $xml .= '    <lastmod>' . htmlspecialchars($urlData['lastmod'], ENT_XML1, 'UTF-8') . '</lastmod>' . "\n";
            }

            if (isset($urlData['priority'])) {
                $xml .= '    <priority>' . htmlspecialchars((string) $urlData['priority'], ENT_XML1, 'UTF-8') . '</priority>' . "\n";
            }

            if (isset($urlData['changefreq'])) {
                $xml .= '    <changefreq>' . htmlspecialchars($urlData['changefreq'], ENT_XML1, 'UTF-8') . '</changefreq>' . "\n";
            }

            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    private function generateErrorXml($message)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<error>' . "\n";
        $xml .= '  <message>' . htmlspecialchars($message, ENT_XML1, 'UTF-8') . '</message>' . "\n";
        $xml .= '</error>';
        return $xml;
    }
}
