@extends('layouts.app')

@section('title', 'Create Discovery Campaign')
@section('subtitle', 'Use Smart Query Builder to find official agency and company websites.')

@section('content')
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Create Discovery Campaign</h1>
            <div class="page-subtitle">
                Build dynamic SERPAPI searches from service focus, audience, region and exclusions.
            </div>
        </div>

        <div class="page-actions">
            <a href="{{ route('discovery.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Discovery
            </a>
        </div>
    </div>

    <form method="post" action="{{ route('discovery.store') }}">
        @csrf

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card mb-4">
                    <div class="card-header">
                        Campaign Basics
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Campaign Name <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="{{ old('name') }}"
                                placeholder="Smart Test - Webflow SaaS Agencies USA"
                                required
                            >
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Query Mode <span class="text-danger">*</span></label>
                                <select name="query_mode" class="form-select" required>
                                    <option value="smart" @selected(old('query_mode', 'smart') === 'smart')>
                                        Smart Query Builder
                                    </option>
                                    <option value="exact" @selected(old('query_mode') === 'exact')>
                                        Exact Queries
                                    </option>
                                </select>
                                <div class="form-text">
                                    Smart mode should be default.
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Search Provider <span class="text-danger">*</span></label>
                                <select name="source_engine" class="form-select" required>
                                    <option value="serpapi_google" @selected(old('source_engine', 'serpapi_google') === 'serpapi_google')>
                                        SERPAPI Google
                                    </option>
                                </select>
                                <div class="form-text">
                                    V1 uses SERPAPI only.
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Max Results Per Query <span class="text-danger">*</span></label>
                                <select name="max_results_per_query" class="form-select" required>
                                    @foreach([3, 5, 10] as $size)
                                        <option value="{{ $size }}" @selected((int) old('max_results_per_query', 3) === $size)>
                                            {{ $size }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    Keep 3 while testing filters.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        Smart Query Builder Inputs
                    </div>

                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Important:</strong> Do not paste static Google searches here. Add business inputs. The system will generate smart queries dynamically.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Service Focus</label>
                                <textarea
                                    name="service_focus"
                                    class="form-control"
                                    rows="5"
                                    placeholder="Webflow&#10;UX Design&#10;Product Design"
                                >{{ old('service_focus') }}</textarea>
                                <div class="form-text">
                                    One per line or comma-separated.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Target Audience</label>
                                <textarea
                                    name="target_audience"
                                    class="form-control"
                                    rows="5"
                                    placeholder="SaaS&#10;B2B&#10;Startups"
                                >{{ old('target_audience') }}</textarea>
                                <div class="form-text">
                                    Who the agency serves.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Regions</label>
                                <textarea
                                    name="regions"
                                    class="form-control"
                                    rows="4"
                                    placeholder="USA&#10;UK&#10;Germany"
                                >{{ old('regions') }}</textarea>
                                <div class="form-text">
                                    Country or broad region.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Cities</label>
                                <textarea
                                    name="cities"
                                    class="form-control"
                                    rows="4"
                                    placeholder="New York&#10;London&#10;Berlin"
                                >{{ old('cities') }}</textarea>
                                <div class="form-text">
                                    Optional. Leave blank for country-level discovery.
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Exclude Terms</label>
                                <textarea
                                    name="exclude_terms"
                                    class="form-control"
                                    rows="3"
                                    placeholder="India&#10;Bangalore&#10;Bengaluru&#10;Mumbai&#10;Delhi&#10;Pune&#10;Hyderabad"
                                >{{ old('exclude_terms', "India\nBangalore\nBengaluru\nMumbai\nDelhi\nPune\nHyderabad") }}</textarea>
                                <div class="form-text">
                                    These are added as negative search signals and also help filtering.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        Exact Queries - Advanced Fallback
                    </div>

                    <div class="card-body">
                        <div class="alert alert-warning">
                            <strong>Use rarely:</strong> Exact mode is only for manual testing. Our main system should use Smart Query Builder.
                        </div>

                        <label class="form-label">Exact Queries</label>
                        <textarea
                            name="exact_queries"
                            class="form-control"
                            rows="5"
                            placeholder='"Webflow agency" "SaaS" "USA" -India -Clutch -Reddit'
                        >{{ old('exact_queries') }}</textarea>

                        <div class="form-text">
                            One exact query per line. Only used when Query Mode is Exact.
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        Notes
                    </div>

                    <div class="card-body">
                        <textarea
                            name="notes"
                            class="form-control"
                            rows="4"
                            placeholder="Why this campaign exists, what type of lead quality we want, or what to avoid."
                        >{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card mb-4">
                    <div class="card-header">
                        Discovery Rules
                    </div>

                    <div class="card-body">
                        <div class="rule-box good">
                            <div class="rule-icon">
                                <i class="bi bi-check2-circle"></i>
                            </div>
                            <div>
                                <strong>Accept</strong>
                                <p>Official agency or company websites, homepages, service pages and relevant business pages.</p>
                            </div>
                        </div>

                        <div class="rule-box bad">
                            <div class="rule-icon">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div>
                                <strong>Reject</strong>
                                <p>Blogs, listicles, Reddit, directories, Instagram, Clutch, templates, job pages and marketplace listings.</p>
                            </div>
                        </div>

                        <div class="rule-box warning">
                            <div class="rule-icon">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div>
                                <strong>Human Review</strong>
                                <p>Discovery only finds candidates. We still crawl, analyze, match proof and review before outreach.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        Good Test Campaign
                    </div>

                    <div class="card-body">
                        <div class="small text-muted mb-2">Use this for first test:</div>

                        <div class="code-box small">
Service Focus:
Webflow

Target Audience:
SaaS

Regions:
USA

Max Results:
3
                        </div>
                    </div>
                </div>

                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Next Step</h5>

                        <p class="text-muted">
                            After creating the campaign, open it and review generated smart queries before running.
                        </p>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save me-1"></i>
                            Save Campaign
                        </button>

                        <a href="{{ route('discovery.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection