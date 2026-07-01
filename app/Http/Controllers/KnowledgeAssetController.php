<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeAsset;
use App\Services\Knowledge\KnowledgeAssetSeederService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KnowledgeAssetController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = [
            'title',
            'asset_type',
            'is_active',
            'created_at',
            'updated_at',
        ];

        $sort = $request->get('sort', 'updated_at');
        $direction = $request->get('direction', 'desc');

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'updated_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $query = KnowledgeAsset::query();

        if ($request->filled('search')) {
            $search = trim($request->get('search'));

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('use_when', 'like', "%{$search}%")
                    ->orWhere('avoid_when', 'like', "%{$search}%")
                    ->orWhere('content_snippet', 'like', "%{$search}%")
                    ->orWhere('tags_json', 'like', "%{$search}%")
                    ->orWhere('industries_json', 'like', "%{$search}%")
                    ->orWhere('technologies_json', 'like', "%{$search}%");
            });
        }

        if ($request->filled('asset_type')) {
            $query->where('asset_type', $request->get('asset_type'));
        }

        if ($request->filled('active')) {
            if ($request->get('active') === '1') {
                $query->where('is_active', true);
            }

            if ($request->get('active') === '0') {
                $query->where('is_active', false);
            }
        }

        $perPage = (int) $request->get('per_page', 20);

        if (! in_array($perPage, [20, 50, 100], true)) {
            $perPage = 20;
        }

        $assets = $query
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        $assetTypes = KnowledgeAsset::query()
            ->whereNotNull('asset_type')
            ->distinct()
            ->orderBy('asset_type')
            ->pluck('asset_type');

        $stats = [
            'total' => KnowledgeAsset::query()->count(),
            'active' => KnowledgeAsset::query()->where('is_active', true)->count(),
            'inactive' => KnowledgeAsset::query()->where('is_active', false)->count(),
            'case_studies' => KnowledgeAsset::query()->where('asset_type', 'case_study')->count(),
        ];

        return view('knowledge-assets.index', [
            'assets' => $assets,
            'assetTypes' => $assetTypes,
            'stats' => $stats,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
        ]);
    }

    public function create()
    {
        return view('knowledge-assets.create', [
            'asset' => new KnowledgeAsset([
                'asset_type' => 'service_page',
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateAsset($request);

        KnowledgeAsset::create($this->payloadFromRequest($request, $validated));

        return redirect()
            ->route('knowledge-assets.index')
            ->with('success', 'Knowledge asset created successfully.');
    }

    public function edit(KnowledgeAsset $knowledgeAsset)
    {
        return view('knowledge-assets.edit', [
            'asset' => $knowledgeAsset,
        ]);
    }

    public function update(Request $request, KnowledgeAsset $knowledgeAsset)
    {
        $validated = $this->validateAsset($request, $knowledgeAsset);

        $knowledgeAsset->update($this->payloadFromRequest($request, $validated));

        return redirect()
            ->route('knowledge-assets.index')
            ->with('success', 'Knowledge asset updated successfully.');
    }

    public function toggle(KnowledgeAsset $knowledgeAsset)
    {
        $knowledgeAsset->update([
            'is_active' => ! $knowledgeAsset->is_active,
        ]);

        return back()->with(
            'success',
            $knowledgeAsset->is_active
                ? 'Knowledge asset activated successfully.'
                : 'Knowledge asset deactivated successfully.'
        );
    }

    public function seed(KnowledgeAssetSeederService $service)
    {
        $service->seedDefaults();

        return back()->with('success', 'Default Zestminds knowledge assets seeded successfully.');
    }

    private function validateAsset(Request $request, ?KnowledgeAsset $asset = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'asset_type' => ['required', 'string', 'max:50'],
            'url' => [
                'required',
                'url',
                'max:191',
                Rule::unique('knowledge_assets', 'url')->ignore($asset?->id),
            ],
            'summary' => ['nullable', 'string'],
            'tags' => ['nullable', 'string'],
            'industries' => ['nullable', 'string'],
            'technologies' => ['nullable', 'string'],
            'use_when' => ['nullable', 'string'],
            'avoid_when' => ['nullable', 'string'],
            'content_snippet' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function payloadFromRequest(Request $request, array $validated): array
    {
        return [
            'title' => $validated['title'],
            'asset_type' => $validated['asset_type'],
            'url' => $validated['url'],
            'summary' => $validated['summary'] ?? null,
            'tags_json' => $this->stringToArray($request->input('tags')),
            'industries_json' => $this->stringToArray($request->input('industries')),
            'technologies_json' => $this->stringToArray($request->input('technologies')),
            'use_when' => $validated['use_when'] ?? null,
            'avoid_when' => $validated['avoid_when'] ?? null,
            'content_snippet' => $validated['content_snippet'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function stringToArray(?string $value): array
    {
        if (! $value) {
            return [];
        }

        return collect(preg_split('/[\r\n,]+/', $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}