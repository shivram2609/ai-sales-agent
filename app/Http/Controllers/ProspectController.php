<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Services\Analyzer\ProspectAnalyzerService;
use App\Services\Crawler\WebsiteCrawlerService;
use App\Services\Drafts\DraftGeneratorService;
use App\Services\Drafts\DraftQualityService;
use App\Services\ProofMatcher\ProofMatcherService;
use App\Services\Prospects\ProspectService;
use Illuminate\Http\Request;

class ProspectController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
	{
		$allowedSorts = [
			'company_name',
			'domain',
			'status',
			'fit_score',
			'source',
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

		$query = Prospect::query();

		if ($request->filled('search')) {
			$search = trim($request->get('search'));

			$query->where(function ($q) use ($search) {
				$q->where('company_name', 'like', "%{$search}%")
					->orWhere('domain', 'like', "%{$search}%")
					->orWhere('website_url', 'like', "%{$search}%")
					->orWhere('category_guess', 'like', "%{$search}%")
					->orWhere('country_guess', 'like', "%{$search}%")
					->orWhere('notes', 'like', "%{$search}%");
			});
		}

		if ($request->filled('status')) {
			$query->where('status', $request->get('status'));
		}

		if ($request->filled('source')) {
			$query->where('source', $request->get('source'));
		}

		$perPage = (int) $request->get('per_page', 20);

		if (! in_array($perPage, [20, 50, 100], true)) {
			$perPage = 20;
		}

		$prospects = $query
			->orderBy($sort, $direction)
			->paginate($perPage)
			->withQueryString();

		$statuses = Prospect::query()
			->whereNotNull('status')
			->distinct()
			->orderBy('status')
			->pluck('status');

		$sources = Prospect::query()
			->whereNotNull('source')
			->distinct()
			->orderBy('source')
			->pluck('source');

		return view('prospects.index', [
			'prospects' => $prospects,
			'statuses' => $statuses,
			'sources' => $sources,
			'sort' => $sort,
			'direction' => $direction,
			'perPage' => $perPage,
		]);
	}
	
    public function create() { return view('prospects.create'); }
	
    public function store(Request $request, ProspectService $service)
    {
        $data = $request->validate(['website_url' => 'required|string', 'company_name' => 'nullable|string', 'category_guess' => 'nullable|string', 'country_guess' => 'nullable|string', 'source' => 'nullable|string', 'notes' => 'nullable|string']);
        $prospect = $service->create($data);
        return redirect()->route('prospects.show', $prospect)->with('success', 'Prospect saved.');
    }
    public function show(Prospect $prospect)
	{
		$prospect->load([
			'pages' => fn ($query) => $query->latest(),
			'analyses' => fn ($query) => $query->latest(),
			'drafts' => fn ($query) => $query->latest(),
			'contacts' => fn ($query) => $query
				->orderByDesc('is_primary')
				->latest(),
		]);

		return view('prospects.show', compact('prospect'));
	}
	
    public function crawl(Prospect $prospect, WebsiteCrawlerService $service) { 
	
		$service->crawlHomepage($prospect); 
	
		return back()->with('success', 'Homepage crawled.'); 
	
	}
    
	public function analyze(Prospect $prospect, ProspectAnalyzerService $service) { 
	
		$service->analyze($prospect); 
		
		return back()->with('success', 'Prospect analyzed.'); 
	}
	
    public function matchProof(Prospect $prospect, ProofMatcherService $service) { 
	
		$service->match($prospect); 
		
		return back()->with('success', 'Proof links matched.'); 
	}
    
	public function generateDraft(Prospect $prospect, DraftGeneratorService $service) { 

		$contact = $prospect->contacts()
			->where('status', \App\Models\ProspectContact::STATUS_ACTIVE)
			->orderByDesc('is_primary')
			->first();

		$service->generate($prospect, $contact);
		return back()->with('success', 'Draft generated.'); 
	}
	
    public function qualityLatestDraft(Prospect $prospect, DraftQualityService $service)
    {
        $draft = $prospect->drafts()->latest()->firstOrFail();
        $service->check($draft);
        return back()->with('success', 'Draft quality checked.');
    }
}
