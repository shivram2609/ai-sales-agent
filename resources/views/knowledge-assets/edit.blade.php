@extends('layouts.app')

@section('title', 'Edit Knowledge Asset')
@section('subtitle', 'Update proof asset metadata used by the outreach engine.')

@section('content')
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Edit Knowledge Asset</h1>
            <div class="page-subtitle">
                {{ $asset->title }}
            </div>
        </div>

        <div class="page-actions">
            <a href="{{ route('knowledge-assets.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Assets
            </a>

            <a href="{{ $asset->url }}" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-box-arrow-up-right me-1"></i>
                Open URL
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @include('knowledge-assets._form', [
                'asset' => $asset,
                'action' => route('knowledge-assets.update', $asset),
                'method' => 'PUT',
                'buttonText' => 'Update Asset',
            ])
        </div>
    </div>
@endsection