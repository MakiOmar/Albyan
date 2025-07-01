<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\SitemapController;

class GenerateSitemap extends Command
{
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
        $this->info('Generating main sitemap...');
        Cache::forget('sitemap.xml');
        
        $this->info('Generating courses sitemap...');
        Cache::forget('sitemap-courses.xml');
        
        $this->info('Generating blog sitemap...');
        Cache::forget('sitemap-blog.xml');
        
        $this->info('Generating upcoming courses sitemap...');
        Cache::forget('sitemap-upcoming-courses.xml');

        $this->info('All sitemap caches cleared. Sitemaps will be regenerated on next access.');
    }

    private function generateCoursesSitemap()
    {
        $this->info('Generating courses sitemap...');
        Cache::forget('sitemap-courses.xml');
        $this->info('Courses sitemap cache cleared.');
    }

    private function generateBlogSitemap()
    {
        $this->info('Generating blog sitemap...');
        Cache::forget('sitemap-blog.xml');
        $this->info('Blog sitemap cache cleared.');
    }

    private function generateUpcomingCoursesSitemap()
    {
        $this->info('Generating upcoming courses sitemap...');
        Cache::forget('sitemap-upcoming-courses.xml');
        $this->info('Upcoming courses sitemap cache cleared.');
    }
} 