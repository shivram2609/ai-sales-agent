@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Edit Prospect Contact</h1>

            <div class="text-muted">
                @if($contact->prospect)
                    {{ $contact->prospect->company_name ?? $contact->prospect->domain }}
                @else
                    Contact #{{ $contact->id }}
                @endif
            </div>
        </div>

        <a href="{{ route('prospects.show', $contact->prospect_id) }}" class="btn btn-outline-secondary">
            Back to Prospect
        </a>
    </div>

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

    <form method="post" action="{{ route('prospect-contacts.update', $contact) }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h2 class="h5 fw-bold mb-0">Contact Details</h2>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $contact->name) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $contact->email) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Title</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $contact->title) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">LinkedIn URL</label>
                                <input type="url" name="linkedin_url" class="form-control" value="{{ old('linkedin_url', $contact->linkedin_url) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $contact->phone) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Source</label>
                                <select name="source" class="form-select">
                                    @foreach($sources as $value => $label)
                                        <option value="{{ $value }}" @selected(old('source', $contact->source) === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $contact->status) === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea name="notes" class="form-control" rows="4">{{ old('notes', $contact->notes) }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input
                                        type="checkbox"
                                        name="is_primary"
                                        value="1"
                                        class="form-check-input"
                                        id="is_primary"
                                        @checked(old('is_primary', $contact->is_primary))
                                    >

                                    <label for="is_primary" class="form-check-label">
                                        Mark as primary contact
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                    <div class="card-header bg-white">
                        <h2 class="h5 fw-bold mb-0">Save Contact</h2>
                    </div>

                    <div class="card-body">
                        <div class="small text-muted mb-3">
                            Keep this clean. Campaigns and email tracking will use these contacts later.
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Save Contact
                        </button>

                        <a href="{{ route('prospects.show', $contact->prospect_id) }}" class="btn btn-outline-secondary w-100 mt-2">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection