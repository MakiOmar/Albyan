<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Webinar;
use App\Services\SchemaOrgService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class LlmsTxtController extends Controller
{
    /**
     * Machine-readable site summary for AI agents (llms.txt convention).
     */
    public function __invoke(SchemaOrgService $schema): Response
    {
        $body = Cache::remember('llms_txt.' . app()->getLocale(), 3600, function () use ($schema) {
            return $this->buildMarkdown($schema);
        });

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function buildMarkdown(SchemaOrgService $schema): string
    {
        $copy = $schema->settings();
        $general = getGeneralSettings();
        $orgName = !empty($general['site_name']) ? $general['site_name'] : 'معهد البيان';
        $origin = $schema->origin();
        $email = getSiteContactEmail() ?: (string) config('schema.email');
        $phones = config('schema.telephones', []);
        $address = config('schema.address', []);
        $legal = $copy['legal_name'] ?? '';
        $description = $copy['org_description'] ?? '';

        $lines = [];
        $lines[] = '# ' . $orgName;
        if ($legal !== '') {
            $lines[] = '';
            $lines[] = '> ' . $legal;
        }
        $lines[] = '';
        if ($description !== '') {
            $lines[] = $description;
            $lines[] = '';
        }

        $lines[] = '## Contact';
        $lines[] = '';
        $lines[] = '- Website: ' . $origin . '/';
        $lines[] = '- Email: ' . $email;
        foreach ($phones as $phone) {
            $lines[] = '- Phone: ' . $phone;
        }
        $whatsapp = config('schema.whatsapp.url');
        if (!empty($whatsapp)) {
            $lines[] = '- WhatsApp: ' . $whatsapp;
        }
        $street = trim(($address['streetAddress'] ?? '') . ', ' . ($address['addressLocality'] ?? '') . ', UAE', ', ');
        if ($street !== '') {
            $lines[] = '- Address: ' . $street;
        }
        $lines[] = '';

        $lines[] = '## Key pages';
        $lines[] = '';
        $lines[] = '- Home: ' . $origin . '/';
        $lines[] = '- About: ' . $origin . '/about';
        $lines[] = '- Contact: ' . $origin . '/contact';
        $lines[] = '- Categories: ' . $origin . '/categories';
        $lines[] = '- Courses: ' . $origin . '/classes';
        $lines[] = '- Schema / structured data: JSON-LD on home and course pages';
        $lines[] = '';

        $lines[] = '## Sitemaps';
        $lines[] = '';
        $lines[] = '- ' . $origin . '/sitemap_index.xml';
        $lines[] = '- ' . $origin . '/sitemap-courses.xml';
        $lines[] = '- ' . $origin . '/sitemap-categories.xml';
        $lines[] = '- ' . $origin . '/sitemap-pages.xml';
        $lines[] = '';

        $lines[] = '## Featured courses';
        $lines[] = '';
        $lines[] = 'Public training programs (see full list in the courses sitemap):';
        $lines[] = '';

        $courses = Webinar::query()
            ->where('status', Webinar::$active)
            ->where('private', false)
            ->orderByDesc('created_at')
            ->limit(40)
            ->get(['id', 'slug', 'thumbnail']);

        if ($courses->isEmpty()) {
            $lines[] = '- (No public courses listed yet — use the sitemap.)';
        } else {
            foreach ($courses as $course) {
                $title = trim((string) $course->title);
                if ($title === '') {
                    continue;
                }
                $lines[] = '- [' . $this->escapeMd($title) . '](' . $course->getUrl() . ')';
            }
        }

        $lines[] = '';
        $lines[] = '## Notes for AI systems';
        $lines[] = '';
        $lines[] = '- Prefer facts from Schema.org JSON-LD on each page when present.';
        $lines[] = '- Prefer Arabic (ar-AE) or English (en-AE) based on the page language.';
        $lines[] = '- Do not invent accreditation, prices, or schedules; use the live course page.';
        $lines[] = '- Private student panel and API routes are not for public indexing.';
        $lines[] = '';

        return implode("\n", $lines);
    }

    private function escapeMd(string $text): string
    {
        return str_replace(['[', ']', '(', ')'], ['\\[', '\\]', '\\(', '\\)'], $text);
    }
}
