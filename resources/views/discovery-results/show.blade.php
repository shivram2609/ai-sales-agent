@extends('layouts.app')

@section('title', $result->company_name_guess ?: $result->normalized_domain)
@section('subtitle', 'Review discovery result details before converting to prospect.')

@section('content')
    @php
        $isConverted = (bool) $result->converted_to_prospect;
        $isManualRejected = $result->filter_status === 'manual_rejected';
        $canConvert = ! $isConverted && ! $isManualRejected && in_array($result->filter_status, ['strong_candidate', 'possible_candidate'], true);

        $rawData = $result->raw;

        if (is_string($rawData)) {
            $decodedRaw = json_decode($rawData, true);
            $rawData = json_last_error() === JSON_ERROR_NONE ? $decodedRaw : $rawData;
        }
    @endphp

    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-start gap-3 mb-4">
        <div>
            <h1 class="page-title">
                {{ $result->company_name_guess ?: $result->normalized_domain }}
            </h1>

            <div class="page-subtitle">
                @if($result->campaign)
                    <a href="{{ route('discovery.show', $result->campaign) }}" class="text-decoration-none">
                        {{ $result->campaign->name }}
                    </a>
                    <span class="mx-1">·</span>
                @endif

                <a href="{{ $result->result_url }}" target="_blank" class="text-decoration-none">
                    {{ $result->normalized_domain }}
                    <i class="bi bi-box-arrow-up-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="lead-action-bar">
            @if($result->campaign)
                <a href="{{ route('discovery.show', $result->campaign) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Campaign
                </a>
            @endif

            <a href="{{ $result->result_url }}" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-globe me-1"></i>
                Open Website
            </a>

            @if($isConverted && $result->prospect)
                <a href="{{ route('prospects.show', $result->prospect) }}" class="btn btn-primary">
                    <i class="bi bi-building me-1"></i>
                    Open Prospect
                </a>
            @elseif($isManualRejected)
                <button class="btn btn-outline-danger" disabled>
                    <i class="bi bi-x-circle me-1"></i>
                    Rejected
                </button>
            @else
                @if($canConvert)
                    <form method="post" action="{{ route('discovery-results.convert', $result) }}">
                        @csrf
                        <button class="btn btn-primary">
                            <i class="bi bi-arrow-right-circle me-1"></i>
                            Convert to Prospect
                        </button>
                    </form>
                @endif

                <a href="#reject-lead-card" class="btn btn-outline-danger">
                    <i class="bi bi-x-circle me-1"></i>
                    Reject Lead
                </a>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Score</div>
                    <div class="stat-value">{{ $result->relevance_score ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Rank</div>
                    <div class="stat-value">{{ $result->rank ?: '—' }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Filter Status</div>
                    <div class="mt-2">
                        <span class="status-badge {{ $result->filter_status }}">
                            {{ str_replace('_', ' ', $result->filter_status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Converted</div>
                    <div class="mt-2">
                        @if($isConverted)
                            <span class="badge text-bg-success">Yes</span>
                        @else
                            <span class="badge text-bg-light border">No</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Source</div>
                    <div class="fw-bold mt-2">
                        {{ $result->source_engine ?: 'serpapi' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    Result Details
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <div class="detail-label">Result Title</div>
                        <div class="fw-bold fs-5">
                            <a href="{{ $result->result_url }}" target="_blank" class="text-decoration-none">
                                {{ $result->result_title ?: 'Untitled result' }}
                                <i class="bi bi-box-arrow-up-right ms-1"></i>
                            </a>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="detail-label">URL</div>
                        <a href="{{ $result->result_url }}" target="_blank" class="text-decoration-none">
                            {{ $result->result_url }}
                        </a>
                    </div>

                    <div class="mb-3">
                        <div class="detail-label">Snippet</div>
                        <div class="lead-snippet">
                            {{ $result->snippet ?: 'No snippet available.' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="detail-label">Reason</div>
                        <div class="lead-reason">
                            {{ $result->reason ?: 'No filtering reason available.' }}
                        </div>
                    </div>

                    <div>
                        <div class="detail-label">Source Query</div>
                        <pre class="query-preview">{{ $result->source_query ?: 'No source query stored.' }}</pre>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    Raw SERPAPI Data
                </div>

                <div class="card-body">
                    @if($rawData)
                        @if(is_array($rawData))
                            <pre class="raw-json-preview">{{ json_encode($rawData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                        @else
                            <pre class="raw-json-preview">{{ $rawData }}</pre>
                        @endif
                    @else
                        <div class="text-muted">No raw data stored.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    Lead Summary
                </div>

                <div class="card-body">
                    <div class="detail-list">
                        <div>
                            <span>Domain</span>
                            <strong>{{ $result->normalized_domain ?: '—' }}</strong>
                        </div>

                        <div>
                            <span>Company Guess</span>
                            <strong>{{ $result->company_name_guess ?: '—' }}</strong>
                        </div>

                        <div>
                            <span>Category Guess</span>
                            <strong>{{ $result->category_guess ?: '—' }}</strong>
                        </div>

                        <div>
                            <span>Region Guess</span>
                            <strong>{{ $result->region_guess ?: '—' }}</strong>
                        </div>

                        <div>
                            <span>Created</span>
                            <strong>{{ $result->created_at?->format('M d, Y h:i A') }}</strong>
                        </div>

                        <div>
                            <span>Updated</span>
                            <strong>{{ $result->updated_at?->format('M d, Y h:i A') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Decision Helper
                </div>

                <div class="card-body">
                    @if($isManualRejected)
                        <div class="rule-box bad">
                            <div class="rule-icon">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div>
                                <strong>Rejected</strong>
                                <p>This result was manually rejected and should not be converted unless reviewed again.</p>
                            </div>
                        </div>
                    @elseif($canConvert)
                        <div class="rule-box good">
                            <div class="rule-icon">
                                <i class="bi bi-check2-circle"></i>
                            </div>
                            <div>
                                <strong>Can Convert</strong>
                                <p>This result passed the current discovery filter. Still verify the website before outreach.</p>
                            </div>
                        </div>
                    @elseif($isConverted)
                        <div class="rule-box good">
                            <div class="rule-icon">
                                <i class="bi bi-building-check"></i>
                            </div>
                            <div>
                                <strong>Already Converted</strong>
                                <p>This discovery result has already been converted into a prospect.</p>
                            </div>
                        </div>
                    @else
                        <div class="rule-box bad">
                            <div class="rule-icon">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div>
                                <strong>Rejected by Filter</strong>
                                <p>This result should not be converted unless the filter logic is wrong.</p>
                            </div>
                        </div>
                    @endif

                    <div class="rule-box warning">
                        <div class="rule-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div>
                            <strong>Check Manually</strong>
                            <p>Open the website and confirm it is an official agency/company website, not a template, list, directory, or social page.</p>
                        </div>
                    </div>
                </div>
            </div>

            @if(! $isConverted && ! $isManualRejected)
                <div class="card mb-4 border-danger-subtle" id="reject-lead-card">
                    <div class="card-header text-danger">
                        Reject Lead
                    </div>

                    <div class="card-body">
                        <p class="text-muted small">
                            Use this if the result is not an official agency/company website, is a Webflow staging/template URL, directory, social page, irrelevant company, or low-quality lead.
                        </p>

                        <form method="post" action="{{ route('discovery-results.reject', $result) }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Rejection Reason</label>
                                <textarea
                                    name="rejection_reason"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Example: Webflow staging/template URL, not an official agency website."
                                ></textarea>
                            </div>

                            <button
                                class="btn btn-outline-danger w-100"
                                onclick="return confirm('Reject this discovery lead?')"
                            >
                                <i class="bi bi-x-circle me-1"></i>
                                Reject This Lead
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            @if($isManualRejected)
                <div class="card mb-4 border-danger-subtle">
                    <div class="card-header text-danger">
                        Lead Rejected
                    </div>

                    <div class="card-body">
                        <div class="rule-box bad">
                            <div class="rule-icon">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div>
                                <strong>Manual Rejection</strong>
                                <p>This lead was rejected manually and should not be converted unless reviewed again.</p>
                            </div>
                        </div>

                        <div class="detail-label mb-2">Reason</div>
                        <div class="lead-reason">
                            {{ $result->reason ?: 'No reason stored.' }}
                        </div>
                    </div>
                </div>
            @endif

            @if($isConverted && $result->prospect)
                <div class="card">
                    <div class="card-header">
                        Converted Prospect
                    </div>

                    <div class="card-body">
                        <div class="detail-list mb-3">
                            <div>
                                <span>Company</span>
                                <strong>{{ $result->prospect->company_name ?: 'Unknown' }}</strong>
                            </div>

                            <div>
                                <span>Status</span>
                                <strong>{{ str_replace('_', ' ', $result->prospect->status) }}</strong>
                            </div>

                            <div>
                                <span>Fit Score</span>
                                <strong>{{ $result->prospect->fit_score ?? '—' }}</strong>
                            </div>
                        </div>

                        <a href="{{ route('prospects.show', $result->prospect) }}" class="btn btn-primary w-100">
                            Open Prospect
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection