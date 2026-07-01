<?php

namespace App\Http\Controllers;

use App\Models\DiscoveryCampaign;
use App\Models\DiscoveryResult;
use App\Services\Discovery\DiscoveryQueryBuilderService;
use App\Services\Discovery\DiscoveryService;
use App\Jobs\RunDiscoveryCampaignJob;
use Illuminate\Http\Request;

class DiscoveryController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
	{
		$allowedSorts = [
			'name',
			'query_mode',
			'source_engine',
			'status',
			'max_results_per_query',
			'created_at',
			'updated_at',
		];

		$sort = $request->get('sort', 'created_at');
		$direction = $request->get('direction', 'desc');

		if (! in_array($sort, $allowedSorts, true)) {
			$sort = 'created_at';
		}

		if (! in_array($direction, ['asc', 'desc'], true)) {
			$direction = 'desc';
		}

		$query = \App\Models\DiscoveryCampaign::query();

		if ($request->filled('search')) {
			$search = trim($request->get('search'));

			$query->where(function ($q) use ($search) {
				$q->where('name', 'like', "%{$search}%")
					->orWhere('service_focus', 'like', "%{$search}%")
					->orWhere('target_audience', 'like', "%{$search}%")
					->orWhere('regions_json', 'like', "%{$search}%")
					->orWhere('cities_json', 'like', "%{$search}%")
					->orWhere('notes', 'like', "%{$search}%");
			});
		}

		if ($request->filled('status')) {
			$query->where('status', $request->get('status'));
		}

		if ($request->filled('query_mode')) {
			$query->where('query_mode', $request->get('query_mode'));
		}

		if ($request->filled('source_engine')) {
			$query->where('source_engine', $request->get('source_engine'));
		}

		$perPage = (int) $request->get('per_page', 20);

		if (! in_array($perPage, [20, 50, 100], true)) {
			$perPage = 20;
		}

		$campaigns = $query
			->withCount('results')
			->orderBy($sort, $direction)
			->paginate($perPage)
			->withQueryString();

		$statuses = \App\Models\DiscoveryCampaign::query()
			->whereNotNull('status')
			->distinct()
			->orderBy('status')
			->pluck('status');

		$queryModes = \App\Models\DiscoveryCampaign::query()
			->whereNotNull('query_mode')
			->distinct()
			->orderBy('query_mode')
			->pluck('query_mode');

		$sourceEngines = \App\Models\DiscoveryCampaign::query()
			->whereNotNull('source_engine')
			->distinct()
			->orderBy('source_engine')
			->pluck('source_engine');

		return view('discovery.index', [
			'campaigns' => $campaigns,
			'statuses' => $statuses,
			'queryModes' => $queryModes,
			'sourceEngines' => $sourceEngines,
			'sort' => $sort,
			'direction' => $direction,
			'perPage' => $perPage,
		]);
	}
    
	
	public function create() { return view('discovery.create'); }
	
    public function store(\Illuminate\Http\Request $request)
	{
		$validated = $request->validate([
			'name' => ['required', 'string', 'max:191'],
			'query_mode' => ['required', 'in:smart,exact'],
			'source_engine' => ['required', 'string', 'max:50'],
			'service_focus' => ['nullable', 'string'],
			'target_audience' => ['nullable', 'string'],
			'regions' => ['nullable', 'string'],
			'cities' => ['nullable', 'string'],
			'exclude_terms' => ['nullable', 'string'],
			'exact_queries' => ['nullable', 'string'],
			'max_results_per_query' => ['required', 'integer', 'min:1', 'max:10'],
			'notes' => ['nullable', 'string'],
		]);

		$toArray = function (?string $value): array {
			if (! $value) {
				return [];
			}

			return collect(preg_split('/[\r\n,]+/', $value))
				->map(fn ($item) => trim($item))
				->filter()
				->unique()
				->values()
				->all();
		};

		$campaign = \App\Models\DiscoveryCampaign::create([
			'name' => $validated['name'],
			'query_mode' => $validated['query_mode'],
			'source_engine' => $validated['source_engine'] ?: 'serpapi_google',
			'service_focus' => $toArray($validated['service_focus'] ?? null),
			'target_audience' => $toArray($validated['target_audience'] ?? null),
			'regions_json' => $toArray($validated['regions'] ?? null),
			'cities_json' => $toArray($validated['cities'] ?? null),
			'exclude_terms_json' => $toArray($validated['exclude_terms'] ?? null),
			'exact_queries_json' => $toArray($validated['exact_queries'] ?? null),
			'max_results_per_query' => $validated['max_results_per_query'],
			'status' => 'new',
			'notes' => $validated['notes'] ?? null,
		]);

		return redirect()
			->route('discovery.show', $campaign)
			->with('success', 'Discovery campaign created successfully.');
	}
    public function show(
		\Illuminate\Http\Request $request,
		\App\Models\DiscoveryCampaign $campaign,
		\App\Services\Discovery\DiscoveryQueryBuilderService $builder
	) {
		$allowedSorts = [
			'relevance_score',
			'rank',
			'result_title',
			'normalized_domain',
			'filter_status',
			'created_at',
			'updated_at',
		];

		$sort = $request->get('sort', 'relevance_score');
		$direction = $request->get('direction', 'desc');

		if (! in_array($sort, $allowedSorts, true)) {
			$sort = 'relevance_score';
		}

		if (! in_array($direction, ['asc', 'desc'], true)) {
			$direction = 'desc';
		}

		$resultsQuery = $campaign->results()->with('prospect');

		if ($request->filled('search')) {
			$search = trim($request->get('search'));

			$resultsQuery->where(function ($q) use ($search) {
				$q->where('result_title', 'like', "%{$search}%")
					->orWhere('result_url', 'like', "%{$search}%")
					->orWhere('normalized_domain', 'like', "%{$search}%")
					->orWhere('snippet', 'like', "%{$search}%")
					->orWhere('company_name_guess', 'like', "%{$search}%")
					->orWhere('reason', 'like', "%{$search}%")
					->orWhere('source_query', 'like', "%{$search}%");
			});
		}

		if ($request->filled('filter_status')) {
			$resultsQuery->where('filter_status', $request->get('filter_status'));
		}

		if ($request->filled('converted')) {
			$resultsQuery->where('converted_to_prospect', $request->get('converted') === 'yes');
		}

		if ($request->filled('min_score')) {
			$resultsQuery->where('relevance_score', '>=', (int) $request->get('min_score'));
		}

		$perPage = (int) $request->get('per_page', 20);

		if (! in_array($perPage, [20, 50, 100], true)) {
			$perPage = 20;
		}

		$results = $resultsQuery
			->orderBy($sort, $direction)
			->paginate($perPage)
			->withQueryString();

		$baseResults = $campaign->results();

		$stats = [
			'total' => (clone $baseResults)->count(),
			'strong' => (clone $baseResults)->where('filter_status', 'strong_candidate')->count(),
			'possible' => (clone $baseResults)->where('filter_status', 'possible_candidate')->count(),
			'rejected' => (clone $baseResults)->where('filter_status', 'rejected')->count(),
			'converted' => (clone $baseResults)->where('converted_to_prospect', true)->count(),
		];

		$filterStatuses = $campaign->results()
			->whereNotNull('filter_status')
			->distinct()
			->orderBy('filter_status')
			->pluck('filter_status');

		try {
			$queries = $builder->build($campaign);
		} catch (\Throwable $e) {
			$queries = [];
		}

		$logs = $campaign->logs()
			->latest()
			->limit(20)
			->get();

		return view('discovery.show', [
			'campaign' => $campaign,
			'queries' => $queries,
			'results' => $results,
			'stats' => $stats,
			'filterStatuses' => $filterStatuses,
			'logs' => $logs,
			'sort' => $sort,
			'direction' => $direction,
			'perPage' => $perPage,
		]);
	}
    
	public function run(\App\Models\DiscoveryCampaign $campaign)
	{
		if (in_array($campaign->status, ['queued', 'running'], true)) {
			return redirect()
				->route('discovery.show', $campaign)
				->with('success', 'Discovery campaign is already queued or running.');
		}

		$campaign->update([
			'status' => 'queued',
		]);

		RunDiscoveryCampaignJob::dispatch($campaign->id);

		return redirect()
			->route('discovery.show', $campaign)
			->with('success', 'Discovery campaign queued successfully. Keep the queue worker running in terminal.');
	}
	
    public function convert(DiscoveryResult $result, DiscoveryService $service) { $prospect = $service->convertToProspect($result); return redirect()->route('prospects.show', $prospect)->with('success', 'Discovery result converted to prospect.'); }
    public function convertStrong(DiscoveryCampaign $campaign, DiscoveryService $service) { $count = $service->convertStrongCandidates($campaign); return back()->with('success', "Converted {$count} discovery results to prospects."); }
    private function lines(string $text): array { return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $text)))); }
}
