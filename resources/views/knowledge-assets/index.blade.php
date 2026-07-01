@extends('layouts.app')

@section('title', 'Knowledge Assets')
@section('subtitle', 'Manage proof links, case studies and service pages used by the outreach engine.')

@section('content')
    @php
        $nextDirection = $direction === 'asc' ? 'desc' : 'asc';

        $sortUrl = function ($field) use ($nextDirection) {
            return request()->fullUrlWithQuery([
                'sort' => $field,
                'direction' => $nextDirection,
            ]);
        };
    @endphp

    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Knowledge Assets</h1>
            <div class="page-subtitle">
                These proof links are used for proof matching and outreach personalization.
            </div>
        </div>

        <div class="knowledge-action-bar">
            <form method="post" action="{{ route('knowledge-assets.seed') }}">
                @csrf
                <button class="btn btn-outline-secondary" onclick="return confirm('Seed default Zestminds assets? Existing URLs will be updated.')">
                    <i class="bi bi-arrow-repeat me-1"></i>
                    Seed Defaults
                </button>
            </form>

            <a href="{{ route('knowledge-assets.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Add Asset
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card mini-metric-card h-100">
                <div class="card-body">
                    <div class="mini-metric-label">Total Assets</div>
                    <div class="mini-metric-value">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card mini-metric-card h-100">
                <div class="card-body">
                    <div class="mini-metric-label">Active</div>
                    <div class="mini-metric-value">{{ $stats['active'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card mini-metric-card h-100">
                <div class="card-body">
                    <div class="mini-metric-label">Inactive</div>
                    <div class="mini-metric-value">{{ $stats['inactive'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card mini-metric-card h-100">
                <div class="card-body">
                    <div class="mini-metric-label">Case Studies</div>
                    <div class="mini-metric-value">{{ $stats['case_studies'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card table-card mb-4">
        <div class="card-body">
            <form method="get" action="{{ route('knowledge-assets.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label">Search</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Search title, URL, tag, industry, technology..."
                    >
                </div>

                <div class="col-lg-2">
                    <label class="form-label">Asset Type</label>
                    <select name="asset_type" class="form-select">
                        <option value="">All Types</option>

                        @foreach($assetTypes as $assetType)
                            <option value="{{ $assetType }}" @selected(request('asset_type') === $assetType)>
                                {{ str_replace('_', ' ', $assetType) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2">
                    <label class="form-label">Status</label>
                    <select name="active" class="form-select">
                        <option value="">All</option>
                        <option value="1" @selected(request('active') === '1')>Active</option>
                        <option value="0" @selected(request('active') === '0')>Inactive</option>
                    </select>
                </div>

                <div class="col-lg-2">
                    <label class="form-label">Per Page</label>
                    <select name="per_page" class="form-select">
                        @foreach([20, 50, 100] as $value)
                            <option value="{{ $value }}" @selected((int) request('per_page', $perPage) === $value)>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2">
                    <div class="d-grid">
                        <button class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i>
                            Filter
                        </button>
                    </div>
                </div>

                @if(request()->hasAny(['search', 'asset_type', 'active', 'per_page']))
                    <div class="col-12">
                        <a href="{{ route('knowledge-assets.index') }}" class="btn btn-sm btn-outline-secondary">
                            Clear Filters
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between gap-2">
            <div>
                <strong>Assets</strong>
                <span class="text-muted">({{ $assets->total() }})</span>
            </div>

            <div class="text-muted small">
                Showing {{ $assets->firstItem() ?? 0 }} - {{ $assets->lastItem() ?? 0 }} of {{ $assets->total() }}
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ $sortUrl('title') }}" class="table-sort-link">
                                    Asset
                                </a>
                            </th>

                            <th>
                                <a href="{{ $sortUrl('asset_type') }}" class="table-sort-link">
                                    Type
                                </a>
                            </th>

                            <th>Tags / Tech</th>

                            <th>Use When</th>

                            <th>
                                <a href="{{ $sortUrl('is_active') }}" class="table-sort-link">
                                    Status
                                </a>
                            </th>

                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($assets as $asset)
                            <tr>
                                <td>
                                    <div class="fw-bold">
                                        {{ $asset->title }}
                                    </div>

                                    <a href="{{ $asset->url }}" target="_blank" class="small text-decoration-none">
                                        {{ $asset->url }}
                                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                                    </a>

                                    @if($asset->summary)
                                        <div class="small text-muted mt-1">
                                            {{ \Illuminate\Support\Str::limit($asset->summary, 120) }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <span class="asset-type-pill">
                                        {{ str_replace('_', ' ', $asset->asset_type) }}
                                    </span>
                                </td>

                                <td>
                                    <div class="knowledge-tags">
                                        @foreach(array_slice($asset->tags_json ?? [], 0, 3) as $tag)
                                            <span>{{ $tag }}</span>
                                        @endforeach

                                        @foreach(array_slice($asset->technologies_json ?? [], 0, 3) as $technology)
                                            <span>{{ $technology }}</span>
                                        @endforeach
                                    </div>
                                </td>

                                <td>
                                    <div class="small text-muted knowledge-use-when">
                                        {{ \Illuminate\Support\Str::limit($asset->use_when ?: '—', 120) }}
                                    </div>
                                </td>

                                <td>
                                    @if($asset->is_active)
                                        <span class="status-badge active">Active</span>
                                    @else
                                        <span class="status-badge inactive">Inactive</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <div class="knowledge-list-actions">
                                        <a href="{{ route('knowledge-assets.edit', $asset) }}" class="btn btn-sm btn-outline-primary">
                                            Edit
                                        </a>

                                        <form method="post" action="{{ route('knowledge-assets.toggle', $asset) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button class="btn btn-sm {{ $asset->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                {{ $asset->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-link-45deg"></i>
                                        </div>

                                        <h5 class="fw-bold mb-1">No knowledge assets yet</h5>

                                        <p class="text-muted mb-3">
                                            Seed default Zestminds assets or add a new proof link manually.
                                        </p>

                                        <form method="post" action="{{ route('knowledge-assets.seed') }}">
                                            @csrf
                                            <button class="btn btn-primary">
                                                Seed Default Assets
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($assets->hasPages())
            <div class="card-footer">
                {{ $assets->links() }}
            </div>
        @endif
    </div>
@endsection