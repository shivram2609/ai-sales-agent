<?php

namespace App\Services\Analyzer;

use App\Models\AgentLog;
use App\Models\Prospect;
use App\Models\ProspectAnalysis;
use RuntimeException;

class ProspectAnalyzerService
{
    public function analyze(Prospect $prospect): ProspectAnalysis
    {
        $page = $prospect->pages()->latest()->first();
        if (!$page) {
            throw new RuntimeException('No crawled page found. Please crawl homepage first.');
        }

        $text = strtolower(($page->title ?? '').' '.($page->meta_description ?? '').' '.json_encode($page->headings_json).' '.($page->main_text ?? ''));
        $services = $this->detectServices($text);
        $targets = $this->detectTargets($text);
        $companyType = $this->detectCompanyType($text, $prospect->category_guess);
        $techDepth = $this->detectTechnicalDepth($text);
        $region = $this->detectRegion($text, $prospect->country_guess);
        $score = $this->score($text, $companyType, $services, $targets, $techDepth, $region);

        $analysis = ProspectAnalysis::create([
            'prospect_id' => $prospect->id,
            'company_type' => $companyType,
            'primary_services_json' => $services,
            'target_clients_json' => $targets,
            'visible_technical_depth' => $techDepth,
            'likely_gap' => $this->likelyGap($companyType, $techDepth),
            'partnership_angle' => $this->partnershipAngle($companyType, $services),
            'region' => $region,
            'confidence' => $score >= 60 ? 75 : 55,
            'rule_score' => $score,
            'ai_score' => $score,
            'final_score' => $score,
            'recommended_action' => $score >= 80 ? 'priority_outreach' : ($score >= 60 ? 'manual_review' : 'skip'),
            'reasoning_summary' => "Rule-based analysis detected {$companyType}, services: ".implode(', ', $services).", region: {$region}, tech depth: {$techDepth}.",
            'raw' => ['source' => 'rule_based_v1'],
        ]);

        $prospect->update(['status' => 'analyzed', 'fit_score' => $score]);
        AgentLog::create([
            'prospect_id' => $prospect->id,
            'step_name' => 'analyze_prospect_rule_based',
            'output' => ['analysis_id' => $analysis->id, 'final_score' => $score, 'recommended_action' => $analysis->recommended_action],
        ]);

        return $analysis;
    }

    private function detectServices(string $text): array
    {
        $map = [
            'Webflow' => ['webflow'],
            'Shopify' => ['shopify'],
            'UI/UX' => ['ui/ux', 'ux design', 'user experience', 'interface design'],
            'Branding' => ['branding', 'brand strategy'],
            'Digital Marketing' => ['seo', 'ppc', 'paid media', 'marketing'],
            'CRM' => ['crm', 'hubspot', 'zoho', 'salesforce'],
            'AI Automation' => ['ai automation', 'workflow automation', 'make.com', 'zapier', 'n8n'],
            'Custom Software' => ['custom software', 'web application', 'saas development', 'api integration'],
        ];
        return $this->detectFromMap($text, $map, ['Agency Services']);
    }

    private function detectTargets(string $text): array
    {
        $map = [
            'SaaS' => ['saas'],
            'Startups' => ['startup', 'founder'],
            'B2B' => ['b2b'],
            'E-commerce' => ['ecommerce', 'e-commerce', 'shopify'],
            'Healthcare' => ['healthcare', 'medical', 'hipaa'],
            'Fintech' => ['fintech', 'finance'],
        ];
        return $this->detectFromMap($text, $map, ['Businesses']);
    }

    private function detectFromMap(string $text, array $map, array $fallback): array
    {
        $found = [];
        foreach ($map as $label => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($text, $needle)) { $found[] = $label; break; }
            }
        }
        return array_values(array_unique($found ?: $fallback));
    }

    private function detectCompanyType(string $text, ?string $guess): string
    {
        $guess = $guess ?: '';
        if (str_contains($text.$guess, 'webflow')) return 'Webflow agency';
        if (str_contains($text.$guess, 'shopify')) return 'Shopify agency';
        if (str_contains($text.$guess, 'ui') || str_contains($text.$guess, 'ux')) return 'UI/UX agency';
        if (str_contains($text.$guess, 'branding')) return 'Branding agency';
        if (str_contains($text.$guess, 'digital marketing')) return 'Digital marketing agency';
        if (str_contains($text.$guess, 'crm') || str_contains($text.$guess, 'erp')) return 'CRM/ERP consultant';
        if (str_contains($text, 'agency') || str_contains($text, 'studio')) return 'Agency / service company';
        return 'Unknown company type';
    }

    private function detectTechnicalDepth(string $text): string
    {
        $high = ['laravel', 'node.js', 'react', 'python', 'fastapi', 'kubernetes', 'aws', 'backend', 'api integration', 'custom software'];
        $hits = 0;
        foreach ($high as $needle) { if (str_contains($text, $needle)) $hits++; }
        return $hits >= 4 ? 'high' : ($hits >= 2 ? 'medium' : 'low');
    }

    private function detectRegion(string $text, ?string $guess): string
    {
        $guess = $guess ?: '';
        $combo = strtolower($text.' '.$guess);
        foreach (['USA' => ['usa','united states','new york','california'], 'UK' => ['uk','london','united kingdom'], 'Canada' => ['canada','toronto'], 'Australia' => ['australia','sydney'], 'Germany' => ['germany','berlin','deutschland'], 'Netherlands' => ['netherlands','amsterdam'], 'UAE' => ['uae','dubai']] as $region => $needles) {
            foreach ($needles as $needle) { if (str_contains($combo, $needle)) return $region; }
        }
        return $guess ?: '';
    }

    private function score(string $text, string $companyType, array $services, array $targets, string $techDepth, string $region): int
    {
        $score = 30;
        if (in_array($region, ['USA','UK','Canada','Australia','Germany','Netherlands','UAE'])) $score += 20;
        if (str_contains(strtolower($companyType), 'agency') || str_contains(strtolower($companyType), 'studio') || str_contains(strtolower($companyType), 'consultant')) $score += 20;
        if (array_intersect($targets, ['SaaS','Startups','B2B','E-commerce'])) $score += 15;
        if ($techDepth === 'low') $score += 20;
        if ($techDepth === 'medium') $score += 10;
        if ($techDepth === 'high') $score -= 10;
        if (str_contains($text, 'portfolio') || str_contains($text, 'case stud')) $score += 10;
        if (str_contains($text, 'india') || str_contains($text, 'bangalore') || str_contains($text, 'mumbai') || str_contains($text, 'delhi')) $score -= 25;
        return max(0, min(100, $score));
    }

    private function likelyGap(string $companyType, string $techDepth): string
    {
        if ($techDepth === 'low') return 'Likely needs reliable technical partner for backend, custom app, integrations, CRM, or AI automation requests beyond design/marketing scope.';
        if ($techDepth === 'medium') return 'May need overflow or specialist execution support for more complex client builds.';
        return 'Strong technical depth visible; only approach if partnership/overflow angle is clear.';
    }

    private function partnershipAngle(string $companyType, array $services): string
    {
        return 'Zestminds can support as a white-label or partner development team for client projects that require custom software, SaaS, backend/API, CRM, Shopify, or AI automation execution.';
    }
}
