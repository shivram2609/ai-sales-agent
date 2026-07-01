@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Campaigns</h1>
            <div class="text-muted">Organize outreach lists, drafts, and manual follow-up tracking.</div>
        </div>

        <a href="{{ route('campaigns.create') }}" class="btn btn-primary">
            Create Campaign
        </a>
    </div>

    <form method="get" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($selectedStatus === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn btn-outline-secondary w-100">
                        Filter
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($campaigns->count())
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Audience</th>
                                <th>Region</th>
                                <th>Status</th>
                                <th>Members</th>
                                <th>Created</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($campaigns as $campaign)
                                <tr>
                                    <td>
                                        <a href="{{ route('campaigns.show', $campaign) }}" class="fw-bold">
                                            {{ $campaign->name }}
                                        </a>
                                    </td>
                                    <td>{{ $campaign->audience_label ?: '-' }}</td>
                                    <td>{{ $campaign->region_label ?: '-' }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $statuses[$campaign->status] ?? $campaign->status }}
                                        </span>
                                    </td>
                                    <td>{{ $campaign->members_count }}</td>
                                    <td>{{ $campaign->created_at?->format('M d, Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-sm btn-outline-primary">
                                            Open
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $campaigns->links() }}
            @else
                <div class="alert alert-light border mb-0">
                    No campaigns yet.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection