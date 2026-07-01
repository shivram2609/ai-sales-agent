@php
    $assetTypes = [
        'service_page' => 'Service Page',
        'case_study' => 'Case Study',
        'guide' => 'Guide',
        'landing_page' => 'Landing Page',
        'portfolio' => 'Portfolio',
        'tool' => 'Tool',
        'other' => 'Other',
    ];

    $tagsValue = old('tags', implode(', ', $asset->tags_json ?? []));
    $industriesValue = old('industries', implode(', ', $asset->industries_json ?? []));
    $technologiesValue = old('technologies', implode(', ', $asset->technologies_json ?? []));
@endphp

<form method="post" action="{{ $action }}">
    @csrf

    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="mb-3">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="title"
                    value="{{ old('title', $asset->title) }}"
                    class="form-control @error('title') is-invalid @enderror"
                    placeholder="Example: AI Workflow Automation Services"
                    required
                >

                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">URL <span class="text-danger">*</span></label>
                <input
                    type="url"
                    name="url"
                    value="{{ old('url', $asset->url) }}"
                    class="form-control @error('url') is-invalid @enderror"
                    placeholder="https://www.zestminds.com/..."
                    required
                >

                @error('url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Summary</label>
                <textarea
                    name="summary"
                    rows="4"
                    class="form-control @error('summary') is-invalid @enderror"
                    placeholder="Short summary of what this asset proves."
                >{{ old('summary', $asset->summary) }}</textarea>

                @error('summary')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Use When</label>
                <textarea
                    name="use_when"
                    rows="4"
                    class="form-control @error('use_when') is-invalid @enderror"
                    placeholder="Example: Use when prospect is an agency needing backend, automation or development support."
                >{{ old('use_when', $asset->use_when) }}</textarea>

                @error('use_when')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Avoid When</label>
                <textarea
                    name="avoid_when"
                    rows="3"
                    class="form-control @error('avoid_when') is-invalid @enderror"
                    placeholder="Example: Avoid for healthcare-specific outreach unless the proof is relevant."
                >{{ old('avoid_when', $asset->avoid_when) }}</textarea>

                @error('avoid_when')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-0">
                <label class="form-label">Content Snippet</label>
                <textarea
                    name="content_snippet"
                    rows="6"
                    class="form-control @error('content_snippet') is-invalid @enderror"
                    placeholder="Optional key proof points or short copy from the page."
                >{{ old('content_snippet', $asset->content_snippet) }}</textarea>

                @error('content_snippet')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card bg-light border-0 mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Asset Type <span class="text-danger">*</span></label>
                        <select name="asset_type" class="form-select @error('asset_type') is-invalid @enderror" required>
                            @foreach($assetTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('asset_type', $asset->asset_type) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        @error('asset_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <input type="hidden" name="is_active" value="0">

                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            id="is_active"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', $asset->is_active ?? true))
                        >

                        <label class="form-check-label" for="is_active">
                            Active for proof matching
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Tags</label>
                <textarea
                    name="tags"
                    rows="3"
                    class="form-control @error('tags') is-invalid @enderror"
                    placeholder="agency_partner, ai_automation, crm"
                >{{ $tagsValue }}</textarea>

                <div class="form-text">
                    Comma or new-line separated.
                </div>

                @error('tags')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Industries</label>
                <textarea
                    name="industries"
                    rows="3"
                    class="form-control @error('industries') is-invalid @enderror"
                    placeholder="Agency, SaaS, Healthcare"
                >{{ $industriesValue }}</textarea>

                <div class="form-text">
                    Comma or new-line separated.
                </div>

                @error('industries')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Technologies</label>
                <textarea
                    name="technologies"
                    rows="3"
                    class="form-control @error('technologies') is-invalid @enderror"
                    placeholder="Laravel, React, OpenAI, Make"
                >{{ $technologiesValue }}</textarea>

                <div class="form-text">
                    Comma or new-line separated.
                </div>

                @error('technologies')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid gap-2">
                <button class="btn btn-primary">
                    <i class="bi bi-check2-circle me-1"></i>
                    {{ $buttonText }}
                </button>

                <a href="{{ route('knowledge-assets.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>