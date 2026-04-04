<?php

namespace App\Console\Commands;

use App\Models\Blog;
use App\Models\Webinar;
use App\Models\UpcomingCourse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GenerateSitemap extends Command
{
    private const MAX_URLS_PER_SITEMAP = 50000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate {type? : Type of sitemap to generate (all, courses, blog, upcoming-courses)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap XML files for SEO';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->argument('type') ?? 'all';

        $this->info('Generating sitemap...');

        switch ($type) {
            case 'all':
                $this->generateAllSitemaps();
                break;
            case 'courses':
                $this->generateCoursesSitemap();
                break;
            case 'blog':
                $this->generateBlogSitemap();
                break;
            case 'upcoming-courses':
                $this->generateUpcomingCoursesSitemap();
                break;
            default:
                $this->error('Invalid sitemap type. Use: all, courses, blog, or upcoming-courses');
                return 1;
        }

        $this->info('Sitemap generation completed successfully!');
        return 0;
    }

    private function generateAllSitemaps()
    {
        $this->info('Clearing sitemap caches...');
        Cache::forget('sitemap_index.xml');
        Cache::forget('sitemap.xml');
        Cache::forget('sitemap-pages.xml');
        Cache::forget('sitemap-categories.xml');
        Cache::forget('sitemap-blog-categories.xml');
        Cache::forget('sitemap-instructors.xml');
        Cache::forget('sitemap-courses.xml');
        Cache::forget('sitemap-blog.xml');
        Cache::forget('sitemap-upcoming-courses.xml');
        Cache::forget('sitemap-courses-index.xml');

        $this->forgetPaginatedCourseCaches();
        $this->forgetPaginatedBlogCaches();
        $this->forgetPaginatedUpcomingCaches();

        $this->info('All sitemap caches cleared. Sitemaps will be regenerated on next access.');
    }

    private function forgetPaginatedCourseCaches(): void
    {
        $count = Webinar::where('status', Webinar::$active)
            ->where('type', '!=', 'text_lesson')
            ->count();
        $pages = max(1, (int) ceil($count / self::MAX_URLS_PER_SITEMAP));
        for ($p = 1; $p <= $pages; $p++) {
            Cache::forget('sitemap-courses-page-' . $p . '.xml');
        }
    }

    private function forgetPaginatedBlogCaches(): void
    {
        $count = Blog::where('status', 'publish')->count();
        $pages = max(1, (int) ceil($count / self::MAX_URLS_PER_SITEMAP));
        for ($p = 1; $p <= $pages; $p++) {
            Cache::forget('sitemap-blog-page-' . $p . '.xml');
        }
    }

    private function forgetPaginatedUpcomingCaches(): void
    {
        $count = UpcomingCourse::where('status', UpcomingCourse::$active)->count();
        $pages = max(1, (int) ceil($count / self::MAX_URLS_PER_SITEMAP));
        for ($p = 1; $p <= $pages; $p++) {
            Cache::forget('sitemap-upcoming-courses-page-' . $p . '.xml');
        }
    }

    private function generateCoursesSitemap()
    {
        $this->info('Generating courses sitemap...');
        Cache::forget('sitemap_index.xml');
        Cache::forget('sitemap-courses.xml');
        Cache::forget('sitemap-courses-index.xml');
        $this->forgetPaginatedCourseCaches();
        $this->info('Courses sitemap cache cleared.');
    }

    private function generateBlogSitemap()
    {
        $this->info('Generating blog sitemap...');
        Cache::forget('sitemap_index.xml');
        Cache::forget('sitemap-blog.xml');
        $this->forgetPaginatedBlogCaches();
        $this->info('Blog sitemap cache cleared.');
    }

    private function generateUpcomingCoursesSitemap()
    {
        $this->info('Generating upcoming courses sitemap...');
        Cache::forget('sitemap_index.xml');
        Cache::forget('sitemap-upcoming-courses.xml');
        $this->forgetPaginatedUpcomingCaches();
        $this->info('Upcoming courses sitemap cache cleared.');
    }
}
