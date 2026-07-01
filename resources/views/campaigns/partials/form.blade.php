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

<form method="post" action="{{ $action }}">
    @csrf

    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 fw-bold mb-0">Campaign Details</h2>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Campaign Name</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name', $campaign->name) }}"
                            required
                            placeholder="Webflow Agencies - USA"
                        >
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Audience</label>
                            <input
                                type="text"
                                name="audience_label"
                                class="form-control"
                                value="{{ old('audience_label', $campaign->audience_label) }}"
                                placeholder="Webflow agencies, SaaS startups"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Region</label>
                            <input
                                type="text"
                                name="region_label"
                                class="form-control"
                                value="{{ old('region_label', $campaign->region_label) }}"
                                placeholder="USA, UK, Europe"
                            >
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $campaign->description) }}</textarea>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold">Internal Notes</label>
                        <textarea name="notes" class="form-control" rows="4">{{ old('notes', $campaign->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white">
                    <h2 class="h5 fw-bold mb-0">Settings</h2>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $campaign->status ?: 'draft') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button class="btn btn-primary w-100">
                        {{ $buttonText }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>