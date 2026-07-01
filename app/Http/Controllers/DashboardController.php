<?php

namespace App\Http\Controllers;

use App\Models\DiscoveryCampaign;
use App\Models\DiscoveryResult;
use App\Models\KnowledgeAsset;
use App\Models\OutreachDraft;
use App\Models\Prospect;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'prospects_total' => Prospect::query()->count(),
            'prospects_approved' => Prospect::query()->where('status', 'approved')->count(),
            'prospects_ready_for_review' => Prospect::query()
                ->whereIn('status', ['quality_checked', 'draft_created', 'proof_matched'])
                ->count(),

            'discovery_campaigns_total' => DiscoveryCampaign::query()->count(),
            'discovery_campaigns_running' => DiscoveryCampaign::query()
                ->whereIn('status', ['queued', 'running'])
                ->count(),

            'discovery_results_total' => DiscoveryResult::query()->count(),
            'discovery_results_strong' => DiscoveryResult::query()
                ->where('filter_status', 'strong_candidate')
                ->count(),
            'discovery_results_possible' => DiscoveryResult::query()
                ->where('filter_status', 'possible_candidate')
                ->count(),
            'discovery_results_converted' => DiscoveryResult::query()
                ->where('converted_to_prospect', true)
                ->count(),
            'discovery_results_manual_rejected' => DiscoveryResult::query()
                ->where('filter_status', 'manual_rejected')
                ->count(),

            'drafts_total' => OutreachDraft::query()->count(),
            'drafts_quality_checked' => OutreachDraft::query()
                ->where('status', 'quality_checked')
                ->count(),

            'knowledge_assets_total' => KnowledgeAsset::query()->count(),
        ];

        $recentProspects = Prospect::query()
            ->latest()
            ->limit(8)
            ->get();

        $recentCampaigns = DiscoveryCampaign::query()
            ->latest()
            ->limit(6)
            ->get();

        $recentDrafts = OutreachDraft::query()
            ->with('prospect')
            ->latest()
            ->limit(6)
            ->get();

        $strongDiscoveryResults = DiscoveryResult::query()
            ->whereIn('filter_status', ['strong_candidate', 'possible_candidate'])
            ->where('converted_to_prospect', false)
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard.index', [
            'stats' => $stats,
            'recentProspects' => $recentProspects,
            'recentCampaigns' => $recentCampaigns,
            'recentDrafts' => $recentDrafts,
            'strongDiscoveryResults' => $strongDiscoveryResults,
        ]);
    }
}