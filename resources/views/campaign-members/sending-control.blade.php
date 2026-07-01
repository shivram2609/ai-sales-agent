@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Sending Control</h1>
            <div class="text-muted">
                Approve, schedule, pause, resume, or stop this outreach sequence.
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('campaigns.show', $member->campaign_id) }}" class="btn btn-outline-secondary">
                Back to Campaign
            </a>

            <a href="{{ route('sending-queue.index') }}" class="btn btn-outline-primary">
                Sending Queue
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix these issues:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h2 class="h5 fw-bold mb-0">Campaign Member</h2>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <div class="small text-muted">Campaign</div>
                        <div class="fw-semibold">{{ $member->campaign?->name ?: '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Company</div>
                        <div class="fw-semibold">
                            @if($member->prospect)
                                <a href="{{ route('prospects.show', $member->prospect) }}">
                                    {{ $member->prospect->company_name ?? $member->prospect->domain }}
                                </a>
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Contact</div>
                        <div class="fw-semibold">{{ $member->contact?->name ?: 'No Contact' }}</div>

                        @if($member->contact?->title)
                            <div class="small text-muted">{{ $member->contact->title }}</div>
                        @endif

                        @if($member->contact?->email)
                            <div class="small">
                                <a href="mailto:{{ $member->contact->email }}">{{ $member->contact->email }}</a>
                            </div>
                        @endif

                        <div class="small text-muted mt-1">
                            Contact status: {{ $member->contact?->status ?: '-' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Current Member Status</div>
                        <div class="fw-semibold">{{ str_replace('_', ' ', $member->status) }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Sequence Mode</div>
                        <div class="fw-semibold">
                            {{ $sequenceModes[$member->sequence_mode] ?? $member->sequence_mode ?? 'Manual Only' }}
                        </div>
                    </div>

                    @if($member->approved_for_sending_at)
                        <div class="mb-3">
                            <div class="small text-muted">Approved At</div>
                            <div class="fw-semibold">{{ $member->approved_for_sending_at->format('M d, Y h:i A') }}</div>
                        </div>
                    @endif

                    @if($member->sequence_paused_at)
                        <div class="alert alert-warning">
                            This sequence is paused.
                        </div>
                    @endif

                    @if($member->sequence_stopped_at)
                        <div class="alert alert-danger">
                            This sequence was stopped.
                            @if($member->stop_reason)
                                <div class="small mt-1">{{ $member->stop_reason }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 fw-bold mb-0">Linked Draft Preview</h2>
                </div>

                <div class="card-body">
                    @if($member->draft)
                        <div class="mb-3">
                            <div class="small text-muted">Subject</div>
                            <div class="fw-semibold">{{ $member->draft->subject }}</div>
                        </div>

                        @if($member->draft->preheader)
                            <div class="mb-3">
                                <div class="small text-muted">Preheader</div>
                                <div>{{ $member->draft->preheader }}</div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <div class="small text-muted">Email Body</div>
                            <div class="border rounded bg-light p-3" style="white-space: pre-wrap;">{{ $member->draft->body }}</div>
                        </div>

                        <a href="{{ route('outreach-drafts.show', $member->draft) }}" class="btn btn-outline-primary">
                            Open Draft
                        </a>
                    @else
                        <div class="alert alert-light border mb-0">
                            No draft linked. Create and approve a draft before using automated sending.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h2 class="h5 fw-bold mb-0">Approve and Queue</h2>
                </div>

                <div class="card-body">
                    <form method="post" action="{{ route('campaign-members.approve-sending', $member) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sequence Mode</label>
                            <select name="sequence_mode" class="form-select" required>
                                @foreach($sequenceModes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('sequence_mode', $member->sequence_mode ?: 'manual_only') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="form-text">
                                Manual Only will approve but will not create an email queue job.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">First Email Scheduled At</label>
                            <input
                                type="datetime-local"
                                name="scheduled_at"
                                class="form-control"
                                value="{{ old('scheduled_at', now()->format('Y-m-d\TH:i')) }}"
                            >

                            <div class="form-text">
                                Used only for First Email Only or Full Sequence mode.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Approval Notes</label>
                            <textarea name="approval_notes" class="form-control" rows="3">{{ old('approval_notes', $member->approval_notes) }}</textarea>
                        </div>

                        <button class="btn btn-primary w-100" onclick="return confirm('Approve this campaign member for controlled sending?')">
                            Approve and Queue
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h2 class="h5 fw-bold mb-0">Manual Controls</h2>
                </div>

                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($member->status === \App\Models\CampaignMember::STATUS_PAUSED)
                            <form method="post" action="{{ route('campaign-members.resume-sequence', $member) }}">
                                @csrf
                                <button class="btn btn-outline-success w-100">
                                    Resume Sequence
                                </button>
                            </form>
                        @else
                            <form method="post" action="{{ route('campaign-members.pause-sequence', $member) }}">
                                @csrf
                                <button class="btn btn-outline-warning w-100">
                                    Pause Sequence
                                </button>
                            </form>
                        @endif

                        <form method="post" action="{{ route('campaign-members.stop-sequence', $member) }}">
                            @csrf

                            <textarea name="stop_reason" class="form-control mb-2" rows="2" placeholder="Reason for stopping">{{ old('stop_reason') }}</textarea>

                            <button
                                class="btn btn-outline-danger w-100"
                                onclick="return confirm('Stop this sequence and cancel queued emails?')"
                            >
                                Stop Sequence
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 fw-bold mb-0">Email Jobs</h2>
                </div>

                <div class="card-body">
                    @if($member->outboundEmailJobs->count())
                        <div class="list-group">
                            @foreach($member->outboundEmailJobs as $job)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between gap-3">
                                        <div>
                                            <div class="fw-semibold">{{ str_replace('_', ' ', $job->email_type) }}</div>
                                            <div class="small text-muted">
                                                {{ $job->status }}
                                                @if($job->scheduled_at)
                                                    - {{ $job->scheduled_at->format('M d, Y h:i A') }}
                                                @endif
                                            </div>
                                        </div>

                                        @if(in_array($job->status, ['queued', 'failed'], true))
                                            <form method="post" action="{{ route('outbound-email-jobs.cancel', $job) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-light border mb-0">
                            No email jobs yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection