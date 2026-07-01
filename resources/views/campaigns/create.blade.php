@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Create Campaign</h1>
            <div class="text-muted">Create an outreach list before sending anything manually.</div>
        </div>

        <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary">
            Back
        </a>
    </div>

    @include('campaigns.partials.form', [
        'campaign' => $campaign,
        'statuses' => $statuses,
        'action' => route('campaigns.store'),
        'method' => 'POST',
        'buttonText' => 'Create Campaign',
    ])
</div>
@endsection