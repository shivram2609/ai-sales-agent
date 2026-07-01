@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Edit Campaign</h1>
            <div class="text-muted">{{ $campaign->name }}</div>
        </div>

        <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-outline-secondary">
            Back to Campaign
        </a>
    </div>

    @include('campaigns.partials.form', [
        'campaign' => $campaign,
        'statuses' => $statuses,
        'action' => route('campaigns.update', $campaign),
        'method' => 'PUT',
        'buttonText' => 'Save Campaign',
    ])
</div>
@endsection