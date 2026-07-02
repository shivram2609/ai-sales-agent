@extends('layouts.app')

@section('title', $draft->subject ?: 'Outreach Draft')
@section('subtitle', 'Review and copy this draft manually. No automatic sending.')

@section('content')
    @php
        $prospect = $draft->prospect;

        $fullDraft = trim(
            ($draft->subject ? "Subject: {$draft->subject}\n\n" : '') .
            "EMAIL:\n" . ($draft->body ?: '') . "\n\n" .
            "LINKEDIN CONNECTION NOTE:\n" . ($draft->linkedin_connection_note ?: '') . "\n\n" .
            "LINKEDIN FOLLOW-UP:\n" . ($draft->linkedin_followup ?: '') . "\n\n" .
            "FOLLOW-UP 1:\n" . ($draft->follow_up_1 ?: '') . "\n\n" .
            "FOLLOW-UP 2:\n" . ($draft->follow_up_2 ?: '')
        );
    @endphp

    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-start gap-3 mb-4">
        <div>
            <h1 class="page-title">
                {{ $draft->subject ?: 'Outreach Draft' }}
            </h1>
			
			@if($draft->contact)
				<div class="mb-3">
					<div class="small text-muted fw-semibold">Assigned Contact</div>

					<div class="p-3 bg-light border rounded">
						<div class="fw-bold">
							{{ $draft->contact->name ?: 'Unnamed Contact' }}

							@if($draft->contact->is_primary)
								<span class="badge bg-primary ms-1">Primary</span>
							@endif
						</div>

						@if($draft->contact->title)
							<div class="text-muted small">{{ $draft->contact->title }}</div>
						@endif

						@if($draft->contact->email)
							<div class="small">
								<a href="mailto:{{ $draft->contact->email }}">
									{{ $draft->contact->email }}
								</a>
							</div>
						@endif

						@if($draft->contact->linkedin_url)
							<div class="small">
								<a href="{{ $draft->contact->linkedin_url }}" target="_blank" rel="noopener">
									LinkedIn
								</a>
							</div>
						@endif
					</div>
				</div>
			@endif

            <div class="page-subtitle">
                @if($prospect)
                    Draft for
                    <a href="{{ url('/prospects/' . $prospect->id) }}" class="text-decoration-none">
                        {{ $prospect->company_name ?: $prospect->domain }}
                    </a>
                @else
                    Draft is not linked with a prospect.
                @endif
            </div>
        </div>

        <div class="draft-action-bar">
            <a href="{{ route('outreach-drafts.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Drafts
            </a>

            @if($prospect)
                <a href="{{ url('/prospects/' . $prospect->id) }}" class="btn btn-outline-primary">
                    <i class="bi bi-building me-1"></i>
                    Open Prospect
                </a>
            @endif
			
			<a href="{{ route('outreach-drafts.edit', $draft) }}" class="btn btn-outline-secondary">
				<i class="bi bi-pencil-square me-1"></i>
				Edit Draft
			</a>
			

            <form method="post" action="{{ route('outreach-drafts.improve-ai', $draft) }}">
				@csrf

				@php
					$hasAiFeedback = ! empty($draft->ai_quality_notes);
				@endphp

				<button
					class="btn btn-outline-primary"
					onclick="return confirm('{{ $hasAiFeedback ? 'Rewrite this draft using AI quality feedback? Current draft text will be updated.' : 'Improve this draft with AI? Current draft text will be updated.' }}')"
				>
					<i class="bi bi-stars me-1"></i>
					{{ $hasAiFeedback ? 'Rewrite using AI Feedback' : 'Improve with AI' }}
				</button>
			</form>

            <form method="post" action="{{ route('outreach-drafts.quality-check-ai', $draft) }}">
                @csrf
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-shield-check me-1"></i>
                    AI Quality Check
                </button>
            </form>

            <button
                type="button"
                class="btn btn-primary js-copy-text"
                data-copy-target="#copy-full-draft-{{ $draft->id }}"
            >
                <i class="bi bi-clipboard me-1"></i>
                Copy Full Draft
            </button>
        </div>
    </div>

    <textarea id="copy-full-draft-{{ $draft->id }}" class="copy-source" readonly>{{ $fullDraft }}</textarea>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Email Draft</span>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary js-copy-text"
                        data-copy-target="#copy-email-draft-{{ $draft->id }}"
                    >
                        <i class="bi bi-clipboard me-1"></i>
                        Copy Email
                    </button>
                </div>

                <div class="card-body">
                    <textarea id="copy-email-draft-{{ $draft->id }}" class="copy-source" readonly>@if($draft->subject)
