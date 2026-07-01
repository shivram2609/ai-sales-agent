<?php

namespace App\Services\AI;

use App\Models\AiLog;
use App\Models\OutreachDraft;
use Throwable;

class DraftQualityService
{
    public function __construct(
        private OpenAiClientService $openAiClient
    ) {
    }

    public function check(OutreachDraft $draft): array
    {
        $draft->loadMissing(['prospect', 'contact']);

        $inputSnapshot = $this->buildInputSnapshot($draft);
        $promptVersion = config('services.openai.draft_prompt_version', 'v1');
        $model = config('services.openai.model');

        $log = AiLog::create([
            'loggable_type' => OutreachDraft::class,
            'loggable_id' => $draft->id,
            'action' => 'draft_quality_check',
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
                'draft_quality_response',
                1800
            );

            $json = $result['json'];

            $draft->update([
                'status' => 'ai_quality_checked',
                'ai_quality_score' => $json['scores']['overall_quality'] ?? null,
                'ai_personalization_score' => $json['scores']['personalization'] ?? null,
                'ai_relevance_score' => $json['scores']['relevance'] ?? null,
                'ai_proof_score' => $json['scores']['proof_usage'] ?? null,
                'ai_spam_risk_score' => $json['scores']['spam_risk'] ?? null,
                'ai_quality_notes' => $this->qualityText($json),
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

        return [
            'prospect' => [
                'company_name' => $prospect?->company_name,
                'domain' => $prospect?->domain,
                'website_url' => $prospect?->website_url,
                'category_guess' => $prospect?->category_guess,
                'fit_score' => $prospect?->fit_score,
                'notes' => $prospect?->notes,
            ],
            'draft' => [
                'subject' => $draft->subject,
                'email_body' => $draft->body,
                'linkedin_connection_note' => $draft->linkedin_connection_note,
                'linkedin_followup' => $draft->linkedin_followup,
                'follow_up_1' => $draft->follow_up_1,
                'follow_up_2' => $draft->follow_up_2,
            ],
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
        ];
    }

    private function systemPrompt(): string
	{
		return <<<PROMPT
	Act as a strict senior B2B email reviewer and technical CMO for Zestminds Technologies.

	Review the draft as if Shivam will personally send it.

	The draft should:
	- sound human
	- feel friendly
	- be easy to understand
	- mention a specific prospect context or practical problem
	- explain clearly why Shivam is reaching out
	- use a soft CTA
	
	If a contact is assigned:
	- Check whether the draft is written to that person.
	- Check whether the greeting matches the contact name.
	- Check whether the message angle fits their title or role.
	- Score lower if the email still sounds like it is written only to the company.

	Score lower if:
	- it sounds salesy
	- it sounds over-polished
	- it sounds like AI
	- it sounds like a generic agency pitch
	- it is confusing
	- it uses vague phrases
	- it has weak personalization
	- it uses fake compliments
	- it forces proof
	- it is too long
	- it asks too hard for a meeting
	- it uses fancy characters
	- it uses ampersand

	Score higher if:
	- the reason for outreach is clear in 5 seconds
	- it sounds like a real person wrote it
	- it uses simple words
	- it references a practical gap or situation
	- it gives concrete examples
	- it has a low-pressure CTA

	Character check:
	- Flag ampersand.
	- Flag em dash or en dash.
	- Flag curly quotes.
	- Flag fancy bullets or arrows.

	Return valid JSON only.
	PROMPT;
	}

    private function userPrompt(array $inputSnapshot): string
    {
        return "Review this outreach draft and return only valid JSON matching the schema.\n\nINPUT:\n" .
            json_encode($inputSnapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'verdict' => [
                    'type' => 'string',
                    'enum' => ['sendable', 'needs_minor_edit', 'needs_major_rewrite', 'do_not_send'],
                ],
                'summary' => ['type' => 'string'],
                'strengths' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'issues' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'recommended_edits' => [
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
                'verdict',
                'summary',
                'strengths',
                'issues',
                'recommended_edits',
                'scores',
            ],
        ];
    }

    private function qualityText(array $json): string
    {
        $lines = [];

        $lines[] = 'Verdict: ' . ($json['verdict'] ?? 'unknown');
        $lines[] = '';
        $lines[] = 'Summary: ' . ($json['summary'] ?? 'No summary.');
        $lines[] = '';

        if (! empty($json['strengths'])) {
            $lines[] = 'Strengths:';
            foreach ($json['strengths'] as $item) {
                $lines[] = '- ' . $item;
            }
            $lines[] = '';
        }

        if (! empty($json['issues'])) {
            $lines[] = 'Issues:';
            foreach ($json['issues'] as $item) {
                $lines[] = '- ' . $item;
            }
            $lines[] = '';
        }

        if (! empty($json['recommended_edits'])) {
            $lines[] = 'Recommended edits:';
            foreach ($json['recommended_edits'] as $item) {
                $lines[] = '- ' . $item;
            }
        }

        return trim(implode("\n", $lines));
    }
}