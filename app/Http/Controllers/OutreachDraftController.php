<?php

namespace App\Http\Controllers;

use App\Models\OutreachDraft;
use App\Services\AI\DraftImprovementService;
use App\Services\AI\DraftQualityService;
use Illuminate\Http\Request;
use Throwable;
use App\Models\ProspectContact;
use Illuminate\Validation\Rule;

class OutreachDraftController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = [
            'subject',
            'status',
            'created_at',
            'updated_at',
        ];

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $query = OutreachDraft::query()
            ->with('prospect');

        if ($request->filled('search')) {
            $search = trim($request->get('search'));

            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhere('linkedin_connection_note', 'like', "%{$search}%")
                    ->orWhere('linkedin_followup', 'like', "%{$search}%")
                    ->orWhere('follow_up_1', 'like', "%{$search}%")
                    ->orWhere('follow_up_2', 'like', "%{$search}%")
                    ->orWhereHas('prospect', function ($prospectQuery) use ($search) {
                        $prospectQuery->where('company_name', 'like', "%{$search}%")
                            ->orWhere('domain', 'like', "%{$search}%")
                            ->orWhere('website_url', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $perPage = (int) $request->get('per_page', 20);

        if (! in_array($perPage, [20, 50, 100], true)) {
            $perPage = 20;
        }

        $drafts = $query
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        $statuses = OutreachDraft::query()
            ->whereNotNull('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        return view('outreach-drafts.index', [
            'drafts' => $drafts,
            'statuses' => $statuses,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
        ]);
    }

    public function show(OutreachDraft $outreachDraft)
    {
        $outreachDraft->load('prospect');

        return view('outreach-drafts.show', [
            'draft' => $outreachDraft,
        ]);
    }

    public function improveWithAi(OutreachDraft $outreachDraft, DraftImprovementService $service)
    {
        try {
            $service->improve($outreachDraft);

            return redirect()
                ->route('outreach-drafts.show', $outreachDraft)
                ->with('success', 'Draft improved with AI successfully.');
        } catch (Throwable $e) {
            return redirect()
                ->route('outreach-drafts.show', $outreachDraft)
                ->with('error', 'AI draft improvement failed: ' . $e->getMessage());
        }
    }

    public function qualityCheckWithAi(OutreachDraft $outreachDraft, DraftQualityService $service)
    {
        try {
            $service->check($outreachDraft);

            return redirect()
                ->route('outreach-drafts.show', $outreachDraft)
                ->with('success', 'AI quality check completed successfully.');
        } catch (Throwable $e) {
            return redirect()
                ->route('outreach-drafts.show', $outreachDraft)
                ->with('error', 'AI quality check failed: ' . $e->getMessage());
        }
    }
	
	
	public function edit(OutreachDraft $outreachDraft)
	{
		$outreachDraft->load([
			'prospect.contacts',
			'contact',
		]);

		return view('outreach-drafts.edit', [
			'draft' => $outreachDraft,
			'contacts' => $outreachDraft->prospect?->contacts ?? collect(),
		]);
	}

	public function update(Request $request, OutreachDraft $outreachDraft)
	{
		$validated = $request->validate([
			'prospect_contact_id' => [
				'nullable',
				'integer',
				Rule::exists('prospect_contacts', 'id')
					->where(fn ($query) => $query->where('prospect_id', $outreachDraft->prospect_id)),
			],
			'subject' => ['required', 'string', 'max:255'],
			'preheader' => ['nullable', 'string', 'max:255'],
			'body' => ['required', 'string'],
			'linkedin_connection_note' => ['nullable', 'string', 'max:500'],
			'linkedin_followup' => ['nullable', 'string'],
			'follow_up_1' => ['nullable', 'string'],
			'follow_up_2' => ['nullable', 'string'],
		]);

		$outreachDraft->update([
			'prospect_contact_id' => $validated['prospect_contact_id'] ?? null,
			'subject' => trim($validated['subject']),
			'preheader' => isset($validated['preheader']) ? trim($validated['preheader']) : null,
			'body' => trim($validated['body']),
			'linkedin_connection_note' => isset($validated['linkedin_connection_note']) ? trim($validated['linkedin_connection_note']) : null,
			'linkedin_followup' => isset($validated['linkedin_followup']) ? trim($validated['linkedin_followup']) : null,
			'follow_up_1' => isset($validated['follow_up_1']) ? trim($validated['follow_up_1']) : null,
			'follow_up_2' => isset($validated['follow_up_2']) ? trim($validated['follow_up_2']) : null,

			'status' => 'manually_edited',

			'ai_quality_score' => null,
			'ai_personalization_score' => null,
			'ai_relevance_score' => null,
			'ai_proof_score' => null,
			'ai_spam_risk_score' => null,
			'ai_quality_notes' => null,
			'ai_improvement_notes' => null,
			'ai_last_checked_at' => null,
		]);

		return redirect()
			->route('outreach-drafts.show', $outreachDraft)
			->with('success', 'Draft updated manually. Please run AI Quality Check again before sending.');
	}
	
	public function createForContact(ProspectContact $prospectContact, DraftImprovementService $draftImprovementService)
	{
		$prospectContact->load('prospect');

		$prospect = $prospectContact->prospect;
		$companyName = $prospect?->company_name ?: $prospect?->domain ?: 'your company';

		$firstName = $this->firstName($prospectContact->name);
		$greeting = $firstName ? "Hi {$firstName}," : 'Hi there,';

		$draft = OutreachDraft::create([
			'prospect_id' => $prospectContact->prospect_id,
			'prospect_contact_id' => $prospectContact->id,
			'subject' => 'Possible technical support for ' . $companyName,
			'preheader' => null,
			'body' => $greeting . "\n\n"
				. "I wanted to create a contact-specific draft for {$companyName}.\n\n"
				. "This is only a starting point. Please improve it before sending.\n\n"
				. "Best,\n"
				. "Shivam\n"
				. "Zestminds Technologies",
			'linkedin_connection_note' => null,
			'linkedin_followup' => null,
			'follow_up_1' => null,
			'follow_up_2' => null,
			'status' => 'contact_draft_created',
		]);

		try {
			$draftImprovementService->improve($draft->fresh(['prospect', 'contact']));

			return redirect()
				->route('outreach-drafts.show', $draft)
				->with('success', 'Contact-specific AI draft generated. Please review and edit before sending.');
		} catch (Throwable $e) {
			report($e);

			return redirect()
				->route('outreach-drafts.show', $draft)
				->with('error', 'Basic draft created, but AI generation failed. You can click Improve with AI manually.');
		}
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