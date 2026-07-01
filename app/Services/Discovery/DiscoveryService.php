<?php

namespace App\Services\Discovery;

use App\Models\DiscoveryCampaign;
use App\Models\DiscoveryResult;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DiscoveryService
{
    public function __construct(
        private DiscoveryQueryBuilderService $queryBuilder,
        private SerpApiService $serpApiService,
        private DiscoveryFilterService $filterService
    ) {
    }

    public function runCampaign(DiscoveryCampaign $campaign): array
    {
        $campaign->update([
            'status' => 'running',
        ]);

        $queries = $this->queryBuilder->build($campaign);

        $summary = [
            'queries' => count($queries),
            'accepted' => 0,
            'rejected_skipped' => 0,
            'duplicates' => 0,
            'errors' => 0,
        ];

        $seenDomains = [];

        try {
            foreach ($queries as $query) {
                $results = $this->serpApiService->search(
                    $query,
                    (int) $campaign->max_results_per_query
                );

                foreach ($results as $index => $result) {
                    $filtered = $this->filterService->evaluate($result, $query, $campaign);

                    if (! $filtered['accepted']) {
                        $summary['rejected_skipped']++;
                        continue;
                    }

                    $domain = $filtered['normalized_domain'];

                    if (! $domain) {
                        $summary['rejected_skipped']++;
                        continue;
                    }

                    if (in_array($domain, $seenDomains, true)) {
                        $summary['duplicates']++;
                        continue;
                    }

                    $seenDomains[] = $domain;

                    $alreadyExists = DiscoveryResult::query()
                        ->where('discovery_campaign_id', $campaign->id)
                        ->where('normalized_domain', $domain)
                        ->exists();

                    if ($alreadyExists) {
                        $summary['duplicates']++;
                        continue;
                    }

                    DiscoveryResult::create([
                        'discovery_campaign_id' => $campaign->id,
                        'source_query' => $query,
                        'result_title' => $result['title'] ?? null,
                        'result_url' => $result['link'] ?? null,
                        'normalized_domain' => $domain,
                        'snippet' => $result['snippet'] ?? null,
                        'source_engine' => $campaign->source_engine ?: 'serpapi_google',
                        'rank' => $result['position'] ?? ($index + 1),
                        'company_name_guess' => $filtered['company_name_guess'],
                        'category_guess' => $filtered['category_guess'],
                        'region_guess' => $filtered['region_guess'],
                        'relevance_score' => $filtered['relevance_score'],
                        'filter_status' => $filtered['filter_status'],
                        'reason' => $filtered['reason'],
                        'raw' => $result,
                    ]);

                    $summary['accepted']++;
                }
            }

            $campaign->update([
                'status' => 'completed',
            ]);

            Log::info('Discovery campaign completed.', [
                'campaign_id' => $campaign->id,
                'summary' => $summary,
            ]);

            return $summary;
        } catch (Throwable $e) {
            $campaign->update([
                'status' => 'failed',
            ]);

            Log::error('Discovery campaign failed inside service.', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
	
	
	public function convertToProspect(DiscoveryResult $result): Prospect
	{
		return DB::transaction(function () use ($result) {
			$domain = $result->normalized_domain ?: $this->domainFromUrl($result->result_url);

			$prospect = Prospect::query()
				->where('domain', $domain)
				->orWhere('website_url', $result->result_url)
				->first();

			if (! $prospect) {
				$prospect = Prospect::create([
					'website_url' => $result->result_url,
					'domain' => $domain,
					'company_name' => $result->company_name_guess ?: $this->companyNameFromDomain($domain),
					'category_guess' => $result->category_guess ?: 'unknown',
					'country_guess' => $result->region_guess ?: null,
					'source' => 'discovery',
					'status' => 'draft_created',
					'fit_score' => $result->relevance_score ?? 0,
					'notes' => $this->buildProspectNotes($result),
				]);
			} else {
				$prospect->update([
					'website_url' => $prospect->website_url ?: $result->result_url,
					'company_name' => $prospect->company_name ?: ($result->company_name_guess ?: $this->companyNameFromDomain($domain)),
					'category_guess' => $prospect->category_guess ?: ($result->category_guess ?: 'unknown'),
					'country_guess' => $prospect->country_guess ?: ($result->region_guess ?: null),
					'source' => $prospect->source ?: 'discovery',
					'fit_score' => max((int) ($prospect->fit_score ?? 0), (int) ($result->relevance_score ?? 0)),
				]);
			}

			$result->update([
				'converted_to_prospect' => true,
				'prospect_id' => $prospect->id,
			]);

			return $prospect;
		});
	}

	public function convertStrongCandidates(DiscoveryCampaign $campaign): int
	{
		$count = 0;

		$results = DiscoveryResult::query()
			->where('discovery_campaign_id', $campaign->id)
			->where('filter_status', 'strong_candidate')
			->where(function ($query) {
				$query->where('converted_to_prospect', false)
					->orWhereNull('converted_to_prospect');
			})
			->get();

		foreach ($results as $result) {
			$this->convertToProspect($result);
			$count++;
		}

		return $count;
	}

	private function buildProspectNotes(DiscoveryResult $result): string
	{
		$lines = [
			'Discovered from search query: ' . $result->source_query,
			'Discovery result title: ' . $result->result_title,
			'Snippet: ' . $result->snippet,
			'Filter status: ' . $result->filter_status,
			'Reason: ' . $result->reason,
			'Discovery result ID: ' . $result->id,
		];

		return implode("\n", array_filter($lines));
	}

	private function domainFromUrl(?string $url): ?string
	{
		if (! $url) {
			return null;
		}

		$host = parse_url($url, PHP_URL_HOST);

		if (! $host) {
			return null;
		}

		return preg_replace('/^www\./', '', strtolower($host));
	}

	private function companyNameFromDomain(?string $domain): ?string
	{
		if (! $domain) {
			return null;
		}

		$base = explode('.', $domain)[0] ?? $domain;

		return Str::of($base)
			->replace(['-', '_'], ' ')
			->title()
			->toString();
	}
}