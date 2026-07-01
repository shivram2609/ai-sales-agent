<?php

namespace App\Services\Review;

use App\Models\AgentLog;
use App\Models\Prospect;
use App\Models\ReviewAction;
use RuntimeException;

class ReviewQueueService
{
    public function queue()
    {
        return Prospect::query()
            ->where('fit_score', '>=', 60)
            ->whereNotIn('status', ['approved', 'rejected', 'not_fit'])
            ->whereHas('drafts', fn($q) => $q->where('status', 'quality_checked'))
            ->with([
				'analyses' => fn ($q) => $q->latest()->limit(1),

				'drafts' => fn ($q) => $q
					->whereIn('status', [
						'quality_checked',
						'ai_quality_checked',
						'ai_improved',
					])
					->latest()
					->limit(1),
			])
            ->latest('updated_at')
            ->get();
    }

    public function approve(Prospect $prospect, ?string $notes = null): void
    {
        $draft = $prospect->drafts()->latest()->first();
        if (!$draft || $draft->status !== 'quality_checked') throw new RuntimeException('Draft must pass quality check before approval.');
        $prospect->update(['status' => 'approved']);
        $draft->update(['status' => 'approved']);
        ReviewAction::create(['prospect_id' => $prospect->id, 'action' => 'approved', 'notes' => $notes ?: 'Approved for outreach.']);
        AgentLog::create(['prospect_id' => $prospect->id, 'step_name' => 'human_review_approved', 'input' => ['draft_id' => $draft->id], 'output' => ['status' => 'approved', 'notes' => $notes]]);
    }
    public function reject(Prospect $prospect, ?string $notes = null): void
    {
        $draft = $prospect->drafts()->latest()->first();
        $prospect->update(['status' => 'rejected']);
        if ($draft) $draft->update(['status' => 'rejected']);
        ReviewAction::create(['prospect_id' => $prospect->id, 'action' => 'rejected', 'notes' => $notes ?: 'Rejected during human review.']);
        AgentLog::create(['prospect_id' => $prospect->id, 'step_name' => 'human_review_rejected', 'input' => ['draft_id' => $draft?->id], 'output' => ['status' => 'rejected', 'notes' => $notes]]);
    }
    public function markNotFit(Prospect $prospect, ?string $notes = null): void
    {
        $prospect->update(['status' => 'not_fit']);
        ReviewAction::create(['prospect_id' => $prospect->id, 'action' => 'not_fit', 'notes' => $notes ?: 'Marked as not fit.']);
        AgentLog::create(['prospect_id' => $prospect->id, 'step_name' => 'human_review_not_fit', 'output' => ['status' => 'not_fit', 'notes' => $notes]]);
    }
}
