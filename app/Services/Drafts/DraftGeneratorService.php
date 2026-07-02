<?php

namespace App\Services\Drafts;

use App\Models\AgentLog;
use App\Models\OutreachDraft;
use App\Models\Prospect;
use App\Models\ProspectContact;
use App\Services\AI\DraftTextSanitizerService;
use App\Services\ProofMatcher\ProofMatcherService;
use RuntimeException;

class DraftGeneratorService
{
    public function __construct(
        private ProofMatcherService $proofMatcherService,
        private DraftTextSanitizerService $sanitizer
    ) {}

    public function generate(Prospect $prospect, ?ProspectContact $contact = null): OutreachDraft
    {
        $analysis = $prospect->analyses()->latest()->first();
        if (!$analysis) throw new RuntimeException('No analysis found. Please run analyzer first.');

        $proofMatch = $this->proofMatcherService->match($prospect);
        $proofs = $proofMatch['selectedProofs'] ?? [];
        if (!$proofs) throw new RuntimeException('No proof links matched. Please seed knowledge assets or check analysis.');

        $companyName = $prospect->company_name ?: $prospect->domain ?: 'there';
        $greeting = $contact?->name ? explode(' ', trim($contact->name))[0] : (str_contains($companyName, '.') ? 'there' : $companyName);
        $companyType = strtolower($analysis->company_type ?: 'agency');
        $label = $companyType;
        $primaryProof = $proofs[0];
        $secondProof = $proofs[1] ?? null;
        $subject = $this->subject($companyType);
        $supportLine = $this->supportLine($companyType);

        $bodyLines = [
            "Hi {$greeting},", '',
            "I noticed your team appears to work in the {$label} space.", '',
            $supportLine, '',
            'Zestminds supports agencies as a technical development partner for custom web apps, SaaS builds, backend/API work, CRM systems, Shopify/e-commerce, and AI workflow automation.', '',
            'This page explains the partner model: '.$primaryProof['url'],
            $secondProof ? 'Relevant proof: '.$secondProof['url'] : null, '',
            'Would it make sense to connect and see if we can support you on overflow or specialist development work?', '',
            'Best,', 'Shivam', 'Zestminds Technologies', '',
            'If this is not relevant, just reply “not interested” and I will not follow up.',
        ];

        $draft = OutreachDraft::create([
            'prospect_id' => $prospect->id,
            'prospect_contact_id' => $contact?->id,
            'channel' => 'email_linkedin',
            'subject' => $this->sanitizer->sanitizeString($subject),
            'body' => $this->sanitizer->sanitizeString(implode("\n", array_values(array_filter($bodyLines, fn($line) => $line !== null)))),
            'linkedin_connection_note' => $this->sanitizer->sanitizeString("Hi, I noticed your team works around {$label}. I run Zestminds, where we support agencies with development, APIs, SaaS, CRM, Shopify, and AI automation work. Thought it may be useful to connect."),
            'linkedin_followup' => $this->sanitizer->sanitizeString('Thanks for connecting. Quick context: we help agencies as a technical execution partner when they need reliable dev support for client projects, especially backend, SaaS, integrations, CRM, and AI automation. Happy to share a few examples if useful.'),
            'follow_up_1' => $this->sanitizer->sanitizeString("Hi {$greeting},\n\nJust following up on my earlier note.\n\nThe reason I reached out is simple: many {$label} teams get client requests that go beyond design, marketing, or platform setup - things like backend systems, APIs, dashboards, CRM integrations, or AI automation.\n\nThat is where Zestminds can quietly support as an execution partner.\n\nWorth a quick conversation?\n\nBest,\nShivam"),
            'follow_up_2' => $this->sanitizer->sanitizeString("Hi {$greeting},\n\nLast note from my side.\n\nIf you ever need extra technical hands for client projects - SaaS, custom web apps, backend/API work, Shopify, CRM, or AI automation - Zestminds can support either as a white-label or partner team.\n\nNo pressure at all. Should I keep you in mind for future collaboration, or is this not relevant right now?\n\nBest,\nShivam"),
            'proof_links_used' => $proofs,
            'personalization_notes' => $this->sanitizer->sanitizeArray(["Detected company type: {$analysis->company_type}", 'Used primary proof: '.$primaryProof['title']]),
            'risk_flags' => $this->sanitizer->sanitizeArray(['Rule-based draft only. Human review required before sending.', 'Verify company fit before outreach.']),
            'status' => 'drafted',
        ]);

        $prospect->update(['status' => 'draft_created']);
        AgentLog::create(['prospect_id' => $prospect->id, 'step_name' => 'generate_outreach_draft_rule_based', 'output' => ['draft_id' => $draft->id, 'subject' => $draft->subject]]);
        return $draft;
    }

    private function subject(string $companyType): string
    {
        if (str_contains($companyType, 'webflow')) return 'Possible dev support for Webflow client projects';
        if (str_contains($companyType, 'shopify')) return 'Possible Shopify and custom dev support';
        if (str_contains($companyType, 'ui') || str_contains($companyType, 'branding')) return 'Development support for designed products';
        if (str_contains($companyType, 'digital marketing')) return 'Technical support for client automation work';
        return 'Possible development support for client projects';
    }

    private function supportLine(string $companyType): string
    {
        if (str_contains($companyType, 'webflow')) return 'Many Webflow-focused teams get client requests around backend systems, API integrations, SaaS features, dashboards, or automation, but do not always want to hire a full in-house engineering team for every project.';
        if (str_contains($companyType, 'shopify')) return 'Many Shopify/e-commerce teams eventually need custom app work, migrations, third-party integrations, backend workflows, or automation support beyond standard storefront work.';
        if (str_contains($companyType, 'ui') || str_contains($companyType, 'branding')) return 'Many design-led teams create strong product concepts and interfaces, but need a reliable engineering partner to build the actual SaaS, portal, dashboard, or custom web application behind them.';
        if (str_contains($companyType, 'digital marketing')) return 'Many marketing teams get requests around CRM integrations, reporting dashboards, landing page systems, automation workflows, or AI-enabled internal tools.';
        return 'Many agency teams get client requests around custom software, backend systems, integrations, SaaS products, CRM, or AI automation, but do not always want to expand the internal team for every project.';
    }
}
