<?php

namespace App\Services\Knowledge;

use App\Models\KnowledgeAsset;

class KnowledgeAssetSeederService
{
    public function seedDefaults(): array
    {
        $assets = $this->defaultAssets();
        foreach ($assets as $asset) {
            KnowledgeAsset::updateOrCreate(['url' => $asset['url']], $asset);
        }
        return ['count' => count($assets), 'assets' => KnowledgeAsset::where('is_active', true)->get()];
    }

    private function defaultAssets(): array
    {
        return [
            ['title' => 'Agency Development Partner', 'asset_type' => 'service_page', 'url' => 'https://www.zestminds.com/agency-development-partner', 'summary' => 'Zestminds as a reliable development partner for agencies.', 'tags_json' => ['agency_partner','white_label','development_team'], 'industries_json' => ['Agency','SaaS','B2B'], 'technologies_json' => ['Laravel','React','Node','AI Automation'], 'use_when' => 'Use for agency partner outreach.', 'avoid_when' => 'Avoid for direct end-client compliance or legal-audit positioning.', 'content_snippet' => 'Agency development partner for design, marketing, Webflow, Shopify, and product teams.', 'is_active' => true],
            ['title' => 'AI Workflow Automation Services', 'asset_type' => 'service_page', 'url' => 'https://www.zestminds.com/ai-workflow-automation-services', 'summary' => 'AI workflow automation, CRM automation and internal tools.', 'tags_json' => ['ai_automation','workflow','crm'], 'industries_json' => ['SaaS','B2B','Operations'], 'technologies_json' => ['OpenAI','Make','Zapier','n8n'], 'use_when' => 'Use when prospect offers marketing ops, CRM, automation, SaaS, or AI services.', 'avoid_when' => null, 'content_snippet' => 'AI workflow automation services for business processes.', 'is_active' => true],
            ['title' => 'AI Workflow Automation Guide', 'asset_type' => 'guide', 'url' => 'https://www.zestminds.com/guides/ai-workflow-automation', 'summary' => 'Educational guide explaining AI workflow automation.', 'tags_json' => ['guide','ai_automation'], 'industries_json' => ['B2B','SaaS'], 'technologies_json' => ['OpenAI','Automation'], 'use_when' => 'Use as a softer proof link for awareness.', 'avoid_when' => null, 'content_snippet' => 'Guide to AI workflow automation.', 'is_active' => true],
            ['title' => 'AI Workflow Automation Case Study - Make, FastAPI and Monday CRM', 'asset_type' => 'case_study', 'url' => 'https://www.zestminds.com/ai-workflow-automation-make-fastapi-monday-crm', 'summary' => 'Case study for automation using Make, FastAPI, and Monday CRM.', 'tags_json' => ['case_study','ai_automation','crm'], 'industries_json' => ['Operations','B2B'], 'technologies_json' => ['Make','FastAPI','Monday CRM'], 'use_when' => 'Use when prospect mentions automation, CRM, operations, or API workflows.', 'avoid_when' => null, 'content_snippet' => 'Automation case study connecting Make, FastAPI, and Monday CRM.', 'is_active' => true],
            ['title' => 'Laravel CRM Case Study', 'asset_type' => 'case_study', 'url' => 'https://www.zestminds.com/laravel-crm-case-study', 'summary' => 'Laravel CRM proof for custom CRM builds.', 'tags_json' => ['case_study','crm','laravel'], 'industries_json' => ['B2B','Sales'], 'technologies_json' => ['Laravel','MySQL'], 'use_when' => 'Use for CRM, backend, custom app, or Laravel fit.', 'avoid_when' => null, 'content_snippet' => 'Laravel CRM case study.', 'is_active' => true],
            ['title' => 'MVP Development Services', 'asset_type' => 'service_page', 'url' => 'https://www.zestminds.com/mvp-development', 'summary' => 'MVP and SaaS development services.', 'tags_json' => ['mvp','saas','startup'], 'industries_json' => ['SaaS','Startups'], 'technologies_json' => ['Laravel','React','Node'], 'use_when' => 'Use when target audience includes SaaS/startups/founders.', 'avoid_when' => null, 'content_snippet' => 'MVP development for SaaS and startups.', 'is_active' => true],
            ['title' => 'Dedicated Development Teams', 'asset_type' => 'service_page', 'url' => 'https://www.zestminds.com/dedicated-development-teams', 'summary' => 'Dedicated team model for client projects.', 'tags_json' => ['dedicated_team','staff_augmentation','agency_partner'], 'industries_json' => ['Agency','B2B'], 'technologies_json' => ['Full Stack'], 'use_when' => 'Use when agency may need overflow or long-term delivery support.', 'avoid_when' => null, 'content_snippet' => 'Dedicated development teams.', 'is_active' => true],
            ['title' => 'HIPAA-Compliant AI Hospital System Case Study', 'asset_type' => 'case_study', 'url' => 'https://www.zestminds.com/case-study-hipaa-compliant-ai-hospital-system', 'summary' => 'Healthcare AI case study.', 'tags_json' => ['case_study','healthcare','ai'], 'industries_json' => ['Healthcare'], 'technologies_json' => ['AI','Healthcare'], 'use_when' => 'Use for healthcare prospects.', 'avoid_when' => null, 'content_snippet' => 'HIPAA-compliant AI hospital system case study.', 'is_active' => true],
            ['title' => '1337 Institute LMS Case Study', 'asset_type' => 'case_study', 'url' => 'https://www.zestminds.com/1337institute-of-technologies-case-study', 'summary' => 'LMS case study.', 'tags_json' => ['case_study','lms','education'], 'industries_json' => ['Education'], 'technologies_json' => ['LMS','Web App'], 'use_when' => 'Use for education/LMS prospects.', 'avoid_when' => null, 'content_snippet' => 'LMS case study.', 'is_active' => true],
        ];
    }
}
