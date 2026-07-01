@extends('layouts.app')

@section('title', $campaign->name)
@section('subtitle', 'Review generated smart queries, SERPAPI results, filtering decisions and prospect conversion.')

@section('content')
    @php
        $sortLink = function ($label, $column) use ($sort, $direction) {
            $nextDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';
            $icon = '';

            if ($sort === $column) {
                $icon = $direction === 'asc' ? ' ↑' : ' ↓';
            }

            $url = request()->fullUrlWithQuery([
                'sort' => $column,
                'direction' => $nextDirection,
            ]);

            return '<a href="'.$url.'" class="text-decoration-none text-dark">'.$label.$icon.'</a>';
        };

        $serviceFocus = is_array($campaign->service_focus) ? $campaign->service_focus : [];
        $targetAudience = is_array($campaign->target_audience) ? $campaign->target_audience : [];
        $regions = is_array($campaign->regions_json) ? $campaign->regions_json : [];
        $cities = is_array($campaign->cities_json) ? $campaign->cities_json : [];
        $excludeTerms = is_array($campaign->exclude_terms_json) ? $campaign->exclude_terms_json : [];
    @endphp

    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
        <div>
            <h1 class="page-title">{{ $campaign->name }}</h1>
            <div class="page-subtitle">
                {{ strtoupper($campaign->source_engine ?: 'serpapi_google') }}
                <span class="mx-1">·</span>
                {{ str_replace('_', ' ', $campaign->query_mode) }}
                <span class="mx-1">·</span>
                Created {{ $campaign->created_at?->diffForHumans() }}
            </div>
        </div>

        <div class="page-actions">
            <a href="{{ route('discovery.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back
            </a>

            @if(!in_array($campaign->status, ['queued', 'running'], true))
                <form method="post" action="{{ route('discovery.run', $campaign) }}" class="m-0">
                    @csrf
                    <button class="btn btn-primary">
                        <i class="bi bi-play-fill me-1"></i>
                        Run Campaign
                    </button>
                </form>
            @else
                <button class="btn btn-outline-secondary" disabled>
                    <i class="bi bi-hourglass-split me-1"></i>
                    Campaign Running
                </button>
            @endif

            @if(($stats['strong'] ?? 0) > 0)
                <form method="post" action="{{ route('discovery.convert-strong', $campaign) }}" class="m-0">
                    @csrf
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-arrow-right-circle me-1"></i>
                        Convert Strong
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Status</div>
                    <div class="mt-2">
                        <span class="status-badge {{ $campaign->status }}">
                            {{ str_replace('_', ' ', $campaign->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Total Results</div>
                    <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Strong</div>
                    <div class="stat-value">{{ $stats['strong'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Possible</div>
                    <div class="stat-value">{{ $stats['possible'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Rejected</div>
                    <div class="stat-value">{{ $stats['rejected'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Converted</div>
                    <div class="stat-value">{{ $stats['converted'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <span>Generated Smart Queries</span>
                    <span class="small text-muted">{{ count($queries) }} query/queries</span>
                </div>

                <div class="card-body">
                    @if(count($queries))
                        <div class="query-list">
                            @foreach($queries as $query)
                                <div class="query-item">
                                    <code>{{ $query }}</code>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bi bi-search"></i>
                            </div>
                            <h5 class="fw-bold mb-1">No generated queries</h5>
                            <p class="text-muted mb-0">
                                Check campaign inputs or query mode.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    Campaign Inputs
                </div>

                <div class="card-body">
                    <div class="detail-list">
                        <div>
                            <span>Mode</span>
                            <strong>{{ str_replace('_', ' ', $campaign->query_mode) }}</strong>
                        </div>

                        <div>
                            <span>Provider</span>
                            <strong>{{ $campaign->source_engine ?: 'serpapi_google' }}</strong>
                        </div>

                        <div>
                            <span>Max / Query</span>
                            <strong>{{ $campaign->max_results_per_query }}</strong>
                        </div>

                        <div>
                            <span>Focus</span>
                            <strong>{{ count($serviceFocus) ? implode(', ', $serviceFocus) : '—' }}</strong>
                        </div>

                        <div>
                            <span>Audience</span>
                            <strong>{{ count($targetAudience) ? implode(', ', $targetAudience) : '—' }}</strong>
                        </div>

                        <div>
                            <span>Regions</span>
                            <strong>{{ count($regions) ? implode(', ', $regions) : '—' }}</strong>
                        </div>

                        <div>
                            <span>Cities</span>
                            <strong>{{ count($cities) ? implode(', ', $cities) : '—' }}</strong>
                        </div>
                    </div>

                    @if(count($excludeTerms))
                        <hr>
                        <div class="detail-label mb-2">Exclude Terms</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($excludeTerms as $term)
                                <span class="badge text-bg-light border">{{ $term }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if($campaign->notes)
                        <hr>
                        <div class="detail-label mb-2">Notes</div>
                        <div class="text-muted">{{ $campaign->notes }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            Result Filters
        </div>

        <div class="card-body">
            <form method="get" action="{{ route('discovery.show', $campaign) }}" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label">Search Results</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="{{ request('search') }}"
                        placeholder="Company, domain, title, query, reason..."
                    >
                </div>

                <div class="col-lg-2">
                    <label class="form-label">Filter Status</label>
                    <select name="filter_status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach($filterStatuses as $status)
                            <option value="{{ $status }}" @selected(request('filter_status') === $status)>
                                {{ str_replace('_', ' ', $status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2">
                    <label class="form-label">Converted</label>
                    <select name="converted" class="form-select">
                        <option value="">All</option>
                        <option value="yes" @selected(request('converted') === 'yes')>Converted</option>
                        <option value="no" @selected(request('converted') === 'no')>Not Converted</option>
                    </select>
                </div>

                <div class="col-lg-1">
                    <label class="form-label">Min Score</label>
                    <input
                        type="number"
                        name="min_score"
                        class="form-control"
                        value="{{ request('min_score') }}"
                        min="0"
                        max="100"
                    >
                </div>

                <div class="col-lg-1">
                    <label class="form-label">Per Page</label>
                    <select name="per_page" class="form-select">
                        @foreach([20, 50, 100] as $size)
                            <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 d-flex gap-2">
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>
                        Filter
                    </button>

                    <a href="{{ route('discovery.show', $campaign) }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card table-card mb-4">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <span>Discovery Results</span>
            <span class="small text-muted">
                Showing {{ $results->firstItem() ?? 0 }}–{{ $results->lastItem() ?? 0 }} of {{ $results->total() }}
            </span>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 discovery-results-table">
                    <thead>
                        <tr>
                            <th>{!! $sortLink('Score', 'relevance_score') !!}</th>
                            <th>{!! $sortLink('Result', 'result_title') !!}</th>
                            <th>{!! $sortLink('Domain', 'normalized_domain') !!}</th>
                            <th>{!! $sortLink('Status', 'filter_status') !!}</th>
                            <th>Reason</th>
                            <th>{!! $sortLink('Rank', 'rank') !!}</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($results as $result)
                            <tr>
                                <td>
                                    <span class="fit-score">{{ $result->relevance_score ?? 0 }}</span>
                                </td>

                                <td>
                                    <div class="fw-bold result-title">
                                        <a href="{{ $result->result_url }}" target="_blank" class="text-decoration-none">
                                            {{ $result->result_title ?: 'Untitled result' }}
                                            <i class="bi bi-box-arrow-up-right ms-1"></i>
                                        </a>
                                    </div>

                                    @if($result->snippet)
                                        <div class="small text-muted result-snippet">
                                            {{ $result->snippet }}
                                        </div>
                                    @endif

                                    @if($result->source_query)
                                        <div class="small text-muted mt-1">
                                            Query: <code>{{ \Illuminate\Support\Str::limit($result->source_query, 120) }}</code>
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <div class="fw-semibold">{{ $result->normalized_domain }}</div>
                                    <div class="small text-muted">
                                        {{ $result->company_name_guess ?: 'No company guess' }}
                                    </div>
                                </td>

                                <td>
                                    <span class="status-badge {{ $result->filter_status }}">
                                        {{ str_replace('_', ' ', $result->filter_status) }}
                                    </span>

                                    @if($result->converted_to_prospect)
                                        <div class="mt-2">
                                            <span class="badge text-bg-success">Converted</span>
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <div class="small text-muted result-reason">
                                        {{ $result->reason ?: '—' }}
                                    </div>
                                </td>

                                <td>
                                    {{ $result->rank ?: '—' }}
                                </td>

                                <td class="text-end">
									<div class="d-inline-flex flex-wrap justify-content-end gap-2">
										<a href="{{ route('discovery-results.show', $result) }}" class="btn btn-sm btn-outline-secondary">
											Details
										</a>

										@if($result->converted_to_prospect && $result->prospect)
											<a href="{{ route('prospects.show', $result->prospect) }}" class="btn btn-sm btn-outline-primary">
												Prospect
											</a>
										@elseif($result->filter_status === 'manual_rejected')
											<button class="btn btn-sm btn-outline-danger" disabled>
												Rejected
											</button>
										@elseif(in_array($result->filter_status, ['strong_candidate', 'possible_candidate'], true))
											<form method="post" action="{{ route('discovery-results.convert', $result) }}" class="m-0">
												@csrf
												<button class="btn btn-sm btn-primary">
													Convert
												</button>
											</form>

											<form method="post" action="{{ route('discovery-results.reject', $result) }}" class="m-0">
												@csrf
												<input type="hidden" name="rejection_reason" value="Rejected from discovery campaign results list.">
												<button
													class="btn btn-sm btn-outline-danger"
													onclick="return confirm('Reject this discovery lead?')"
												>
													Reject
												</button>
											</form>
										@else
											<button class="btn btn-sm btn-outline-secondary" disabled>
												Rejected
											</button>
										@endif
									</div>
								</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-search"></i>
                                        </div>

                                        <h5 class="fw-bold mb-1">No discovery results found</h5>

                                        <p class="text-muted mb-3">
                                            Run the campaign or adjust filters.
                                        </p>

                                        @if(!in_array($campaign->status, ['queued', 'running'], true))
                                            <form method="post" action="{{ route('discovery.run', $campaign) }}">
                                                @csrf
                                                <button class="btn btn-primary">
                                                    <i class="bi bi-play-fill me-1"></i>
                                                    Run Campaign
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mb-4">
        {{ $results->links() }}
    </div>

    <div class="card">
        <div class="card-header">
            Recent Discovery Logs
        </div>

        <div class="card-body">
            @forelse($logs as $log)
                <div class="mini-record">
                    <div class="fw-bold">{{ $log->step_name ?? 'Discovery Log' }}</div>

                    @if($log->message ?? false)
                        <div class="small text-muted">{{ $log->message }}</div>
                    @endif

                    <div class="small text-muted">
                        {{ $log->created_at?->diffForHumans() }}
                    </div>
                </div>
            @empty
                <div class="text-muted">No discovery logs yet.</div>
            @endforelse
        </div>
    </div>
@endsection