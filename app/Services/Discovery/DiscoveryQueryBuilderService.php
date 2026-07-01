<?php

namespace App\Services\Discovery;

use App\Models\DiscoveryCampaign;

class DiscoveryQueryBuilderService
{
    private int $maxSmartQueries = 6;

    private array $defaultExcludeTerms = [
        'reddit',
        'quora',
        'clutch',
        'designrush',
        'upwork',
        'fiverr',
        'instagram',
        'facebook',
        'linkedin',
        'youtube',
        'blog',
        'article',
        'articles',
        'post',
        'posts',
        'best',
        'top',
        'list',
        'ranking',
        'reviewed',
        'compared',
        'template',
        'templates',
        'jobs',
        'careers',

    ];

    public function build(DiscoveryCampaign $campaign): array
    {
        if ($campaign->query_mode === 'exact') {
            return $this->buildExactQueries($campaign);
        }

        return $this->buildSmartQueries($campaign);
    }

    private function buildExactQueries(DiscoveryCampaign $campaign): array
    {
        $queries = $this->toArray($campaign->exact_queries_json);

        return array_slice($this->uniqueClean($queries), 0, 10);
    }

    private function buildSmartQueries(DiscoveryCampaign $campaign): array
    {
        $services = $this->toArray($campaign->service_focus);
        $audiences = $this->toArray($campaign->target_audience);
        $regions = $this->toArray($campaign->regions_json);
        $cities = $this->toArray($campaign->cities_json);

        if (! count($services)) {
            $services = ['Webflow'];
        }

        if (! count($audiences)) {
            $audiences = ['SaaS'];
        }

        if (! count($regions)) {
            $regions = ['USA'];
        }

        $negativePart = $this->negativeQueryPart($campaign);
        $queries = [];

        foreach ($services as $service) {
            foreach ($audiences as $audience) {
                foreach ($regions as $region) {
                    $queries[] = '"' . $service . ' agency" "' . $audience . '" "' . $region . '" ' . $negativePart;
                    $queries[] = '"' . $service . '" "' . $audience . '" "services" "' . $region . '" ' . $negativePart;
                    $queries[] = '"' . $audience . '" "' . $service . ' agency" "contact" "' . $region . '" ' . $negativePart;
                    $queries[] = '"' . $service . '" "' . $audience . '" "portfolio" "contact" "' . $region . '" ' . $negativePart;

                    if (count($queries) >= $this->maxSmartQueries) {
                        break 3;
                    }
                }
            }
        }

        foreach ($cities as $city) {
            foreach ($services as $service) {
                foreach ($audiences as $audience) {
                    $queries[] = '"' . $service . ' agency" "' . $audience . '" "' . $city . '" ' . $negativePart;

                    if (count($queries) >= $this->maxSmartQueries) {
                        break 3;
                    }
                }
            }
        }

        return array_slice($this->uniqueClean($queries), 0, $this->maxSmartQueries);
    }

    private function negativeQueryPart(DiscoveryCampaign $campaign): string
    {
        $campaignExcludes = $this->toArray($campaign->exclude_terms_json);
        $terms = array_merge($campaignExcludes, $this->defaultExcludeTerms);

        return collect($this->uniqueClean($terms))
            ->map(fn ($term) => '-' . trim($term))
            ->implode(' ');
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

    private function uniqueClean(array $items): array
    {
        return collect($items)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}