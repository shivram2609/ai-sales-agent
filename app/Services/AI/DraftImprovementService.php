<?php

namespace App\Services\AI;

use App\Models\AiLog;
use App\Models\KnowledgeAsset;
use App\Models\OutreachDraft;
use Throwable;

class DraftImprovementService
{
    public function __construct(
    private OpenAiClientService $openAiClient,
    private DraftTextSanitizerService $sanitizer
	) {
	}

    public function improve(OutreachDraft $draft): array
    {
        $draft->loadMissing(['prospect', 'contact']);

        $inputSnapshot = $this->buildInputSnapshot($draft);
        $promptVersion = config('services.openai.draft_prompt_version', 'v1');
        $model = config('services.openai.model');

        $log = AiLog::create([
            'loggable_type' => OutreachDraft::class,
            'loggable_id' => $draft->id,
            'action' => 'draft_improvement',
            'model' => $model,
            'prompt_version' => $promptVersion,
            'input_snapshot' => $inputSnapshot,
            'status' => 'running',
        ]);

        try {
            $result = $this->openAiClient->structuredResponse(
                $this->systemPrompt(),
                $this->userPrompt($inputSnapshot),
                $this->schema(),
                'draft_improvement_response',
                3200
            );

            $json = $result['json'];

            $improvementNotes = $this->sanitizer->sanitizeArray($json['improvement_notes'] ?? []);
			$warnings = $this->sanitizer->sanitizeArray($json['warnings'] ?? []);

			$draft->update([
				'subject' => $this->sanitizer->sanitizeString($json['subject'] ?? $draft->subject),
				'preheader' => $this->sanitizer->sanitizeString($json['preheader'] ?? $draft->preheader),
				'body' => $this->sanitizer->sanitizeString($json['email_body'] ?? $draft->body),
				'linkedin_connection_note' => $this->sanitizer->sanitizeString($json['linkedin_connection_note'] ?? $draft->linkedin_connection_note),
				'linkedin_followup' => $this->sanitizer->sanitizeString($json['linkedin_followup'] ?? $draft->linkedin_followup),
				'follow_up_1' => $this->sanitizer->sanitizeString($json['follow_up_1'] ?? $draft->follow_up_1),
				'follow_up_2' => $this->sanitizer->sanitizeString($json['follow_up_2'] ?? $draft->follow_up_2),
				'status' => 'ai_improved',

				'ai_quality_score' => $json['scores']['overall_quality'] ?? null,
				'ai_personalization_score' => $json['scores']['personalization'] ?? null,
				'ai_relevance_score' => $json['scores']['relevance'] ?? null,
				'ai_proof_score' => $json['scores']['proof_usage'] ?? null,
				'ai_spam_risk_score' => $json['scores']['spam_risk'] ?? null,

				'ai_improvement_notes' => $this->arrayToText($improvementNotes),
				'ai_quality_notes' => $this->arrayToText($warnings),
				'ai_model_used' => $result['model'],
				'ai_prompt_version' => $promptVersion,
				'ai_last_checked_at' => now(),
			]);

            $log->update([
                'output_json' => $json,
                'status' => 'completed',
            ]);

            return $json;
        } catch (Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function buildInputSnapshot(OutreachDraft $draft): array
	{
		$prospect = $draft->prospect;

		$knowledgeAssets = KnowledgeAsset::query()
			->where('is_active', true)
			->latest()
			->limit(8)
			->get()
			->map(function (KnowledgeAsset $asset) {
				return [
					'title' => $asset->title,
					'asset_type' => $asset->asset_type,
					'url' => $asset->url,
					'summary' => $asset->summary,
					'tags' => $asset->tags_json ?? [],
					'industries' => $asset->industries_json ?? [],
					'technologies' => $asset->technologies_json ?? [],
					'use_when' => $asset->use_when,
					'avoid_when' => $asset->avoid_when,
					'content_snippet' => $asset->content_snippet,
				];
			})
			->values()
			->all();

		return [
			'sender' => [
				'name' => 'Shivam',
				'company' => 'Zestminds Technologies',
				'voice' => 'senior developer and technical founder',
				'core_offer' => 'development support, AI automation, SaaS/MVP development, integrations, CRM/ERP work, backend workflows, dashboards, portals, and technical delivery support',
			],

			'prospect' => [
				'company_name' => $prospect?->company_name,
				'domain' => $prospect?->domain,
				'website_url' => $prospect?->website_url,
				'category_guess' => $prospect?->category_guess,
				'country_guess' => $prospect?->country_guess,
				'fit_score' => $prospect?->fit_score,
				'status' => $prospect?->status,
				'notes' => $prospect?->notes,
				'analysis_summary' => $prospect?->analysis_summary ?? $prospect?->analysis ?? null,
			],

			'knowledge_assets' => $knowledgeAssets,
			
			'contact' => [
				'name' => $draft->contact?->name,
				'email' => $draft->contact?->email,
				'title' => $draft->contact?->title,
				'linkedin_url' => $draft->contact?->linkedin_url,
				'phone' => $draft->contact?->phone,
				'source' => $draft->contact?->source,
				'status' => $draft->contact?->status,
				'is_primary' => $draft->contact?->is_primary,
				'notes' => $draft->contact?->notes,
			],

			'current_draft' => [
				'subject' => $draft->subject,
				'email_body' => $draft->body,
				'linkedin_connection_note' => $draft->linkedin_connection_note,
				'linkedin_followup' => $draft->linkedin_followup,
				'follow_up_1' => $draft->follow_up_1,
				'follow_up_2' => $draft->follow_up_2,
			],

			'ai_quality_feedback' => [
				'overall_quality_score' => $draft->ai_quality_score,
				'personalization_score' => $draft->ai_personalization_score,
				'relevance_score' => $draft->ai_relevance_score,
				'proof_score' => $draft->ai_proof_score,
				'spam_risk_score' => $draft->ai_spam_risk_score,
				'quality_notes' => $draft->ai_quality_notes,
				'previous_improvement_notes' => $draft->ai_improvement_notes,
				'last_checked_at' => $draft->ai_last_checked_at?->toDateTimeString(),
			],
		];
	}

   private function systemPrompt(): string
	{
		return <<<PROMPT
	Act as a senior B2B outreach strategist and technical CMO for Zestminds Technologies.

	But write in Shivam's voice:
	- senior developer
	- technical founder
	- warm and practical
	- not salesy
	- not over-polished
	- not like an agency pitch

	Your job is to improve the draft so it feels like Shivam personally wrote it after checking the prospect.

	Use this simple email flow:
	1. Start with a specific observation about the prospect.
	2. Mention a practical gap or situation they may face.
	3. Explain how Zestminds can help in simple words.
	4. End with a soft, low-pressure CTA.

	Keep it clear:
	- The reader should understand in 5 seconds why Shivam is reaching out.
	- Use concrete examples like CRM sync, API integration, automation, dashboard, backend workflow, client portal, custom form workflow, or technical handoff.
	- Do not use vague phrases like "bits behind it" or "site sitting as a brochure".
	- Do not fake compliments.
	- Do not invent facts, clients, numbers, results, or case studies.
	- Use proof only if it fits naturally.

	If ai_quality_feedback is present:
	- Treat it as correction instructions.
	- Fix the problems mentioned in the quality notes.
	- If the draft is generic, make it more specific.
	- If it is salesy, make it calmer and more human.
	- If proof is weak, use better proof or avoid proof.
	
	If contact details are provided:
	- Write to that person, not just the company.
	- Use first name only when the name is available.
	- Use their role/title to make the message more relevant, but do not overdo it.
	- Do not mention their email address.
	- Do not pretend you know them personally.
	- If the contact is founder, CEO, CTO, operations, or marketing, adjust the problem angle naturally.

	Style rules:
	- First email: 90 to 140 words.
	- Short paragraphs.
	- Simple language.
	- Friendly but not casual.
	- Confident but not pushy.
	- No hype.
	- No brochure language.
	- No forced meeting request.

	Avoid:
	- I hope this email finds you well
	- I came across your website and was impressed
	- unlock growth
	- streamline your operations
	- transform your business
	- cutting-edge
	- game-changing
	- robust solution
	- tailored solutions
	- end-to-end solutions
	- quick question for you
	- just checking in
	- circle back
	- touch base

	Character rules:
	- Use only normal laptop keyboard characters.
	- Do not use ampersand.
	- Write "and" instead of ampersand.
	- Do not use em dash or en dash.
	- Do not use curly quotes.
	- Do not use fancy bullets or arrows.
	
	Also generate:
	- subject
	- preheader
	- first email body
	- LinkedIn connection note
	- LinkedIn follow-up
	- email follow-up 1
	- email follow-up 2

	Return valid JSON only.
	PROMPT;
	}

    private function userPrompt(array $inputSnapshot): string
    {
        return "Improve this outreach draft using the provided prospect context and Zestminds proof assets. Return only valid JSON matching the schema.\n\nINPUT:\n" .
            json_encode($inputSnapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'subject' => ['type' => 'string'],
                'email_body' => ['type' => 'string'],
                'linkedin_connection_note' => ['type' => 'string'],
                'linkedin_followup' => ['type' => 'string'],
                'follow_up_1' => ['type' => 'string'],
                'follow_up_2' => ['type' => 'string'],
                'improvement_notes' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'warnings' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'scores' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'overall_quality' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                        'personalization' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                        'relevance' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                        'proof_usage' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                        'spam_risk' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                    ],
                    'required' => [
                        'overall_quality',
                        'personalization',
                        'relevance',
                        'proof_usage',
                        'spam_risk',
                    ],
                ],
            ],
            'required' => [
                'subject',
                'email_body',
                'linkedin_connection_note',
                'linkedin_followup',
                'follow_up_1',
                'follow_up_2',
                'improvement_notes',
                'warnings',
                'scores',
            ],
        ];
    }

    private function arrayToText(array $items): ?string
    {
        if (! count($items)) {
            return null;
        }

        return collect($items)
            ->map(fn ($item) => '- ' . $item)
            ->implode("\n");
    }
}