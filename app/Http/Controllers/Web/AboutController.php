<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class AboutController extends Controller
{
    public function index()
    {
        $contactSettings = getContactPageSettings();

        $whatsappLink = null;
        foreach (getSocials() as $social) {
            if (strtolower($social['title'] ?? '') === 'whatsapp' && !empty($social['link'])) {
                $whatsappLink = $social['link'];
                break;
            }
        }

        $phoneLinks = $this->parseContactLinks($contactSettings['phones'] ?? '', 'tel');
        $emailLinks = $this->parseContactLinks($contactSettings['emails'] ?? '', 'mailto');

        $schemaSameAs = [];
        foreach (getSocials() as $social) {
            if (!empty($social['link'])) {
                $schemaSameAs[] = $social['link'];
            }
        }

        $schemaPhones = array_map(fn ($item) => $item['label'], $phoneLinks);
        $schemaEmails = array_map(fn ($item) => $item['label'], $emailLinks);

        return view('web.default.pages.about', [
            'pageTitle' => 'عن أكاديمية البيان | تدريب مهني معتمد في دبي والإمارات',
            'pageTitleFull' => true,
            'pageDescription' => 'تعرف على أكاديمية البيان، رائد التدريب المهني واللغات في دبي، نتميز بتقديم برامج عملية، شهادات معتمدة، ومسارات تعليمية مصممة خصيصاً لـ ترقية مهاراتك اليوم.',
            'pageRobot' => getPageRobot('about'),
            'whatsappLink' => $whatsappLink,
            'phoneLinks' => $phoneLinks,
            'emailLinks' => $emailLinks,
            'schemaSameAs' => array_values(array_unique($schemaSameAs)),
            'schemaPhones' => $schemaPhones,
            'schemaEmails' => $schemaEmails,
        ]);
    }

    /**
     * @return array<int, array{label: string, href: string}>
     */
    private function parseContactLinks(string $raw, string $scheme): array
    {
        $items = array_filter(array_map('trim', preg_split('/[,;\n]+/', $raw) ?: []));
        $links = [];

        foreach ($items as $item) {
            if ($scheme === 'tel') {
                $digits = preg_replace('/[^\d+]/', '', $item);
                $href = $digits !== '' ? 'tel:' . $digits : '#';
            } else {
                $href = filter_var($item, FILTER_VALIDATE_EMAIL) ? 'mailto:' . $item : '#';
            }

            $links[] = [
                'label' => $item,
                'href' => $href,
            ];
        }

        return $links;
    }
}
