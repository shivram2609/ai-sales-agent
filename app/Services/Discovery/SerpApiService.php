<?php

namespace App\Services\Discovery;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SerpApiService
{
    public function search(string $query, int $maxResults = 3): array
    {
        $apiKey = env('SERPAPI_API_KEY');

        if (! $apiKey) {
            throw new RuntimeException('SERPAPI_API_KEY is missing in .env');
        }

        $maxResults = max(1, min($maxResults, 10));

        $response = Http::timeout(20)
            ->retry(1, 1000)
            ->get('https://serpapi.com/search.json', [
                'engine' => 'google',
                'q' => $query,
                'num' => $maxResults,
                'api_key' => $apiKey,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('SERPAPI request failed: ' . $response->body());
        }

        $data = $response->json();

        return $data['organic_results'] ?? [];
    }
}