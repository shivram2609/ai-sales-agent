<?php

namespace App\Services\Discovery;

use App\Models\DiscoveryCampaign;

class DiscoveryFilterService
{
    private array $excludedDomains = [
        'reddit.com',
        'quora.com',
        'linkedin.com',
        'facebook.com',
        'instagram.com',
        'twitter.com',
        'x.com',
        'youtube.com',
        'tiktok.com',
        'clutch.co',
        'designrush.com',
        'upwork.com',
        'fiverr.com',
        'goodfirms.co',
        'sortlist.com',
        'agencyspotter.com',
        'themanifest.com',
        'land-book.com',
        'landbook.com',
        'awwwards.com',
        'behance.net',
        'dribbble.com',
        'pinterest.com',
        'producthunt.com',
        'crunchbase.com',
        'tracxn.com',
        'webflow.io',
        'webflow.com',
    ];

    private array $badPathParts = [
        '/blog',
        '/blogs',
        '/post',
        '/posts',
        '/article',
        '/articles',
        '/news',
        '/resources',
        '/insights',
        '/guide',
        '/guides',
        '/directory',
        '/directories',
        '/companies',
        '/developers',
        '/reviews',
        '/review',
        '/compare',
        '/comparison',
        '/rankings',
        '/ranking',
        '/best-',
        '/top-',
        '/network/',
        '/profile/',
        '/profiles/',
        '/listing/',
        '/lists/',
        '/template',
        '/templates',
        '/theme',
        '/themes',
        '/jobs',
        '/careers',
        '/marketplace',
    ];

    private array $badTitleOrSnippetTerms = [
        'best webflow agencies',
        'top webflow agencies',
        'best webflow developers',
        'top webflow developers',
        'webflow agencies compared',
        'which one should you hire',
        'list of',
        'ranking',
        'ranked',
        'reviewed',
        'compared',
        'directory',
        'marketplace',
        'template',
        'website template',
        'theme',
        'job',
        'career',
        'hiring',
    ];

    private array $positiveTerms = [
        'agency',
        'studio',
        'design agency',
        'development agency',
        'webflow agency',
        'ux agency',
        'product design',
        'services',
        'portfolio',
        'case study',
        'case studies',
        'contact',
        'about',
        'work',
        'clients',
        'saas',
        'b2b',
        'startup',
        'startups',
    ];

