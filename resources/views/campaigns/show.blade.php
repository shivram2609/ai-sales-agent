@extends('layouts.app')

@section('content')
@php
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
            <h1 class="h3 fw-bold mb-1">{{ $campaign->name }}</h1>

            <div class="text-muted">
                {{ $campaign->audience_label ?: 'No audience label' }}
                @if($campaign->region_label)
                    - {{ $campaign->region_label }}
                @endif
            </div>

            <div class="mt-2">
                <span class="badge bg-light text-dark">
                    {{ $campaignStatuses[$campaign->status] ?? $campaign->status }}
                </span>

                <span class="badge bg-light text-dark">
                    {{ $campaign->members->count() }} member{{ $campaign->members->count() === 1 ? '' : 's' }}
                </span>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary">
                Back
            </a>

            <a href="{{ route('campaigns.edit', $campaign) }}" class="btn btn-outline-primary">
                Edit Campaign
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($campaign->description || $campaign->notes)
        <div class="row g-4 mb-4">
            @if($campaign->description)
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h2 class="h6 fw-bold mb-0">Description</h2>
                        </div>
                        <div class="card-body">
                            {{ $campaign->description }}
                        </div>
                    </div>
                </div>
            @endif

            @if($campaign->notes)
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h2 class="h6 fw-bold mb-0">Internal Notes</h2>
                        </div>
                        <div class="card-body">
                            {{ $campaign->notes }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h2 class="h5 fw-bold mb-0">Add Contact to Campaign</h2>
        </div>

        <div class="card-body">
            <form method="post" action="{{ route('campaigns.members.store', $campaign) }}">
                @csrf

                <div class="row g-3 align-items-end">
                    <div class="col-lg-9">
                        <label class="form-label fw-semibold">Contact</label>
                        <select name="prospect_contact_id" class="form-select" required>
                            <option value="">Select contact</option>

                            @foreach($contacts as $contact)
                                <option value="{{ $contact->id }}">
                                    {{ $contact->name ?: 'Unnamed Contact' }}
                                    @if($contact->title)
                                        - {{ $contact->title }}
                                    @endif
                                    @if($contact->prospect)
                                        - {{ $contact->prospect->company_name ?? $contact->prospect->domain }}
                                    @endif
                                    @if($contact->email)
                                        - {{ $contact->email }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <button class="btn btn-primary w-100">
                            Add Contact
                        </button>
                    </div>
                </div>

                <div class="form-text mt-2">
                    V1 adds contacts one by one. Bulk add can come later after this is stable.
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h2 class="h5 fw-bold mb-0">Campaign Members</h2>
        </div>

        <div class="card-body">
            @if($campaign->members->count())
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Contact</th>
                                <th>Company</th>
                                <th>Status</th>
                                <th>Draft</th>
                                <th>Follow-up Due</th>
                                <th>Notes</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($campaign->members as $member)
                                <tr>
                                    <td>
                                        <div class="fw-bold">
                                            {{ $member->contact?->name ?: 'No Contact' }}
                                        </div>

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
                                        <span class="badge {{ $statusBadgeMap[$member->status] ?? 'bg-secondary' }}">
                                            {{ $memberStatuses[$member->status] ?? $member->status }}
                                        </span>
                                    </td>

                                    <td>
                                        @if($member->draft)
                                            <a href="{{ route('outreach-drafts.show', $member->draft) }}" class="btn btn-sm btn-outline-primary">
                                                Open Draft
                                            </a>
                                        @else
                                            <form method="post" action="{{ route('campaign-members.draft.store', $member) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success">
                                                    Create Draft
                                                </button>
                                            </form>
                                        @endif
                                    </td>

                                    <td>
                                        @if($member->follow_up_1_due_at)
                                            <div class="small">
                                                FU1: {{ $member->follow_up_1_due_at->format('M d, Y') }}
                                            </div>
                                        @endif

                                        @if($member->follow_up_2_due_at)
                                            <div class="small">
                                                FU2: {{ $member->follow_up_2_due_at->format('M d, Y') }}
                                            </div>
                                        @endif

                                        @if(! $member->follow_up_1_due_at && ! $member->follow_up_2_due_at)
                                            -
                                        @endif
                                    </td>

                                    <td style="min-width: 220px;">
                                        <form method="post" action="{{ route('campaign-members.update', $member) }}">
                                            @csrf
                                            @method('PUT')

                                            <select name="status" class="form-select form-select-sm mb-2">
                                                @foreach($memberStatuses as $value => $label)
                                                    <option value="{{ $value }}" @selected($member->status === $value)>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <textarea name="notes" class="form-control form-control-sm mb-2" rows="2" placeholder="Notes">{{ $member->notes }}</textarea>

                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <input type="date" name="first_touch_at" class="form-control form-control-sm" value="{{ $member->first_touch_at?->format('Y-m-d') }}">
                                                </div>

                                                <div class="col-6">
                                                    <input type="date" name="follow_up_1_due_at" class="form-control form-control-sm" value="{{ $member->follow_up_1_due_at?->format('Y-m-d') }}">
                                                </div>
                                            </div>

                                            <input type="hidden" name="follow_up_1_sent_at" value="{{ $member->follow_up_1_sent_at?->format('Y-m-d') }}">
                                            <input type="hidden" name="follow_up_2_due_at" value="{{ $member->follow_up_2_due_at?->format('Y-m-d') }}">
                                            <input type="hidden" name="follow_up_2_sent_at" value="{{ $member->follow_up_2_sent_at?->format('Y-m-d') }}">
                                            <input type="hidden" name="replied_at" value="{{ $member->replied_at?->format('Y-m-d') }}">
                                            <input type="hidden" name="last_interaction_at" value="{{ $member->last_interaction_at?->format('Y-m-d') }}">

                                            <button class="btn btn-sm btn-outline-secondary mt-2">
                                                Save
                                            </button>
                                        </form>
                                    </td>

                                    
									
									<td class="text-end">
										<div class="mb-2">
											@include('campaign-members.partials.quick-actions', ['member' => $member])
										</div>

										<form method="post" action="{{ route('campaign-members.destroy', $member) }}">
											@csrf
											@method('DELETE')

											<button
												class="btn btn-sm btn-outline-danger"
												onclick="return confirm('Remove this contact from campaign?')"
											>
												Remove
											</button>
										</form>
										<a href="{{ route('campaign-members.sending-control', $member) }}" class="btn btn-sm btn-outline-warning mb-2">
											Sending Control
										</a>
									</td>
									
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-light border mb-0">
                    No contacts added to this campaign yet.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection