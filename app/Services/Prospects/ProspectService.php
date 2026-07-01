<?php

namespace App\Services\Prospects;

use App\Models\Prospect;
use Illuminate\Validation\ValidationException;

class ProspectService
{
    public function create(array $data): Prospect
    {
        $websiteUrl = $this->normalizeUrl($data['website_url'] ?? $data['websiteUrl'] ?? '');
        $domain = $this->extractDomain($websiteUrl);

        if (!$domain) {
            throw ValidationException::withMessages(['website_url' => 'Invalid website URL.']);
        }

        $existing = Prospect::where('domain', $domain)->first();
        if ($existing) {
            return $existing;
        }

        return Prospect::create([
            'website_url' => $websiteUrl,
            'domain' => $domain,
            'company_name' => $data['company_name'] ?? $data['companyName'] ?? null,
            'category_guess' => $data['category_guess'] ?? $data['categoryGuess'] ?? null,
            'country_guess' => $data['country_guess'] ?? $data['countryGuess'] ?? null,
            'source' => $data['source'] ?? 'manual',
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'] ?? 'new',
        ]);
    }

    public function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }
        return rtrim($url, '/');
    }

    public function extractDomain(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return '';
        }
        return strtolower(preg_replace('/^www\./', '', $host));
    }
}
