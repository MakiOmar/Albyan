<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Webinar;
use App\Models\UpcomingCourse;
use App\Models\Category;
use App\Models\BlogCategory;
use App\Models\User;
use Carbon\Carbon;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index()
    {
        // Cache the sitemap for 24 hours to improve performance
        return Cache::remember('sitemap.xml', 86400, function () {
            $sitemap = Sitemap::create();

            // Add static pages
            $this->addStaticPages($sitemap);

            // Add published courses/webinars
            $this->addPublishedCourses($sitemap);

            // Add published blog posts
            $this->addPublishedBlogPosts($sitemap);

            // Add published upcoming courses
            $this->addPublishedUpcomingCourses($sitemap);

            // Add categories
            $this->addCategories($sitemap);

            // Add blog categories
            $this->addBlogCategories($sitemap);

            // Add instructors/teachers
            $this->addInstructors($sitemap);

            return $sitemap->toResponse(request());
        });
    }

    private function addStaticPages($sitemap)
    {
        $staticPages = [
            '/' => ['priority' => 1.0, 'changeFreq' => 'daily'],
            '/courses' => ['priority' => 0.9, 'changeFreq' => 'daily'],
            '/webinars' => ['priority' => 0.9, 'changeFreq' => 'daily'],
            '/blog' => ['priority' => 0.8, 'changeFreq' => 'daily'],
            '/instructors' => ['priority' => 0.7, 'changeFreq' => 'weekly'],
            '/categories' => ['priority' => 0.7, 'changeFreq' => 'weekly'],
            '/about' => ['priority' => 0.6, 'changeFreq' => 'monthly'],
            '/contact' => ['priority' => 0.6, 'changeFreq' => 'monthly'],
            '/terms' => ['priority' => 0.5, 'changeFreq' => 'monthly'],
            '/privacy' => ['priority' => 0.5, 'changeFreq' => 'monthly'],
        ];

        foreach ($staticPages as $url => $settings) {
            $sitemap->add(
                Url::create($url)
                    ->setPriority($settings['priority'])
                    ->setChangeFrequency($settings['changeFreq'])
                    ->setLastModificationDate(now())
            );
        }
    }

    private function addPublishedCourses($sitemap)
    {
        // Get all published courses and webinars with chunking for better memory management
        Webinar::where('status', Webinar::$active)
            ->where('type', '!=', 'text_lesson') // Exclude text lessons if needed
            ->orderBy('updated_at', 'desc')
            ->chunk(100, function ($courses) use ($sitemap) {
                foreach ($courses as $course) {
                    $url = '/course/' . $course->slug;
                    
                    $sitemap->add(
                        Url::create($url)
                            ->setPriority(0.8)
                            ->setChangeFrequency('weekly')
                            ->setLastModificationDate($course->updated_at ? Carbon::createFromTimestamp($course->updated_at) : now())
                    );
                }
            });
    }

    private function addPublishedBlogPosts($sitemap)
    {
        // Get all published blog posts with chunking for better memory management
        Blog::where('status', 'publish')
            ->orderBy('updated_at', 'desc')
            ->chunk(100, function ($blogPosts) use ($sitemap) {
                foreach ($blogPosts as $post) {
                    $url = '/blog/' . $post->slug;
                    
                    $sitemap->add(
                        Url::create($url)
                            ->setPriority(0.7)
                            ->setChangeFrequency('monthly')
                            ->setLastModificationDate($post->updated_at ? Carbon::createFromTimestamp($post->updated_at) : now())
                    );
                }
            });
    }

    private function addPublishedUpcomingCourses($sitemap)
    {
        // Get all published upcoming courses with chunking for better memory management
        UpcomingCourse::where('status', UpcomingCourse::$active)
            ->orderBy('created_at', 'desc')
            ->chunk(100, function ($upcomingCourses) use ($sitemap) {
                foreach ($upcomingCourses as $course) {
                    $url = '/upcoming-course/' . $course->slug;
                    
                    $sitemap->add(
                        Url::create($url)
                            ->setPriority(0.6)
                            ->setChangeFrequency('weekly')
                            ->setLastModificationDate($course->created_at ? Carbon::createFromTimestamp($course->created_at) : now())
                    );
                }
            });
    }

    private function addCategories($sitemap)
    {
        // Get all categories
        $categories = Category::all();

        foreach ($categories as $category) {
            $url = '/category/' . $category->slug;
            
            $sitemap->add(
                Url::create($url)
                    ->setPriority(0.6)
                    ->setChangeFrequency('weekly')
                    ->setLastModificationDate(now())
            );
        }
    }

    private function addBlogCategories($sitemap)
    {
        // Get all blog categories
        $blogCategories = BlogCategory::all();

        foreach ($blogCategories as $category) {
            $url = '/blog/category/' . $category->slug;
            
            $sitemap->add(
                Url::create($url)
                    ->setPriority(0.5)
                    ->setChangeFrequency('weekly')
                    ->setLastModificationDate(now())
            );
        }
    }

    private function addInstructors($sitemap)
    {
        // Get all instructors/teachers who have published courses
        $instructors = User::whereHas('webinars', function ($query) {
                $query->where('status', Webinar::$active);
            })
            ->where('role_name', 'instructor')
            ->get();

        foreach ($instructors as $instructor) {
            $url = '/instructor/' . $instructor->id;
            
            $sitemap->add(
                Url::create($url)
                    ->setPriority(0.6)
                    ->setChangeFrequency('weekly')
                    ->setLastModificationDate(now())
            );
        }
    }

    /**
     * Generate sitemap for specific content type
     */
    public function courses()
    {
        return Cache::remember('sitemap-courses.xml', 86400, function () {
            $sitemap = Sitemap::create();

            Webinar::where('status', Webinar::$active)
                ->orderBy('updated_at', 'desc')
                ->chunk(100, function ($courses) use ($sitemap) {
                    foreach ($courses as $course) {
                        $url = '/course/' . $course->slug;
                        
                        $sitemap->add(
                            Url::create($url)
                                ->setPriority(0.8)
                                ->setChangeFrequency('weekly')
                                ->setLastModificationDate($course->updated_at ? Carbon::createFromTimestamp($course->updated_at) : now())
                        );
                    }
                });

            return $sitemap->toResponse(request());
        });
    }

    public function blog()
    {
        return Cache::remember('sitemap-blog.xml', 86400, function () {
            $sitemap = Sitemap::create();

            Blog::where('status', 'publish')
                ->orderBy('updated_at', 'desc')
                ->chunk(100, function ($blogPosts) use ($sitemap) {
                    foreach ($blogPosts as $post) {
                        $url = '/blog/' . $post->slug;
                        
                        $sitemap->add(
                            Url::create($url)
                                ->setPriority(0.7)
                                ->setChangeFrequency('monthly')
                                ->setLastModificationDate($post->updated_at ? Carbon::createFromTimestamp($post->updated_at) : now())
                        );
                    }
                });

            return $sitemap->toResponse(request());
        });
    }

    public function upcomingCourses()
    {
        return Cache::remember('sitemap-upcoming-courses.xml', 86400, function () {
            $sitemap = Sitemap::create();

            UpcomingCourse::where('status', UpcomingCourse::$active)
                ->orderBy('updated_at', 'desc')
                ->chunk(100, function ($upcomingCourses) use ($sitemap) {
                    foreach ($upcomingCourses as $course) {
                        $url = '/upcoming-course/' . $course->slug;
                        
                        $sitemap->add(
                            Url::create($url)
                                ->setPriority(0.6)
                                ->setChangeFrequency('weekly')
                                ->setLastModificationDate($course->updated_at ? Carbon::createFromTimestamp($course->updated_at) : now())
                        );
                    }
                });

            return $sitemap->toResponse(request());
        });
    }

    /**
     * Generate paginated sitemap for courses (useful for very large datasets)
     */
    public function coursesPaginated($page = 1)
    {
        $perPage = 1000; // Google recommends max 50,000 URLs per sitemap, but we use 1000 for better performance
        $offset = ($page - 1) * $perPage;
        
        $cacheKey = "sitemap-courses-page-{$page}.xml";
        
        return Cache::remember($cacheKey, 86400, function () use ($perPage, $offset) {
            $sitemap = Sitemap::create();
            
            $courses = Webinar::where('status', Webinar::$active)
                ->where('type', '!=', 'text_lesson')
                ->orderBy('updated_at', 'desc')
                ->skip($offset)
                ->take($perPage)
                ->get();

            foreach ($courses as $course) {
                $url = '/course/' . $course->slug;
                
                $sitemap->add(
                    Url::create($url)
                        ->setPriority(0.8)
                        ->setChangeFrequency('weekly')
                        ->setLastModificationDate($course->updated_at ? Carbon::createFromTimestamp($course->updated_at) : now())
                );
            }

            return $sitemap->toResponse(request());
        });
    }

    /**
     * Generate sitemap index for paginated course sitemaps
     */
    public function coursesIndex()
    {
        return Cache::remember('sitemap-courses-index.xml', 86400, function () {
            $sitemap = Sitemap::create();
            
            $totalCourses = Webinar::where('status', Webinar::$active)
                ->where('type', '!=', 'text_lesson')
                ->count();
            
            $perPage = 1000;
            $totalPages = ceil($totalCourses / $perPage);
            
            // Add main courses sitemap
            $sitemap->add(
                Url::create('/sitemap-courses.xml')
                    ->setLastModificationDate(now())
            );
            
            // Add paginated sitemaps if needed
            for ($page = 1; $page <= $totalPages; $page++) {
                $sitemap->add(
                    Url::create("/sitemap-courses-page-{$page}.xml")
                        ->setLastModificationDate(now())
                );
            }

            return $sitemap->toResponse(request());
        });
    }
} 