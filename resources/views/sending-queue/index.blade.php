@extends('layouts.app')

@section('content')
@php
    $badgeMap = [
        'queued' => 'bg-info text-dark',
        'sending' => 'bg-warning text-dark',
        'sent' => 'bg-success',
        'failed' => 'bg-danger',
        'cancelled' => 'bg-secondary',
        'skipped' => 'bg-dark',
    ];
@endphp

<div class="container-fluid py-4">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Sending Queue</h1>
            <div class="text-muted">
                Controlled queue for approved outbound emails. Actual provider sending will be connected next.
            </div>
        </div>

        <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary">
            Open Campaigns
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

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
            @if($jobs->count())
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Scheduled</th>
                                <th>Type</th>
                                <th>To</th>
                                <th>Company</th>
                                <th>Campaign</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Draft</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($jobs as $job)
                                <tr>
                                    <td>
                                        {{ $job->scheduled_at?->format('M d, Y h:i A') ?: '-' }}
                                    </td>

                                    <td>
                                        {{ $types[$job->email_type] ?? $job->email_type }}
                                    </td>

                                    <td>
                                        <div class="fw-semibold">{{ $job->to_name ?: '-' }}</div>
                                        <div class="small">
                                            <a href="mailto:{{ $job->to_email }}">{{ $job->to_email }}</a>
                                        </div>
                                    </td>

                                    <td>
                                        @if($job->prospect)
                                            <a href="{{ route('prospects.show', $job->prospect) }}">
                                                {{ $job->prospect->company_name ?? $job->prospect->domain }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if($job->campaign)
                                            <a href="{{ route('campaigns.show', $job->campaign) }}">
                                                {{ $job->campaign->name }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td style="min-width: 260px;">
                                        <div class="fw-semibold">{{ $job->subject }}</div>

                                        @if($job->preheader)
                                            <div class="small text-muted">{{ $job->preheader }}</div>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge {{ $badgeMap[$job->status] ?? 'bg-secondary' }}">
                                            {{ $statuses[$job->status] ?? $job->status }}
                                        </span>
                                    </td>

                                    <td>
                                        @if($job->draft)
                                            <a href="{{ route('outreach-drafts.show', $job->draft) }}" class="btn btn-sm btn-outline-primary">
                                                Open Draft
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            @if($job->campaignMember)
                                                <a href="{{ route('campaign-members.sending-control', $job->campaignMember) }}" class="btn btn-sm btn-outline-secondary">
                                                    Control
                                                </a>
                                            @endif

                                            @if(in_array($job->status, ['queued', 'failed'], true))
                                                <form method="post" action="{{ route('outbound-email-jobs.cancel', $job) }}">
                                                    @csrf

                                                    <input type="hidden" name="reason" value="Cancelled from Sending Queue">

                                                    <button
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Cancel this queued email?')"
                                                    >
                                                        Cancel
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $jobs->links() }}
            @else
                <div class="alert alert-light border mb-0">
                    No outbound email jobs found.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection