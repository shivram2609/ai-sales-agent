@extends('layouts.app')

@section('title', 'Outreach Drafts')
@section('subtitle', 'Review AI-generated outreach drafts before sending manually.')

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
            <h1 class="page-title">Outreach Drafts</h1>
            <div class="page-subtitle">
                Search, review and copy outreach drafts. Human approval stays mandatory.
            </div>
        </div>

        <div class="page-actions">
            <a href="{{ url('/prospects') }}" class="btn btn-outline-primary">
                <i class="bi bi-building me-1"></i>
                Prospects
            </a>

            <a href="{{ url('/review-queue') }}" class="btn btn-primary">
                <i class="bi bi-check2-square me-1"></i>
                Review Queue
            </a>
        </div>
    </div>

    <div class="card table-card mb-4">
        <div class="card-body">
            <form method="get" action="{{ route('outreach-drafts.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <label class="form-label">Search</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Search subject, draft text, company, domain..."
                    >
                </div>

                <div class="col-lg-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>

                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>
                                {{ str_replace('_', ' ', $status) }}
                            </option>
                        @endforeach
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

                @if(request()->hasAny(['search', 'status', 'per_page']))
                    <div class="col-12">
                        <a href="{{ route('outreach-drafts.index') }}" class="btn btn-sm btn-outline-secondary">
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
                <strong>Drafts</strong>
                <span class="text-muted">({{ $drafts->total() }})</span>
            </div>

            <div class="text-muted small">
                Showing {{ $drafts->firstItem() ?? 0 }} - {{ $drafts->lastItem() ?? 0 }} of {{ $drafts->total() }}
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ $sortUrl('subject') }}" class="table-sort-link">
                                    Subject
                                </a>
                            </th>

                            <th>Prospect</th>

                            <th>
                                <a href="{{ $sortUrl('status') }}" class="table-sort-link">
                                    Status
                                </a>
                            </th>

                            <th>
                                <a href="{{ $sortUrl('created_at') }}" class="table-sort-link">
                                    Created
                                </a>
                            </th>

                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($drafts as $draft)
                            <tr>
                                <td>
                                    <div class="fw-bold">
                                        {{ $draft->subject ?: 'No subject' }}
                                    </div>

                                    <div class="small text-muted draft-snippet">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($draft->body ?: ''), 120) }}
                                    </div>
                                </td>

                                <td>
                                    @if($draft->prospect)
                                        <div class="fw-semibold">
                                            {{ $draft->prospect->company_name ?: 'Unknown Company' }}
                                        </div>

                                        <div class="small text-muted">
                                            {{ $draft->prospect->domain ?: $draft->prospect->website_url }}
                                        </div>
                                    @else
                                        <span class="text-muted">No prospect</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="status-badge {{ $draft->status }}">
                                        {{ str_replace('_', ' ', $draft->status ?: 'draft') }}
                                    </span>
                                </td>

                                <td>
                                    <div class="small">
                                        {{ $draft->created_at?->format('M d, Y') }}
                                    </div>

                                    <div class="small text-muted">
                                        {{ $draft->created_at?->diffForHumans() }}
                                    </div>
                                </td>

                                <td class="text-end">
                                    <div class="draft-list-actions">
                                        @if($draft->prospect)
                                            <a href="{{ url('/prospects/' . $draft->prospect->id) }}" class="btn btn-sm btn-outline-secondary">
                                                Prospect
                                            </a>
                                        @endif

                                        <a href="{{ route('outreach-drafts.show', $draft) }}" class="btn btn-sm btn-primary">
                                            Open Draft
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-envelope-paper"></i>
                                        </div>

                                        <h5 class="fw-bold mb-1">No outreach drafts yet</h5>

                                        <p class="text-muted mb-0">
                                            Generate drafts from a prospect detail page after crawling, analyzing and matching proof.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($drafts->hasPages())
            <div class="card-footer">
                {{ $drafts->links() }}
            </div>
        @endif
    </div>
@endsection