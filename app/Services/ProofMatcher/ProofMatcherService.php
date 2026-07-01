<?php

namespace App\Services\ProofMatcher;

use App\Models\AgentLog;
use App\Models\KnowledgeAsset;
use App\Models\Prospect;
use RuntimeException;

class ProofMatcherService
{
    public function match(Prospect $prospect): array
    {
        $analysis = $prospect->analyses()->latest()->first();
        if (!$analysis) throw new RuntimeException('No analysis found. Please run analyzer first.');

        $assets = KnowledgeAsset::where('is_active', true)->get();
        $matches = $assets->map(function (KnowledgeAsset $asset) use ($analysis) {
            $score = $this->scoreAsset($asset, $analysis);
            return [
                'id' => $asset->id,
                'title' => $asset->title,
                'url' => $asset->url,
                'asset_type' => $asset->asset_type,
                'score' => $score,
                'reasons' => ['Rule-based proof fit score: '.$score],
            ];
        })->sortByDesc('score')->values()->all();

        $selected = array_values(array_filter($matches, fn($m) => $m['score'] > 0));
        $selected = array_slice($selected, 0, 3);

        AgentLog::create([
            'prospect_id' => $prospect->id,
            'step_name' => 'match_proof_links_rule_based',
            'output' => ['selected_proofs' => $selected],
        ]);

        return ['selectedProofs' => $selected, 'allMatches' => $matches];
    }

    private function scoreAsset(KnowledgeAsset $asset, $analysis): int
    {
        $text = strtolower($asset->title.' '.$asset->summary.' '.json_encode($asset->tags_json).' '.json_encode($asset->industries_json).' '.json_encode($asset->technologies_json).' '.$asset->use_when);
        $companyType = strtolower($analysis->company_type ?? '');
        $services = strtolower(json_encode($analysis->primary_services_json ?? []));
        $targets = strtolower(json_encode($analysis->target_clients_json ?? []));
        $score = 0;
        if (str_contains($companyType, 'agency') && str_contains($text, 'agency')) $score += 60;
        if (str_contains($companyType, 'webflow') && str_contains($text, 'agency')) $score += 25;
        if ((str_contains($companyType, 'ui') || str_contains($companyType, 'branding')) && str_contains($text, 'mvp')) $score += 20;
        if (str_contains($companyType, 'shopify') && (str_contains($text, 'e-commerce') || str_contains($text, 'shopify'))) $score += 25;
        if (str_contains($services, 'crm') && (str_contains($text, 'crm') || str_contains($text, 'laravel'))) $score += 30;
        if (str_contains($services, 'ai automation') && str_contains($text, 'automation')) $score += 35;
        if (str_contains($targets, 'saas') && str_contains($text, 'saas')) $score += 15;
        if (str_contains($targets, 'startup') && (str_contains($text, 'startup') || str_contains($text, 'mvp'))) $score += 15;
        if (str_contains($targets, 'healthcare') && str_contains($text, 'healthcare')) $score += 35;
        if ($asset->asset_type === 'case_study') $score += 5;
        return $score;
    }
}