    public function evaluate(array $result, string $sourceQuery, DiscoveryCampaign $campaign): array
    {
        $url = $result['link'] ?? '';
        $title = $result['title'] ?? '';
        $snippet = $result['snippet'] ?? '';
        $source = $result['source'] ?? '';

        $domain = $this->normalizeDomain($url);
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));
        $haystack = strtolower(trim($title . ' ' . $snippet . ' ' . $source . ' ' . $url));

        if (! $url || ! $domain) {
            return $this->reject('Missing URL or domain.', $domain);
        }

        if ($this->isExcludedDomain($domain)) {
            return $this->reject('Excluded domain: ' . $domain, $domain);
        }

        if ($this->looksLikeStagingOrDemo($domain, $url, $haystack)) {
            return $this->reject('Rejected staging/demo/template URL.', $domain);
        }

        if ($this->hasBadPath($path)) {
            return $this->reject('Rejected non-official path pattern: ' . $path, $domain);
        }

        if ($this->hasBadTitleOrSnippet($haystack)) {
            return $this->reject('Rejected listicle/directory/template style result.', $domain);
        }

        $score = $this->calculateScore($domain, $path, $haystack, $campaign);

        if ($score < 55) {
            return $this->reject('Low official-site confidence score.', $domain, $score);
        }

        return [
            'accepted' => true,
            'filter_status' => $score >= 80 ? 'strong_candidate' : 'possible_candidate',
            'relevance_score' => $score,
            'reason' => 'Accepted as likely official agency/company website.',
            'normalized_domain' => $domain,
            'company_name_guess' => $this->guessCompanyName($result, $domain),
            'category_guess' => $this->guessCategory($haystack, $campaign),
            'region_guess' => $this->guessRegion($haystack, $sourceQuery, $campaign),
        ];
    }

    private function reject(string $reason, ?string $domain = null, int $score = 0): array
    {
        return [
            'accepted' => false,
            'filter_status' => 'rejected',
            'relevance_score' => $score,
            'reason' => $reason,
            'normalized_domain' => $domain,
            'company_name_guess' => null,
            'category_guess' => null,
            'region_guess' => null,
        ];
    }

    private function calculateScore(string $domain, string $path, string $haystack, DiscoveryCampaign $campaign): int
    {
        $score = 25;

        if ($path === '' || $path === '/') {
            $score += 25;
        }

        if ($this->hasOfficialBusinessPath($path)) {
            $score += 20;
        }

        foreach ($this->positiveTerms as $term) {
            if (str_contains($haystack, $term)) {
                $score += 8;
            }
        }

        foreach ($this->toArray($campaign->service_focus) as $service) {
            if ($service && str_contains($haystack, strtolower($service))) {
                $score += 10;
            }
        }

        foreach ($this->toArray($campaign->target_audience) as $audience) {
            if ($audience && str_contains($haystack, strtolower($audience))) {
                $score += 8;
            }
        }

        if (str_contains($domain, 'staging') || str_contains($domain, 'demo') || str_contains($domain, 'template')) {
            $score -= 50;
        }

        return max(0, min(100, $score));
    }

    private function hasOfficialBusinessPath(string $path): bool
    {
        if ($path === '' || $path === '/') {
            return true;
        }

        $goodParts = [
            '/services',
            '/service',
            '/work',
            '/portfolio',
            '/case-studies',
            '/case-study',
            '/about',
            '/contact',
            '/webflow',
            '/webflow-agency',
            '/ux-design',
            '/product-design',
            '/development',
            '/solutions',
            '/what-we-do',
        ];

        foreach ($goodParts as $part) {
            if (str_contains($path, $part)) {
                return true;
            }
        }

        return false;
    }

    private function hasBadPath(string $path): bool
    {
        foreach ($this->badPathParts as $part) {
            if (str_contains($path, $part)) {
                return true;
            }
        }

        return false;
    }

    private function hasBadTitleOrSnippet(string $haystack): bool
    {
        foreach ($this->badTitleOrSnippetTerms as $term) {
            if (str_contains($haystack, $term)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeStagingOrDemo(string $domain, string $url, string $haystack): bool
    {
        $combined = strtolower($domain . ' ' . $url . ' ' . $haystack);

        $badSignals = [
            'staging',
            'demo.',
            '-demo',
            'template',
            'webflow.io',
            'vercel.app',
            'netlify.app',
            'pages.dev',
            'myshopify.com',
        ];

        foreach ($badSignals as $signal) {
            if (str_contains($combined, $signal)) {
                return true;
            }
        }

        return false;
    }

    private function isExcludedDomain(string $domain): bool
    {
        foreach ($this->excludedDomains as $excludedDomain) {
            if ($domain === $excludedDomain || str_ends_with($domain, '.' . $excludedDomain)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeDomain(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! $host) {
            return null;
        }

        $host = strtolower($host);

        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        return $host;
    }

    private function guessCompanyName(array $result, string $domain): string
    {
        $source = trim((string) ($result['source'] ?? ''));

        if ($source && ! $this->isBadCompanyGuess($source)) {
            return $source;
        }

        $title = trim((string) ($result['title'] ?? ''));

        if ($title) {
            $title = preg_replace('/\s+[-|–—]\s+.*/', '', $title);
            $title = preg_replace('/\s+\|\s+.*/', '', $title);
            $title = trim($title);

            if ($title && ! $this->isBadCompanyGuess($title)) {
                return $title;
            }
        }

        return ucfirst(explode('.', $domain)[0]);
    }

    private function isBadCompanyGuess(string $value): bool
    {
        $value = strtolower($value);

        $bad = [
            'instagram',
            'linkedin',
            'facebook',
            'reddit',
            'clutch',
            'webflow',
            'landbook',
            'land-book',
            'website template',
            'best',
            'top',
            'directory',
        ];

        foreach ($bad as $term) {
            if (str_contains($value, $term)) {
                return true;
            }
        }

        return false;
    }

    private function guessCategory(string $haystack, DiscoveryCampaign $campaign): string
    {
        foreach ($this->toArray($campaign->service_focus) as $service) {
            if ($service && str_contains($haystack, strtolower($service))) {
                return $service . ' agency';
            }
        }

        if (str_contains($haystack, 'webflow')) {
            return 'Webflow agency';
        }

        if (str_contains($haystack, 'ux')) {
            return 'UX agency';
        }

        if (str_contains($haystack, 'product design')) {
            return 'Product design agency';
        }

        if (str_contains($haystack, 'development')) {
            return 'Development agency';
        }

        return 'Agency/company';
    }

    private function guessRegion(string $haystack, string $sourceQuery, DiscoveryCampaign $campaign): ?string
    {
        foreach ($this->toArray($campaign->regions_json) as $region) {
            if (str_contains($haystack, strtolower($region)) || str_contains(strtolower($sourceQuery), strtolower($region))) {
                return $region;
            }
        }

        return $this->toArray($campaign->regions_json)[0] ?? null;
    }

    private function toArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            return preg_split('/[\r\n,]+/', $value) ?: [];
        }

        return [];
    }
}