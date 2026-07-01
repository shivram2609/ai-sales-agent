@extends('layouts.app')

@section('title', $prospect->company_name ?: $prospect->domain)
@section('subtitle', 'Prospect pipeline detail, actions, analysis, drafts and logs.')

@section('content')
    @php
        $latestAnalysis = $prospect->analyses->first();
        $latestDraft = $prospect->drafts->first();
    @endphp

    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
        <div>
            <h1 class="page-title">{{ $prospect->company_name ?: $prospect->domain }}</h1>

            <div class="page-subtitle">
                <a href="{{ $prospect->website_url }}" target="_blank" class="text-decoration-none">
                    {{ $prospect->website_url }}
                    <i class="bi bi-box-arrow-up-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="page-actions">
            <a href="{{ route('prospects.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back
            </a>

            <a href="{{ $prospect->website_url }}" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-globe me-1"></i>
                Visit Site
            </a>
        </div>
    </div>
	@include('prospects.partials.contacts-card', ['prospect' => $prospect])

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Status</div>
                    <div class="mt-2">
                        <span class="status-badge {{ $prospect->status }}">
                            {{ str_replace('_', ' ', $prospect->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Fit Score</div>
                    <div class="stat-value">{{ $prospect->fit_score ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Category</div>
                    <div class="fw-bold mt-2">{{ $prospect->category_guess ?: '—' }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Region</div>
                    <div class="fw-bold mt-2">{{ $prospect->country_guess ?: '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <span>Pipeline Actions</span>
            <span class="small text-muted">Run in order from left to right</span>
        </div>

        <div class="card-body">
            <div class="pipeline-actions">
                <form method="post" action="{{ route('prospects.crawl', $prospect) }}">
                    @csrf
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-download me-1"></i>
                        Crawl Homepage
                    </button>
                </form>

                <form method="post" action="{{ route('prospects.analyze', $prospect) }}">
                    @csrf
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-graph-up me-1"></i>
                        Analyze
                    </button>
                </form>

                <form method="post" action="{{ route('prospects.proof-match', $prospect) }}">
                    @csrf
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-link-45deg me-1"></i>
                        Match Proof
                    </button>
                </form>

                <form method="post" action="{{ route('prospects.generate-draft', $prospect) }}">
                    @csrf
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-envelope-plus me-1"></i>
                        Generate Draft
                    </button>
                </form>

                <form method="post" action="{{ route('prospects.quality-latest-draft', $prospect) }}">
                    @csrf
                    <button class="btn btn-primary">
                        <i class="bi bi-shield-check me-1"></i>
                        Quality Check Latest Draft
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card mb-4">
                <div class="card-header">
                    Latest Analysis
                </div>

                <div class="card-body">
                    @if($latestAnalysis)
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="detail-label">Company Type</div>
                                <div class="detail-value">{{ $latestAnalysis->company_type ?: '—' }}</div>
                            </div>

                            <div class="col-md-6">
                                <div class="detail-label">Recommended Action</div>
                                <div class="detail-value">
                                    <span class="status-badge {{ $latestAnalysis->recommended_action }}">
                                        {{ str_replace('_', ' ', $latestAnalysis->recommended_action ?: '—') }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="detail-label">Final Score</div>
                                <div class="detail-value fw-bold">{{ $latestAnalysis->final_score ?? '—' }}</div>
                            </div>

                            <div class="col-md-6">
                                <div class="detail-label">Technical Depth</div>
                                <div class="detail-value">{{ $latestAnalysis->visible_technical_depth ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="detail-label">Likely Gap</div>
                            <div class="detail-value">{{ $latestAnalysis->likely_gap ?: '—' }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="detail-label">Partnership Angle</div>
                            <div class="detail-value">{{ $latestAnalysis->partnership_angle ?: '—' }}</div>
                        </div>

                        <div>
                            <div class="detail-label">Reasoning Summary</div>
                            <div class="detail-value">{{ $latestAnalysis->reasoning_summary ?: '—' }}</div>
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <h5 class="fw-bold mb-1">No analysis yet</h5>
                            <p class="text-muted mb-3">Crawl the homepage first, then run analyzer.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Drafts
                </div>

                <div class="card-body">
                    @forelse($prospect->drafts as $draft)
                        <div class="draft-box">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
                                <div>
                                    <div class="fw-bold">{{ $draft->subject ?: 'No subject' }}</div>
                                    <div class="small text-muted">
                                        {{ $draft->created_at?->diffForHumans() }}
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <span class="status-badge {{ $draft->status }}">
                                        {{ str_replace('_', ' ', $draft->status) }}
                                    </span>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary js-copy-text"
                                        data-copy-target="#draft-copy-{{ $draft->id }}"
                                    >
                                        <i class="bi bi-clipboard me-1"></i>
                                        Copy Draft
                                    </button>

                                    <a href="{{ route('outreach-drafts.show', $draft) }}" class="btn btn-sm btn-outline-primary">
                                        Open Draft
                                    </a>
                                </div>
                            </div>

                            @if($draft->ai_quality_score !== null)
								<div class="small text-muted mb-2">
									AI Quality:
									<span class="status-badge {{ $draft->ai_quality_score >= 70 ? 'active' : 'manual_rejected' }}">
										{{ $draft->ai_quality_score }}/100
									</span>
								</div>

								@if($draft->ai_personalization_score !== null)
									<div class="small text-muted mb-2">
										Personalization:
										<span class="status-badge {{ $draft->ai_personalization_score >= 60 ? 'active' : 'manual_rejected' }}">
											{{ $draft->ai_personalization_score }}/100
										</span>
									</div>
								@endif

								@if($draft->ai_relevance_score !== null)
									<div class="small text-muted mb-2">
										Relevance:
										<span class="status-badge {{ $draft->ai_relevance_score >= 60 ? 'active' : 'manual_rejected' }}">
											{{ $draft->ai_relevance_score }}/100
										</span>
									</div>
								@endif

								@if($draft->ai_spam_risk_score !== null)
									<div class="small text-muted mb-2">
										Spam Risk:
										<span class="status-badge {{ $draft->ai_spam_risk_score <= 40 ? 'active' : 'manual_rejected' }}">
											{{ $draft->ai_spam_risk_score }}/100
										</span>
									</div>
								@endif
							@endif

                            <textarea id="draft-copy-{{ $draft->id }}" class="copy-source" readonly>
								@if($draft->subject)
								Subject: {{ $draft->subject }}

								@endif
								{{ $draft->body }}
							</textarea>

                            <pre class="draft-preview">{{ $draft->body }}</pre>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bi bi-envelope-paper"></i>
                            </div>

                            <h5 class="fw-bold mb-1">No drafts yet</h5>

                            <p class="text-muted mb-3">
                                Generate a draft after analysis and proof matching.
                            </p>

                            <form method="post" action="{{ route('prospects.generate-draft', $prospect) }}">
                                @csrf
                                <button class="btn btn-primary">Generate Draft</button>
                            </form>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card mb-4">
                <div class="card-header">
                    Prospect Info
                </div>

                <div class="card-body">
                    <div class="detail-list">
                        <div>
                            <span>Domain</span>
                            <strong>{{ $prospect->domain }}</strong>
                        </div>

                        <div>
                            <span>Source</span>
                            <strong>{{ $prospect->source ?: '—' }}</strong>
                        </div>

                        <div>
                            <span>Created</span>
                            <strong>{{ $prospect->created_at?->format('M d, Y h:i A') }}</strong>
                        </div>

                        <div>
                            <span>Updated</span>
                            <strong>{{ $prospect->updated_at?->format('M d, Y h:i A') }}</strong>
                        </div>
                    </div>

                    @if($prospect->notes)
                        <hr>
                        <div class="detail-label mb-1">Notes</div>
                        <div class="text-muted">{{ $prospect->notes }}</div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Crawled Pages
                </div>

                <div class="card-body">
                    @forelse($prospect->pages->take(5) as $page)
                        <div class="mini-record">
                            <div class="fw-bold">{{ $page->title ?: 'Untitled page' }}</div>
                            <div class="small text-muted">
                                {{ $page->page_type }} · {{ $page->created_at?->diffForHumans() }}
                            </div>
                            <div class="small text-muted">
                                Text length: {{ strlen($page->main_text ?? '') }}
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">No crawled pages yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Review Actions
                </div>

                <div class="card-body">
                    @forelse($prospect->reviewActions as $action)
                        <div class="mini-record">
                            <div>
                                <span class="status-badge {{ $action->action }}">
                                    {{ str_replace('_', ' ', $action->action) }}
                                </span>
                            </div>
                            <div class="small text-muted mt-1">
                                {{ $action->notes ?: 'No notes' }}
                            </div>
                            <div class="small text-muted">
                                {{ $action->created_at?->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">No review actions yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    Recent Agent Logs
                </div>

                <div class="card-body">
                    @forelse($prospect->agentLogs->take(10) as $log)
                        <div class="mini-record">
                            <div class="fw-bold">{{ $log->step_name }}</div>
                            <div class="small text-muted">{{ $log->created_at?->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="text-muted">No logs yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection