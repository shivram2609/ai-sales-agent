@extends('layouts.app')

@section('title', 'Prospects')
@section('subtitle', 'Manage leads before crawl, analysis, draft and review.')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Prospects</h1>
            <div class="page-subtitle">
                Search, filter, crawl, analyze and approve agency leads.
            </div>
        </div>

        <div class="page-actions">
            <a href="{{ route('prospects.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Add Prospect
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="{{ route('prospects.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label">Search</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="{{ request('search') }}"
                        placeholder="Company, domain, category, region..."
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
                    <label class="form-label">Source</label>
                    <select name="source" class="form-select">
                        <option value="">All sources</option>
                        @foreach($sources as $source)
                            <option value="{{ $source }}" @selected(request('source') === $source)>
                                {{ $source }}
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

                    <a href="{{ route('prospects.index') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    @include('prospects._table', ['prospects' => $prospects])

    <div class="mt-4">
        {{ $prospects->links() }}
    </div>
@endsection