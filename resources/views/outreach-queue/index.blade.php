@extends('layouts.app')

@section('content')
@php
    $closedLabels = [
        'replied' => 'Replied',
        'not_interested' => 'Not Interested',
        'bounced' => 'Bounced',
        'paused' => 'Paused',
    ];

    $statusBadgeMap = [
        'not_started' => 'bg-secondary',
        'draft_ready' => 'bg-info text-dark',
        'approved' => 'bg-primary',
        'sent_manually' => 'bg-success',
        'follow_up_1_due' => 'bg-warning text-dark',
        'follow_up_1_sent' => 'bg-success',
        'follow_up_2_due' => 'bg-warning text-dark',
        'follow_up_2_sent' => 'bg-success',
        'replied' => 'bg-success',
        'not_interested' => 'bg-dark',
        'bounced' => 'bg-danger',
        'paused' => 'bg-secondary',
    ];
@endphp

<div class="container-fluid py-4">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Outreach Queue</h1>
            <div class="text-muted">
                Daily execution view for first touches, follow-ups, missing drafts, and replies.
            </div>
        </div>

        <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary">
            Open Campaigns
        </a>
    </div>
	@if(session('success'))
		<div class="alert alert-success">
			{{ session('success') }}
		</div>
	@endif

	@if(session('error'))
		<div class="alert alert-danger">
			{{ session('error') }}
		</div>
	@endif
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">Follow-ups Due</div>
                    <div class="h3 fw-bold mb-0">{{ $followUpsDue->count() }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">Ready First Touch</div>
                    <div class="h3 fw-bold mb-0">{{ $readyForFirstTouch->count() }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">Missing Drafts</div>
                    <div class="h3 fw-bold mb-0">{{ $missingDrafts->count() }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">Recent Replies</div>
                    <div class="h3 fw-bold mb-0">{{ $recentReplies->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Follow-ups Due --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h5 fw-bold mb-0">Follow-ups Due</h2>
                <div class="small text-muted">Contacts where FU1 or FU2 is due today or overdue.</div>
            </div>
        </div>

        <div class="card-body">
            @if($followUpsDue->count())
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Contact</th>
                                <th>Company</th>
                                <th>Campaign</th>
                                <th>Due</th>
                                <th>Status</th>
                                <th>Draft</th>
                                <th>Notes</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($followUpsDue as $member)
                                @php
                                    $dueType = null;
                                    $dueDate = null;

                                    if ($member->follow_up_1_due_at && ! $member->follow_up_1_sent_at) {
                                        $dueType = 'FU1';
                                        $dueDate = $member->follow_up_1_due_at;
                                    } elseif ($member->follow_up_2_due_at && ! $member->follow_up_2_sent_at) {
                                        $dueType = 'FU2';
                                        $dueDate = $member->follow_up_2_due_at;
                                    }

                                    $isOverdue = $dueDate && $dueDate->startOfDay()->lt($today);
                                @endphp

                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $member->contact?->name ?: 'No Contact' }}</div>

                                        @if($member->contact?->title)
                                            <div class="small text-muted">{{ $member->contact->title }}</div>
                                        @endif

                                        @if($member->contact?->email)
                                            <div class="small">
                                                <a href="mailto:{{ $member->contact->email }}">
                                                    {{ $member->contact->email }}
                                                </a>
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        @if($member->prospect)
                                            <a href="{{ route('prospects.show', $member->prospect) }}" class="fw-semibold">
                                                {{ $member->prospect->company_name ?? $member->prospect->domain }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if($member->campaign)
                                            <a href="{{ route('campaigns.show', $member->campaign) }}">
                                                {{ $member->campaign->name }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if($dueDate)
                                            <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-warning text-dark' }}">
                                                {{ $dueType }}: {{ $dueDate->format('M d, Y') }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge {{ $statusBadgeMap[$member->status] ?? 'bg-secondary' }}">
                                            {{ str_replace('_', ' ', ucfirst($member->status)) }}
                                        </span>
                                    </td>

                                    <td>
                                        @if($member->draft)
                                            <a href="{{ route('outreach-drafts.show', $member->draft) }}" class="btn btn-sm btn-outline-primary">
                                                Open Draft
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td style="min-width: 220px;">
                                        <div class="small text-muted">
                                            {{ $member->notes ?: '-' }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-light border mb-0">
                    No follow-ups due right now.
                </div>
            @endif
        </div>
    </div>

    {{-- Ready for First Touch --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h2 class="h5 fw-bold mb-0">Ready for First Touch</h2>
            <div class="small text-muted">Draft exists and contact has not been marked as sent yet.</div>
        </div>

        <div class="card-body">
            @if($readyForFirstTouch->count())
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Contact</th>
                                <th>Company</th>
                                <th>Campaign</th>
                                <th>Status</th>
                                <th>Draft</th>
                                <th>Notes</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($readyForFirstTouch as $member)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $member->contact?->name ?: 'No Contact' }}</div>

                                        @if($member->contact?->email)
                                            <div class="small">
                                                <a href="mailto:{{ $member->contact->email }}">
                                                    {{ $member->contact->email }}
                                                </a>
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        @if($member->prospect)
                                            <a href="{{ route('prospects.show', $member->prospect) }}" class="fw-semibold">
                                                {{ $member->prospect->company_name ?? $member->prospect->domain }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if($member->campaign)
                                            <a href="{{ route('campaigns.show', $member->campaign) }}">
                                                {{ $member->campaign->name }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge {{ $statusBadgeMap[$member->status] ?? 'bg-secondary' }}">
                                            {{ str_replace('_', ' ', ucfirst($member->status)) }}
                                        </span>
                                    </td>

                                    <td>
                                        @if($member->draft)
                                            <a href="{{ route('outreach-drafts.show', $member->draft) }}" class="btn btn-sm btn-outline-primary">
                                                Open Draft
                                            </a>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="small text-muted">
                                            {{ $member->notes ?: '-' }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-light border mb-0">
                    No first-touch items ready right now.
                </div>
            @endif
        </div>
    </div>

    {{-- Missing Drafts --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h2 class="h5 fw-bold mb-0">Missing Drafts</h2>
            <div class="small text-muted">Campaign members added but no draft linked yet.</div>
        </div>

        <div class="card-body">
            @if($missingDrafts->count())
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Contact</th>
                                <th>Company</th>
                                <th>Campaign</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($missingDrafts as $member)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $member->contact?->name ?: 'No Contact' }}</div>

                                        @if($member->contact?->email)
                                            <div class="small">
                                                <a href="mailto:{{ $member->contact->email }}">
                                                    {{ $member->contact->email }}
                                                </a>
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        @if($member->prospect)
                                            <a href="{{ route('prospects.show', $member->prospect) }}" class="fw-semibold">
                                                {{ $member->prospect->company_name ?? $member->prospect->domain }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if($member->campaign)
                                            <a href="{{ route('campaigns.show', $member->campaign) }}">
                                                {{ $member->campaign->name }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge {{ $statusBadgeMap[$member->status] ?? 'bg-secondary' }}">
                                            {{ str_replace('_', ' ', ucfirst($member->status)) }}
                                        </span>
                                    </td>

                                    <td>
                                        <form method="post" action="{{ route('campaign-members.draft.store', $member) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success">
                                                Create Draft
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-light border mb-0">
                    No missing drafts.
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Replies --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h2 class="h5 fw-bold mb-0">Recent Replies</h2>
            <div class="small text-muted">Campaign members marked as replied.</div>
        </div>

        <div class="card-body">
            @if($recentReplies->count())
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Contact</th>
                                <th>Company</th>
                                <th>Campaign</th>
                                <th>Replied</th>
                                <th>Notes</th>
								<th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($recentReplies as $member)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $member->contact?->name ?: 'No Contact' }}</div>

                                        @if($member->contact?->email)
                                            <div class="small">
                                                <a href="mailto:{{ $member->contact->email }}">
                                                    {{ $member->contact->email }}
                                                </a>
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        @if($member->prospect)
                                            <a href="{{ route('prospects.show', $member->prospect) }}" class="fw-semibold">
                                                {{ $member->prospect->company_name ?? $member->prospect->domain }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if($member->campaign)
                                            <a href="{{ route('campaigns.show', $member->campaign) }}">
                                                {{ $member->campaign->name }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        {{ $member->replied_at?->format('M d, Y') ?: '-' }}
                                    </td>

                                    <td>
                                        <div class="small text-muted">
                                            {{ $member->notes ?: '-' }}
                                        </div>
                                    </td>
									<td style="min-width: 260px;">
										@include('campaign-members.partials.quick-actions', ['member' => $member])
									</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-light border mb-0">
                    No replies marked yet.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection