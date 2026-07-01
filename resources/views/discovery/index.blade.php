@extends('layouts.app')

@section('title', 'Discovery Campaigns')
@section('subtitle', 'Find official agency and company websites using Smart Query Builder and SERPAPI.')

@section('content')
    @php
        function discovery_sort_link($label, $column, $sort, $direction) {
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
        }
    @endphp

    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Discovery Campaigns</h1>
            <div class="page-subtitle">
                Build smart SERPAPI campaigns, reject directories/listicles, and convert only official websites.
            </div>
        </div>

        <div class="page-actions">
            <a href="{{ route('discovery.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Create Campaign
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="{{ route('discovery.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label">Search</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="{{ request('search') }}"
                        placeholder="Campaign, service, region, audience..."
                    >
                </div>

                <div class="col-lg-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>
                                {{ str_replace('_', ' ', $status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2">
                    <label class="form-label">Query Mode</label>
                    <select name="query_mode" class="form-select">
                        <option value="">All modes</option>
                        @foreach($queryModes as $mode)
                            <option value="{{ $mode }}" @selected(request('query_mode') === $mode)>
                                {{ str_replace('_', ' ', $mode) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2">
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

                    <a href="{{ route('discovery.index') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{!! discovery_sort_link('Campaign', 'name', $sort, $direction) !!}</th>
                            <th>Focus</th>
                            <th>Regions</th>
                            <th>{!! discovery_sort_link('Mode', 'query_mode', $sort, $direction) !!}</th>
                            <th>{!! discovery_sort_link('Status', 'status', $sort, $direction) !!}</th>
                            <th>Results</th>
                            <th>{!! discovery_sort_link('Updated', 'updated_at', $sort, $direction) !!}</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($campaigns as $campaign)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $campaign->name }}</div>
                                    <div class="small-text">
                                        {{ $campaign->source_engine ?: 'serpapi_google' }}
                                    </div>
                                </td>

                                <td>
                                    <div class="small-text">
                                        @if(is_array($campaign->service_focus))
                                            {{ implode(', ', array_slice($campaign->service_focus, 0, 3)) }}
                                        @else
                                            {{ $campaign->service_focus ?: '—' }}
                                        @endif
                                    </div>

                                    <div class="small text-muted">
                                        @if(is_array($campaign->target_audience))
                                            {{ implode(', ', array_slice($campaign->target_audience, 0, 3)) }}
                                        @else
                                            {{ $campaign->target_audience ?: '—' }}
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <div class="small-text">
                                        @if(is_array($campaign->regions_json))
                                            {{ implode(', ', array_slice($campaign->regions_json, 0, 3)) }}
                                        @else
                                            {{ $campaign->regions_json ?: '—' }}
                                        @endif
                                    </div>

                                    @if(is_array($campaign->cities_json) && count($campaign->cities_json))
                                        <div class="small text-muted">
                                            Cities: {{ implode(', ', array_slice($campaign->cities_json, 0, 3)) }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <span class="status-badge {{ $campaign->query_mode }}">
                                        {{ str_replace('_', ' ', $campaign->query_mode) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-badge {{ $campaign->status }}">
                                        {{ str_replace('_', ' ', $campaign->status) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="fw-bold">{{ $campaign->results_count ?? 0 }}</span>
                                </td>

                                <td>
                                    <span class="small-text">
                                        {{ $campaign->updated_at?->diffForHumans() }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                        <a href="{{ route('discovery.show', $campaign) }}" class="btn btn-sm btn-outline-primary">
                                            Open
                                        </a>

                                        @if(!in_array($campaign->status, ['queued', 'running'], true))
                                            <form method="post" action="{{ route('discovery.run', $campaign) }}" class="m-0">
                                                @csrf
                                                <button class="btn btn-sm btn-primary">
                                                    Run
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                                Running
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-search"></i>
                                        </div>

                                        <h5 class="fw-bold mb-1">No discovery campaigns yet</h5>

                                        <p class="text-muted mb-3">
                                            Create your first Smart Query Builder campaign for official agency website discovery.
                                        </p>

                                        <a href="{{ route('discovery.create') }}" class="btn btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i>
                                            Create Campaign
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $campaigns->links() }}
    </div>
@endsection