<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignMember;
use App\Models\ProspectContact;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');

        $campaigns = Campaign::query()
            ->withCount('members')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('campaigns.index', [
            'campaigns' => $campaigns,
            'statuses' => Campaign::statuses(),
            'selectedStatus' => $status,
        ]);
    }

    public function create()
    {
        return view('campaigns.create', [
            'campaign' => new Campaign(),
            'statuses' => Campaign::statuses(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'audience_label' => ['nullable', 'string', 'max:190'],
            'region_label' => ['nullable', 'string', 'max:190'],
            'status' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $campaign = Campaign::create([
            'name' => trim($validated['name']),
            'audience_label' => $this->clean($validated['audience_label'] ?? null),
            'region_label' => $this->clean($validated['region_label'] ?? null),
            'status' => $validated['status'],
            'description' => $this->clean($validated['description'] ?? null),
            'notes' => $this->clean($validated['notes'] ?? null),
            'started_at' => $validated['status'] === Campaign::STATUS_ACTIVE ? now() : null,
        ]);

        return redirect()
            ->route('campaigns.show', $campaign)
            ->with('success', 'Campaign created.');
    }

    public function show(Campaign $campaign)
    {
        $campaign->load([
            'members' => fn ($query) => $query
                ->with(['prospect', 'contact', 'draft'])
                ->latest(),
        ]);

        $contacts = ProspectContact::query()
            ->with('prospect')
            ->whereIn('status', ['active', 'needs_research'])
            ->orderBy('name')
            ->limit(500)
            ->get();

        return view('campaigns.show', [
            'campaign' => $campaign,
            'contacts' => $contacts,
            'campaignStatuses' => Campaign::statuses(),
            'memberStatuses' => CampaignMember::statuses(),
        ]);
    }

    public function edit(Campaign $campaign)
    {
        return view('campaigns.edit', [
            'campaign' => $campaign,
            'statuses' => Campaign::statuses(),
        ]);
    }

    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'audience_label' => ['nullable', 'string', 'max:190'],
            'region_label' => ['nullable', 'string', 'max:190'],
            'status' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $status = $validated['status'];

        $campaign->update([
            'name' => trim($validated['name']),
            'audience_label' => $this->clean($validated['audience_label'] ?? null),
            'region_label' => $this->clean($validated['region_label'] ?? null),
            'status' => $status,
            'description' => $this->clean($validated['description'] ?? null),
            'notes' => $this->clean($validated['notes'] ?? null),
            'started_at' => $status === Campaign::STATUS_ACTIVE && ! $campaign->started_at
                ? now()
                : $campaign->started_at,
            'completed_at' => $status === Campaign::STATUS_COMPLETED && ! $campaign->completed_at
                ? now()
                : ($status !== Campaign::STATUS_COMPLETED ? null : $campaign->completed_at),
        ]);

        return redirect()
            ->route('campaigns.show', $campaign)
            ->with('success', 'Campaign updated.');
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return redirect()
            ->route('campaigns.index')
            ->with('success', 'Campaign deleted.');
    }

    private function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}