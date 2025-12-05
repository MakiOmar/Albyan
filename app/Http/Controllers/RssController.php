<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Webinar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RssController extends Controller
{
    /**
     * Generate RSS feed for courses
     */
    public function courses()
    {
        try {
            $xml = Cache::remember('rss-courses.xml', 3600, function () {
                $baseUrl = config('app.url', request()->getSchemeAndHttpHost());
                $siteName = config('app.name', 'Rocket LMS');
                
                // Get latest published courses (limit to 50 for RSS feed)
                $courses = Webinar::where('status', Webinar::$active)
                    ->where('type', '!=', 'text_lesson')
                    ->with('teacher')
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get();

                return $this->generateRssFeed([
                    'title' => $siteName . ' - Courses',
                    'description' => 'Latest courses and webinars from ' . $siteName,
                    'link' => $baseUrl . '/courses',
                    'feedUrl' => $baseUrl . '/rss/courses',
                    'items' => $courses->map(function ($course) use ($baseUrl) {
                        return [
                            'title' => $course->title,
                            'link' => $baseUrl . '/course/' . $course->slug,
                            'description' => strip_tags($course->description ?? ''),
                            'pubDate' => $course->created_at ? Carbon::createFromTimestamp($course->created_at)->format('D, d M Y H:i:s T') : now()->format('D, d M Y H:i:s T'),
                            'author' => $course->teacher->full_name ?? 'Admin',
                            'guid' => $baseUrl . '/course/' . $course->slug,
                        ];
                    })->toArray(),
                ]);
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/rss+xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Courses RSS feed generation error: ' . $e->getMessage());
            return response($this->generateErrorRss($e->getMessage()), 500)
                ->header('Content-Type', 'application/rss+xml; charset=utf-8');
        }
    }

    /**
     * Generate RSS feed for blog posts
     */
    public function blog()
    {
        try {
            $xml = Cache::remember('rss-blog.xml', 3600, function () {
                $baseUrl = config('app.url', request()->getSchemeAndHttpHost());
                $siteName = config('app.name', 'Rocket LMS');
                
                // Get latest published blog posts (limit to 50 for RSS feed)
                $posts = Blog::where('status', 'publish')
                    ->with(['author', 'category'])
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get();

                return $this->generateRssFeed([
                    'title' => $siteName . ' - Blog',
                    'description' => 'Latest blog posts from ' . $siteName,
                    'link' => $baseUrl . '/blog',
                    'feedUrl' => $baseUrl . '/rss/blog',
                    'items' => $posts->map(function ($post) use ($baseUrl) {
                        $description = strip_tags($post->description ?? '');
                        $content = strip_tags($post->content ?? '');
                        $fullDescription = !empty($description) ? $description : $content;
                        
                        return [
                            'title' => $post->title,
                            'link' => $baseUrl . $post->getUrl(),
                            'description' => $fullDescription,
                            'pubDate' => $post->created_at ? Carbon::createFromTimestamp($post->created_at)->format('D, d M Y H:i:s T') : now()->format('D, d M Y H:i:s T'),
                            'author' => $post->author->full_name ?? $post->author->email ?? 'Admin',
                            'guid' => $baseUrl . $post->getUrl(),
                            'category' => $post->category->title ?? null,
                        ];
                    })->toArray(),
                ]);
            });

            return response($xml, 200)
                ->header('Content-Type', 'application/rss+xml; charset=utf-8');
        } catch (\Exception $e) {
            \Log::error('Blog RSS feed generation error: ' . $e->getMessage());
            return response($this->generateErrorRss($e->getMessage()), 500)
                ->header('Content-Type', 'application/rss+xml; charset=utf-8');
        }
    }

    /**
     * Generate RSS 2.0 XML feed
     */
    private function generateRssFeed(array $data)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= '  <channel>' . "\n";
        
        // Channel information
        $xml .= '    <title>' . htmlspecialchars($data['title'], ENT_XML1, 'UTF-8') . '</title>' . "\n";
        $xml .= '    <link>' . htmlspecialchars($data['link'], ENT_XML1, 'UTF-8') . '</link>' . "\n";
        $xml .= '    <description>' . htmlspecialchars($data['description'], ENT_XML1, 'UTF-8') . '</description>' . "\n";
        $xml .= '    <language>en-us</language>' . "\n";
        $xml .= '    <lastBuildDate>' . now()->format('D, d M Y H:i:s T') . '</lastBuildDate>' . "\n";
        $xml .= '    <pubDate>' . now()->format('D, d M Y H:i:s T') . '</pubDate>' . "\n";
        $xml .= '    <ttl>60</ttl>' . "\n";
        $xml .= '    <atom:link href="' . htmlspecialchars($data['feedUrl'], ENT_XML1, 'UTF-8') . '" rel="self" type="application/rss+xml" />' . "\n";
        
        // Items
        foreach ($data['items'] as $item) {
            $xml .= '    <item>' . "\n";
            $xml .= '      <title>' . htmlspecialchars($item['title'], ENT_XML1, 'UTF-8') . '</title>' . "\n";
            $xml .= '      <link>' . htmlspecialchars($item['link'], ENT_XML1, 'UTF-8') . '</link>' . "\n";
            $xml .= '      <description><![CDATA[' . $item['description'] . ']]></description>' . "\n";
            $xml .= '      <pubDate>' . $item['pubDate'] . '</pubDate>' . "\n";
            $xml .= '      <guid isPermaLink="true">' . htmlspecialchars($item['guid'], ENT_XML1, 'UTF-8') . '</guid>' . "\n";
            
            if (isset($item['author'])) {
                $xml .= '      <author>' . htmlspecialchars($item['author'], ENT_XML1, 'UTF-8') . '</author>' . "\n";
            }
            
            if (isset($item['category']) && !empty($item['category'])) {
                $xml .= '      <category>' . htmlspecialchars($item['category'], ENT_XML1, 'UTF-8') . '</category>' . "\n";
            }
            
            $xml .= '    </item>' . "\n";
        }
        
        $xml .= '  </channel>' . "\n";
        $xml .= '</rss>';
        
        return $xml;
    }

    /**
     * Generate error RSS feed
     */
    private function generateErrorRss($message)
    {
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());
        $siteName = config('app.name', 'Rocket LMS');
        
        return $this->generateRssFeed([
            'title' => $siteName . ' - Error',
            'description' => 'RSS feed error: ' . $message,
            'link' => $baseUrl,
            'feedUrl' => $baseUrl . '/rss',
            'items' => [
                [
                    'title' => 'Error Loading Feed',
                    'link' => $baseUrl,
                    'description' => 'An error occurred while generating the RSS feed: ' . htmlspecialchars($message, ENT_XML1, 'UTF-8'),
                    'pubDate' => now()->format('D, d M Y H:i:s T'),
                    'author' => 'System',
                    'guid' => $baseUrl . '/error',
                ],
            ],
        ]);
    }
}