Subject: {{ $draft->subject }}

@endif{{ $draft->body }}</textarea>

                    @if($draft->subject)
                        <div class="mb-3">
                            <div class="detail-label">Subject</div>
                            <div class="draft-subject-preview">
                                {{ $draft->subject }}
                            </div>
                        </div>
                    @endif

                    <div class="detail-label mb-2">Body</div>

                    <div class="draft-box">
                        {!! nl2br(e($draft->body ?: 'No email body available.')) !!}
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>LinkedIn Connection Note</span>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary js-copy-text"
                        data-copy-target="#copy-linkedin-note-{{ $draft->id }}"
                    >
                        <i class="bi bi-clipboard me-1"></i>
                        Copy
                    </button>
                </div>

                <div class="card-body">
                    <textarea id="copy-linkedin-note-{{ $draft->id }}" class="copy-source" readonly>{{ $draft->linkedin_connection_note }}</textarea>

                    <div class="draft-box light-preview">
                        {!! nl2br(e($draft->linkedin_connection_note ?: 'No LinkedIn connection note generated.')) !!}
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>LinkedIn Follow-up</span>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary js-copy-text"
                        data-copy-target="#copy-linkedin-followup-{{ $draft->id }}"
                    >
                        <i class="bi bi-clipboard me-1"></i>
                        Copy
                    </button>
                </div>

                <div class="card-body">
                    <textarea id="copy-linkedin-followup-{{ $draft->id }}" class="copy-source" readonly>{{ $draft->linkedin_followup }}</textarea>

                    <div class="draft-box light-preview">
                        {!! nl2br(e($draft->linkedin_followup ?: 'No LinkedIn follow-up generated.')) !!}
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Email Follow-up 1</span>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary js-copy-text"
                        data-copy-target="#copy-follow-up-1-{{ $draft->id }}"
                    >
                        <i class="bi bi-clipboard me-1"></i>
                        Copy
                    </button>
                </div>

                <div class="card-body">
                    <textarea id="copy-follow-up-1-{{ $draft->id }}" class="copy-source" readonly>{{ $draft->follow_up_1 }}</textarea>

                    <div class="draft-box light-preview">
                        {!! nl2br(e($draft->follow_up_1 ?: 'No follow-up 1 generated.')) !!}
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Email Follow-up 2</span>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary js-copy-text"
                        data-copy-target="#copy-follow-up-2-{{ $draft->id }}"
                    >
                        <i class="bi bi-clipboard me-1"></i>
                        Copy
                    </button>
                </div>

                <div class="card-body">
                    <textarea id="copy-follow-up-2-{{ $draft->id }}" class="copy-source" readonly>{{ $draft->follow_up_2 }}</textarea>

                    <div class="draft-box light-preview">
                        {!! nl2br(e($draft->follow_up_2 ?: 'No follow-up 2 generated.')) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    Draft Summary
                </div>

                <div class="card-body">
                    <div class="detail-list">
                        <div>
                            <span>Status</span>
                            <strong>{{ str_replace('_', ' ', $draft->status ?: 'draft') }}</strong>
                        </div>

                        <div>
                            <span>Created</span>
                            <strong>{{ $draft->created_at?->format('M d, Y h:i A') }}</strong>
                        </div>

                        <div>
                            <span>Updated</span>
                            <strong>{{ $draft->updated_at?->format('M d, Y h:i A') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    AI Review
                </div>

                <div class="card-body">
                    @if($draft->ai_verdict)
                        <div class="mb-3">
                            <span class="status-badge {{ $draft->ai_verdict }}">
                                {{ str_replace('_', ' ', $draft->ai_verdict) }}
                            </span>
                        </div>
                    @endif

                    @if($draft->ai_quality_score !== null)
                        <div class="ai-score-grid mb-3">
                            <div>
                                <span>Overall</span>
                                <strong>{{ $draft->ai_quality_score }}/100</strong>
                            </div>

                            <div>
                                <span>Personalization</span>
                                <strong>{{ $draft->ai_personalization_score ?? '—' }}/100</strong>
                            </div>

                            <div>
                                <span>Relevance</span>
                                <strong>{{ $draft->ai_relevance_score ?? '—' }}/100</strong>
                            </div>

                            <div>
                                <span>Proof</span>
                                <strong>{{ $draft->ai_proof_score ?? '—' }}/100</strong>
                            </div>

                            <div>
                                <span>Spam Risk</span>
                                <strong>{{ $draft->ai_spam_risk_score ?? '—' }}/100</strong>
                            </div>
                        </div>

                        @if($draft->ai_quality_notes)
                            <div class="detail-label mb-2">Quality Notes</div>
                            <div class="ai-notes-box">
                                {!! nl2br(e($draft->ai_quality_notes)) !!}
                            </div>
                        @endif

                        @if($draft->ai_improvement_notes)
                            <div class="detail-label mb-2 mt-3">Improvement Notes</div>
                            <div class="ai-notes-box">
                                {!! nl2br(e($draft->ai_improvement_notes)) !!}
                            </div>
                        @endif

                        <div class="small text-muted mt-3">
                            Model: {{ $draft->ai_model_used ?: '—' }}<br>
                            Checked: {{ $draft->ai_last_checked_at?->diffForHumans() ?: '—' }}
                        </div>
                    @else
                        <div class="text-muted">
                            No AI review yet. Run Improve with AI or AI Quality Check.
                        </div>
                    @endif
                </div>
            </div>

            @if($prospect)
                <div class="card mb-4">
                    <div class="card-header">
                        Prospect Context
                    </div>

                    <div class="card-body">
                        <div class="detail-list mb-3">
                            <div>
                                <span>Company</span>
                                <strong>{{ $prospect->company_name ?: 'Unknown' }}</strong>
                            </div>

                            <div>
                                <span>Domain</span>
                                <strong>{{ $prospect->domain ?: '—' }}</strong>
                            </div>

                            <div>
                                <span>Status</span>
                                <strong>{{ str_replace('_', ' ', $prospect->status ?: 'new') }}</strong>
                            </div>

                            <div>
                                <span>Fit Score</span>
                                <strong>{{ $prospect->fit_score ?? '—' }}</strong>
                            </div>
                        </div>

                        <a href="{{ url('/prospects/' . $prospect->id) }}" class="btn btn-outline-primary w-100">
                            Open Prospect
                        </a>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    Sending Rule
                </div>

                <div class="card-body">
                    <div class="rule-box warning">
                        <div class="rule-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>

                        <div>
                            <strong>Manual approval required</strong>
                            <p>
                                Review personalization, proof links, tone and relevance before sending. This system should prepare drafts, not send spam.
                            </p>
                        </div>
                    </div>

                    <div class="rule-box good">
                        <div class="rule-icon">
                            <i class="bi bi-check2-circle"></i>
                        </div>

                        <div>
                            <strong>Best use</strong>
                            <p>
                                Copy email manually, lightly personalize first line, then send from the right inbox.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection