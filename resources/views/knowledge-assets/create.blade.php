@extends('layouts.app')

@section('title', 'Add Knowledge Asset')
@section('subtitle', 'Add a proof link, service page, guide or case study for outreach matching.')

@section('content')
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Add Knowledge Asset</h1>
            <div class="page-subtitle">
                Add only useful proof assets that can improve outreach relevance.
            </div>
        </div>

        <div class="page-actions">
            <a href="{{ route('knowledge-assets.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Assets
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @include('knowledge-assets._form', [
                'asset' => $asset,
                'action' => route('knowledge-assets.store'),
                'method' => 'POST',
                'buttonText' => 'Create Asset',
            ])
        </div>
    </div>
@endsection