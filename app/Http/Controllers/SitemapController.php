<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Webinar;
use App\Models\UpcomingCourse;
use App\Models\Category;
use App\Models\BlogCategory;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index()
    {
        try {
            // Cache the sitemap for 24 hours to improve performance
            $xml = Cache::remember('sitemap.xml', 86400, function () {
                $urls = [];

                // Add static pages
                $urls = array_merge($urls, $this->getStaticPages());

                // Add published courses/webinars
                $urls = array_merge($urls, $this->getPublishedCourses());

                // Add published blog posts
                $urls = array_merge($urls, $this->getPublishedBlogPosts());

                // Add published upcoming courses
                $urls = array_merge($urls, $this->getPublishedUpcomingCourses());

                // Add categories
                $urls = array_merge($urls, $this->getCategories());

                // Add blog categories
                $urls = array_merge($urls, $this->getBlogCategories());

                // Add instructors/teachers
                $urls = array_merge($urls, $this->getInstructors());

                return $this->generateXml($urls);
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Sitemap generation error: ' . $e->getMessage());
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
            $path = trim($url, '/'); // '/' => '' (home)

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

    private function getPublishedCourses()
    {
        $urls = [];
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());
        
        // Get all published courses and webinars with chunking for better memory management
        Webinar::where('status', Webinar::$active)
            ->where('type', '!=', 'text_lesson')
            ->orderBy('updated_at', 'desc')
            ->chunk(100, function ($courses) use (&$urls, $baseUrl) {
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

    private function getPublishedBlogPosts()
    {
        $urls = [];
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());
        
        // Get all published blog posts with chunking for better memory management
        Blog::where('status', 'publish')
            ->orderBy('updated_at', 'desc')
            ->chunk(100, function ($blogPosts) use (&$urls, $baseUrl) {
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

    private function getPublishedUpcomingCourses()
    {
        $urls = [];
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());
        
        // Get all published upcoming courses with chunking for better memory management
        UpcomingCourse::where('status', UpcomingCourse::$active)
            ->orderBy('created_at', 'desc')
            ->chunk(100, function ($upcomingCourses) use (&$urls, $baseUrl) {
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

    private function getCategories()
    {
        $urls = [];
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());
        
        // Get all categories
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
        
        // Get all blog categories
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
        
        // Get all instructors/teachers who have published courses
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

    /**
     * Generate sitemap for specific content type
     */
    public function courses()
    {
        try {
            $xml = Cache::remember('sitemap-courses.xml', 86400, function () {
                $urls = $this->getPublishedCourses();
                return $this->generateXml($urls);
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
            $xml = Cache::remember('sitemap-blog.xml', 86400, function () {
                $urls = $this->getPublishedBlogPosts();
                return $this->generateXml($urls);
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
            $xml = Cache::remember('sitemap-upcoming-courses.xml', 86400, function () {
                $urls = $this->getPublishedUpcomingCourses();
                return $this->generateXml($urls);
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Upcoming courses sitemap generation error: ' . $e->getMessage());
            return response($this->generateErrorXml($e->getMessage()), 500)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        }
    }

    /**
     * Generate paginated sitemap for courses (useful for very large datasets)
     */
    public function coursesPaginated($page = 1)
    {
        try {
            $perPage = 1000;
            $offset = ($page - 1) * $perPage;
            $cacheKey = "sitemap-courses-page-{$page}.xml";
            
            $xml = Cache::remember($cacheKey, 86400, function () use ($perPage, $offset) {
                $urls = [];
                $baseUrl = config('app.url', request()->getSchemeAndHttpHost());
                
                $courses = Webinar::where('status', Webinar::$active)
                    ->where('type', '!=', 'text_lesson')
                    ->orderBy('updated_at', 'desc')
                    ->skip($offset)
                    ->take($perPage)
                    ->get();

                foreach ($courses as $course) {
                    $urls[] = [
                        'loc' => $baseUrl . '/course/' . $course->slug,
                        'lastmod' => $course->updated_at ? Carbon::parse($course->updated_at)->toAtomString() : now()->toAtomString(),
                        'priority' => 0.8,
                        'changefreq' => 'weekly',
                    ];
                }

                return $this->generateXml($urls);
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Paginated courses sitemap generation error: ' . $e->getMessage());
            return response($this->generateErrorXml($e->getMessage()), 500)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        }
    }

    /**
     * Generate sitemap index for paginated course sitemaps
     */
    public function coursesIndex()
    {
        try {
            $xml = Cache::remember('sitemap-courses-index.xml', 86400, function () {
                $urls = [];
                $baseUrl = config('app.url', request()->getSchemeAndHttpHost());
                
                $totalCourses = Webinar::where('status', Webinar::$active)
                    ->where('type', '!=', 'text_lesson')
                    ->count();
                
                $perPage = 1000;
                $totalPages = ceil($totalCourses / $perPage);
                
                // Add main courses sitemap
                $urls[] = [
                    'loc' => $baseUrl . '/sitemap-courses.xml',
                    'lastmod' => now()->toAtomString(),
                ];
                
                // Add paginated sitemaps if needed
                for ($page = 1; $page <= $totalPages; $page++) {
                    $urls[] = [
                        'loc' => $baseUrl . "/sitemap-courses-page-{$page}.xml",
                        'lastmod' => now()->toAtomString(),
                    ];
                }

                return $this->generateXml($urls);
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

        // hreflang/canonical use language codes (e.g. `en`, `ar`), not country/flag codes.
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
     * Generate XML from URLs array
     */
    private function generateXml(array $urls)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $urlData) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($urlData['loc'], ENT_XML1, 'UTF-8') . '</loc>' . "\n";
            
            if (isset($urlData['lastmod'])) {
                $xml .= '    <lastmod>' . htmlspecialchars($urlData['lastmod'], ENT_XML1, 'UTF-8') . '</lastmod>' . "\n";
            }
            
            if (isset($urlData['priority'])) {
                $xml .= '    <priority>' . htmlspecialchars($urlData['priority'], ENT_XML1, 'UTF-8') . '</priority>' . "\n";
            }
            
            if (isset($urlData['changefreq'])) {
                $xml .= '    <changefreq>' . htmlspecialchars($urlData['changefreq'], ENT_XML1, 'UTF-8') . '</changefreq>' . "\n";
            }
            
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Generate error XML
     */
    private function generateErrorXml($message)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<error>' . "\n";
        $xml .= '  <message>' . htmlspecialchars($message, ENT_XML1, 'UTF-8') . '</message>' . "\n";
        $xml .= '</error>';
        return $xml;
    }
} 