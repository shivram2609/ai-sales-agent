@extends('layouts.app')

@section('title', 'Add Prospect')
@section('subtitle', 'Add a company or agency website to start the outreach pipeline.')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Add Prospect</h1>
            <div class="page-subtitle">
                Add a website URL. The system will normalize the domain and prepare it for crawl and analysis.
            </div>
        </div>

        <div class="page-actions">
            <a href="{{ route('prospects.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Prospects
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    Prospect Details
                </div>

                <div class="card-body">
                    <form method="post" action="{{ route('prospects.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Website URL <span class="text-danger">*</span></label>
                            <input
                                type="url"
                                name="website_url"
                                class="form-control"
                                value="{{ old('website_url') }}"
                                placeholder="https://www.example.com"
                                required
                            >
                            <div class="form-text">
                                Use the official company/agency website, not LinkedIn, Clutch, Instagram, or a directory page.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input
                                type="text"
                                name="company_name"
                                class="form-control"
                                value="{{ old('company_name') }}"
                                placeholder="Example Agency"
                            >
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Category Guess</label>
                                <input
                                    type="text"
                                    name="category_guess"
                                    class="form-control"
                                    value="{{ old('category_guess') }}"
                                    placeholder="Webflow agency, UI/UX agency, Shopify agency"
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Country / Region Guess</label>
                                <input
                                    type="text"
                                    name="country_guess"
                                    class="form-control"
                                    value="{{ old('country_guess') }}"
                                    placeholder="USA, UK, Germany, Canada"
                                >
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">Source</label>
                                <input
                                    type="text"
                                    name="source"
                                    class="form-control"
                                    value="{{ old('source', 'manual') }}"
                                    placeholder="manual, discovery, referral"
                                >
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Notes</label>
                            <textarea
                                name="notes"
                                class="form-control"
                                rows="4"
                                placeholder="Why this prospect looks relevant, where it came from, or anything the team should know."
                            >{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Save Prospect
                            </button>

                            <a href="{{ route('prospects.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">What happens next?</h5>

                    <div class="mini-step">
                        <span>1</span>
                        <div>
                            <strong>Crawl homepage</strong>
                            <p>Extracts title, headings, text, links, and emails.</p>
                        </div>
                    </div>

                    <div class="mini-step">
                        <span>2</span>
                        <div>
                            <strong>Analyze fit</strong>
                            <p>Checks company type, services, target audience, and likely gap.</p>
                        </div>
                    </div>

                    <div class="mini-step">
                        <span>3</span>
                        <div>
                            <strong>Generate draft</strong>
                            <p>Creates email, LinkedIn note, and follow-ups for human review.</p>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3 mb-0">
                        <strong>Rule:</strong> Only official websites should be added as prospects.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection