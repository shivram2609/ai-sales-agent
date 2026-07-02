<?php

namespace App\Services\Drafts;

use App\Models\AgentLog;
use App\Models\DraftQualityCheck;
use App\Models\OutreachDraft;

class DraftQualityService
{
    public function check(OutreachDraft $draft): DraftQualityCheck
    {
        $body = $draft->body ?? '';
        $subject = $draft->subject ?? '';
        $wordCount = str_word_count(strip_tags($body));
        preg_match_all('/https?:\/\/[^\s)]+/i', $body, $links);
        $linkCount = count($links[0] ?? []);
        $hasOptOut = $this->hasOptOut($body);
        $spammy = $this->spammyPhrases($body.' '.$subject);
        $fake = $this->fakePersonalization($body);
        $misleading = $this->misleadingClaims($body);

        $tooLong = $wordCount > 180;
        $tooManyLinks = $linkCount > 2;
        $tooSalesy = count($spammy) >= 2;
        $fixes = [];
        if ($tooLong) $fixes[] = "Email is too long. Current word count: {$wordCount}. Keep first outreach under 120–140 words.";
        if ($tooManyLinks) $fixes[] = "Too many links found: {$linkCount}. Keep first outreach to 1–2 links.";
        if (!$hasOptOut) $fixes[] = 'Missing opt-out line.';
        if ($tooSalesy) $fixes[] = 'Salesy/spammy wording detected: '.implode(', ', $spammy);
        if ($fake) $fixes[] = 'Possible fake personalization detected: '.implode(', ', $fake);
        if ($misleading) $fixes[] = 'Potential unsupported/misleading claim detected: '.implode(', ', $misleading);

        $risk = ($fake || $misleading) ? 'high' : ((!$hasOptOut || $tooManyLinks || $tooLong || $tooSalesy) ? 'medium' : 'low');

        // Same verdict vocabulary as Services\AI\DraftQualityService so both
        // checkers write to a field that means the same thing regardless of
        // which one ran.
        $verdict = match (true) {
            $fake || $misleading => 'do_not_send',
            $risk === 'medium' && count($fixes) >= 2 => 'needs_major_rewrite',
            $risk === 'medium' => 'needs_minor_edit',
            default => 'sendable',
        };

        $check = DraftQualityCheck::create([
            'outreach_draft_id' => $draft->id,
            'has_fake_personalization' => (bool) $fake,
            'has_misleading_claim' => (bool) $misleading,
            'too_salesy' => $tooSalesy,
            'too_long' => $tooLong,
            'missing_opt_out' => !$hasOptOut,
            'too_many_links' => $tooManyLinks,
            'risk_level' => $risk,
            'fixes_required' => $fixes,
        ]);

        $draft->update([
            'status' => $risk === 'high' ? 'needs_revision' : 'quality_checked',
            'ai_verdict' => $verdict,
        ]);
        AgentLog::create(['prospect_id' => $draft->prospect_id, 'step_name' => 'draft_quality_guardrail_v1', 'input' => ['draft_id' => $draft->id, 'word_count' => $wordCount, 'link_count' => $linkCount], 'output' => ['risk_level' => $risk, 'fixes_required' => $fixes]]);
        return $check;
    }

    private function hasOptOut(string $text): bool
    {
        $lower = strtolower($text);
        return str_contains($lower, 'not interested') || str_contains($lower, 'not relevant') || str_contains($lower, 'i will not follow up') || str_contains($lower, 'unsubscribe') || str_contains($lower, 'opt out');
    }
    private function spammyPhrases(string $text): array
    {
        $phrases = ['guaranteed results','100% guaranteed','limited time offer','act now','free trial','risk free','double your revenue','increase your sales overnight','best company','leading company','world class','revolutionary','game changing'];
        $lower = strtolower($text); return array_values(array_filter($phrases, fn($p) => str_contains($lower, $p)));
    }
    private function fakePersonalization(string $text): array
    {
        $phrases = ['i loved your work','i am impressed by your work','your amazing work','big fan of your company','i have been following your company'];
        $lower = strtolower($text); return array_values(array_filter($phrases, fn($p) => str_contains($lower, $p)));
    }
    private function misleadingClaims(string $text): array
    {
        $phrases = ['we have worked with your competitor','we know your clients','we reviewed your internal process','we can guarantee','we will definitely'];
        $lower = strtolower($text); return array_values(array_filter($phrases, fn($p) => str_contains($lower, $p)));
    }
}
