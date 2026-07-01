<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignMember;
use App\Models\OutreachDraft;
use App\Models\ProspectContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CampaignMemberController extends Controller
{
    public function store(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'prospect_contact_id' => [
                'required',
                'integer',
                Rule::exists('prospect_contacts', 'id'),
            ],
        ]);

        $contact = ProspectContact::query()
            ->with('prospect')
            ->findOrFail($validated['prospect_contact_id']);

        $latestDraft = OutreachDraft::query()
            ->where('prospect_id', $contact->prospect_id)
            ->where('prospect_contact_id', $contact->id)
            ->latest()
            ->first();

        CampaignMember::firstOrCreate(
            [
                'campaign_id' => $campaign->id,
                'prospect_contact_id' => $contact->id,
            ],
            [
                'prospect_id' => $contact->prospect_id,
                'outreach_draft_id' => $latestDraft?->id,
                'status' => $latestDraft ? CampaignMember::STATUS_DRAFT_READY : CampaignMember::STATUS_NOT_STARTED,
            ]
        );

        return redirect()
            ->route('campaigns.show', $campaign)
            ->with('success', 'Contact added to campaign.');
    }

    public function update(Request $request, CampaignMember $campaignMember)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'max:80'],
            'first_touch_at' => ['nullable', 'date'],
            'follow_up_1_due_at' => ['nullable', 'date'],
            'follow_up_1_sent_at' => ['nullable', 'date'],
            'follow_up_2_due_at' => ['nullable', 'date'],
            'follow_up_2_sent_at' => ['nullable', 'date'],
            'replied_at' => ['nullable', 'date'],
            'last_interaction_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $campaignMember->update([
            'status' => $validated['status'],
            'first_touch_at' => $validated['first_touch_at'] ?? null,
            'follow_up_1_due_at' => $validated['follow_up_1_due_at'] ?? null,
            'follow_up_1_sent_at' => $validated['follow_up_1_sent_at'] ?? null,
            'follow_up_2_due_at' => $validated['follow_up_2_due_at'] ?? null,
            'follow_up_2_sent_at' => $validated['follow_up_2_sent_at'] ?? null,
            'replied_at' => $validated['replied_at'] ?? null,
            'last_interaction_at' => $validated['last_interaction_at'] ?? null,
            'notes' => $this->clean($validated['notes'] ?? null),
        ]);

        return redirect()
            ->route('campaigns.show', $campaignMember->campaign_id)
            ->with('success', 'Campaign member updated.');
    }

    public function createDraft(CampaignMember $campaignMember, \App\Services\AI\DraftImprovementService $draftImprovementService)
	{
		$campaignMember->load(['campaign', 'prospect', 'contact']);

		if (! $campaignMember->contact) {
			return redirect()
				->route('campaigns.show', $campaignMember->campaign_id)
				->with('error', 'This campaign member does not have a contact.');
		}

		$contact = $campaignMember->contact;
		$prospect = $campaignMember->prospect;

		$companyName = $prospect?->company_name ?: $prospect?->domain ?: 'your company';
		$firstName = $this->firstName($contact->name);
		$greeting = $firstName ? "Hi {$firstName}," : 'Hi there,';

		$draft = OutreachDraft::create([
			'prospect_id' => $campaignMember->prospect_id,
			'prospect_contact_id' => $campaignMember->prospect_contact_id,
			'subject' => 'Possible technical support for ' . $companyName,
			'preheader' => null,
			'body' => $greeting . "\n\n"
				. "I wanted to create a campaign draft for {$companyName}.\n\n"
				. "This is only a starting point. Please improve it before sending.\n\n"
				. "Best,\n"
				. "Shivam\n"
				. "Zestminds Technologies",
			'linkedin_connection_note' => null,
			'linkedin_followup' => null,
			'follow_up_1' => null,
			'follow_up_2' => null,
			'status' => 'campaign_draft_created',
		]);

		$campaignMember->update([
			'outreach_draft_id' => $draft->id,
			'status' => CampaignMember::STATUS_DRAFT_READY,
		]);

		try {
			$draftImprovementService->improve($draft->fresh(['prospect', 'contact']));

			return redirect()
				->route('outreach-drafts.show', $draft)
				->with('success', 'Campaign AI draft generated and linked to campaign member.');
		} catch (\Throwable $e) {
			report($e);

			return redirect()
				->route('outreach-drafts.show', $draft)
				->with('error', 'Basic campaign draft created, but AI generation failed. You can click Improve with AI manually.');
		}
	}
	
	
	public function markSent(CampaignMember $campaignMember)
	{
		if ($this->isClosed($campaignMember)) {
			return back()->with('error', 'This campaign member is already closed.');
		}

		if (! $campaignMember->outreach_draft_id) {
			return back()->with('error', 'Cannot mark sent because no draft is linked.');
		}

		if ($campaignMember->first_touch_at) {
			return back()->with('error', 'First touch is already marked.');
		}

		$campaignMember->update([
			'status' => CampaignMember::STATUS_SENT_MANUALLY,
			'first_touch_at' => now(),
			'follow_up_1_due_at' => $campaignMember->follow_up_1_due_at ?: now()->addDays(3)->startOfDay(),
			'last_interaction_at' => now(),
		]);

		return back()->with('success', 'Marked as sent manually. Follow-up 1 due date added.');
	}

	public function markFollowUp1Sent(CampaignMember $campaignMember)
	{
		if ($this->isClosed($campaignMember)) {
			return back()->with('error', 'This campaign member is already closed.');
		}

		if (! $campaignMember->first_touch_at) {
			return back()->with('error', 'Cannot mark follow-up 1 before first touch.');
		}

		if (! $campaignMember->follow_up_1_due_at) {
			return back()->with('error', 'Follow-up 1 due date is not set.');
		}

		if ($campaignMember->follow_up_1_sent_at) {
			return back()->with('error', 'Follow-up 1 is already marked as sent.');
		}

		$campaignMember->update([
			'status' => CampaignMember::STATUS_FOLLOW_UP_1_SENT,
			'follow_up_1_sent_at' => now(),
			'follow_up_2_due_at' => $campaignMember->follow_up_2_due_at ?: now()->addDays(4)->startOfDay(),
			'last_interaction_at' => now(),
		]);

		return back()->with('success', 'Marked follow-up 1 as sent. Follow-up 2 due date added.');
	}

	public function markFollowUp2Sent(CampaignMember $campaignMember)
	{
		if ($this->isClosed($campaignMember)) {
			return back()->with('error', 'This campaign member is already closed.');
		}

		if (! $campaignMember->follow_up_1_sent_at) {
			return back()->with('error', 'Cannot mark follow-up 2 before follow-up 1 is sent.');
		}

		if (! $campaignMember->follow_up_2_due_at) {
			return back()->with('error', 'Follow-up 2 due date is not set.');
		}

		if ($campaignMember->follow_up_2_sent_at) {
			return back()->with('error', 'Follow-up 2 is already marked as sent.');
		}

		$campaignMember->update([
			'status' => CampaignMember::STATUS_FOLLOW_UP_2_SENT,
			'follow_up_2_sent_at' => now(),
			'last_interaction_at' => now(),
		]);

		return back()->with('success', 'Marked follow-up 2 as sent.');
	}

	public function markReplied(CampaignMember $campaignMember)
	{
		if ($campaignMember->status === CampaignMember::STATUS_REPLIED) {
			return back()->with('error', 'This campaign member is already marked as replied.');
		}

		$campaignMember->update([
			'status' => CampaignMember::STATUS_REPLIED,
			'replied_at' => $campaignMember->replied_at ?: now(),
			'last_interaction_at' => now(),
		]);

		return back()->with('success', 'Marked as replied.');
	}

	public function markNotInterested(CampaignMember $campaignMember)
	{
		if ($campaignMember->status === CampaignMember::STATUS_NOT_INTERESTED) {
			return back()->with('error', 'This campaign member is already marked as not interested.');
		}

		$campaignMember->update([
			'status' => CampaignMember::STATUS_NOT_INTERESTED,
			'last_interaction_at' => now(),
		]);

		return back()->with('success', 'Marked as not interested.');
	}

	public function markBounced(CampaignMember $campaignMember)
	{
		if ($campaignMember->status === CampaignMember::STATUS_BOUNCED) {
			return back()->with('error', 'This campaign member is already marked as bounced.');
		}

		$campaignMember->update([
			'status' => CampaignMember::STATUS_BOUNCED,
			'last_interaction_at' => now(),
		]);

		return back()->with('success', 'Marked as bounced.');
	}

	private function isClosed(CampaignMember $campaignMember): bool
	{
		return in_array($campaignMember->status, [
			CampaignMember::STATUS_REPLIED,
			CampaignMember::STATUS_NOT_INTERESTED,
			CampaignMember::STATUS_BOUNCED,
			CampaignMember::STATUS_PAUSED,
		], true);
	}

    public function destroy(CampaignMember $campaignMember)
    {
        $campaignId = $campaignMember->campaign_id;

        $campaignMember->delete();

        return redirect()
            ->route('campaigns.show', $campaignId)
            ->with('success', 'Contact removed from campaign.');
    }

    private function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function firstName(?string $name): ?string
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        return explode(' ', $name)[0] ?? null;
    }
}