<?php

namespace App\Services\Crawler;

use App\Models\AgentLog;
use App\Models\Prospect;
use App\Models\ProspectPage;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WebsiteCrawlerService
{
    public function crawlHomepage(Prospect $prospect): ProspectPage
    {
        $prospect->update(['status' => 'crawling']);

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 AI Sales Agent Crawler; +https://www.zestminds.com',
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                ->get($prospect->website_url);

            if (!$response->successful()) {
                throw new RuntimeException('Homepage fetch failed with status '.$response->status());
            }

            $html = $response->body();
            $parsed = $this->parseHtml($html, $prospect->website_url);

            $page = ProspectPage::create([
                'prospect_id' => $prospect->id,
                'page_type' => 'homepage',
                'url' => $prospect->website_url,
                'title' => $parsed['title'],
                'meta_description' => $parsed['meta_description'],
                'headings_json' => $parsed['headings'],
                'links_json' => $parsed['links'],
                'emails_json' => $parsed['emails'],
                'main_text' => $parsed['main_text'],
                'raw_html_snapshot' => mb_substr($html, 0, 200000),
                'crawl_status' => 'completed',
            ]);

            $prospect->update(['status' => 'crawled']);
            AgentLog::create([
                'prospect_id' => $prospect->id,
                'step_name' => 'crawl_homepage',
                'input' => ['url' => $prospect->website_url],
                'output' => [
                    'page_id' => $page->id,
                    'title' => $page->title,
                    'text_length' => strlen($page->main_text ?? ''),
                ],
            ]);

            return $page;
        } catch (\Throwable $e) {
            $prospect->update(['status' => 'crawl_failed']);
            AgentLog::create([
                'prospect_id' => $prospect->id,
                'step_name' => 'crawl_homepage_failed',
                'input' => ['url' => $prospect->website_url],
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function parseHtml(string $html, string $baseUrl): array
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
        $xpath = new \DOMXPath($dom);

        $title = trim($xpath->evaluate('string(//title)'));
        $meta = trim($xpath->evaluate('string(//meta[translate(@name,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="description"]/@content)'));

        $headings = [];
        foreach (['h1', 'h2', 'h3'] as $tag) {
            foreach ($dom->getElementsByTagName($tag) as $node) {
                $text = trim(preg_replace('/\s+/', ' ', $node->textContent));
                if ($text) {
                    $headings[] = ['tag' => $tag, 'text' => mb_substr($text, 0, 300)];
                }
            }
        }

        $links = [];
        foreach ($dom->getElementsByTagName('a') as $a) {
            $href = trim($a->getAttribute('href'));
            $text = trim(preg_replace('/\s+/', ' ', $a->textContent));
            if ($href) {
                $links[] = ['href' => $href, 'text' => mb_substr($text, 0, 200)];
            }
        }

        $text = preg_replace('/\s+/', ' ', trim($dom->textContent ?? ''));
        preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $html.' '.$text, $matches);
        $emails = array_values(array_unique($matches[0] ?? []));

        return [
            'title' => mb_substr($title, 0, 500),
            'meta_description' => $meta,
            'headings' => array_slice($headings, 0, 100),
            'links' => array_slice($links, 0, 200),
            'emails' => $emails,
            'main_text' => mb_substr($text, 0, 100000),
        ];
    }
}
