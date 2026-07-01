@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Edit Outreach Draft</h1>

            <div class="text-muted">
                @if($draft->prospect)
                    {{ $draft->prospect->company_name ?? $draft->prospect->domain }}
                @else
                    Draft #{{ $draft->id }}
                @endif
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('outreach-drafts.show', $draft) }}" class="btn btn-outline-secondary">
                Back to Draft
            </a>

            @if($draft->prospect)
                <a href="{{ route('prospects.show', $draft->prospect) }}" class="btn btn-outline-secondary">
                    Open Prospect
                </a>
            @endif
        </div>
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

    <form method="post" action="{{ route('outreach-drafts.update', $draft) }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h2 class="h5 fw-bold mb-0">Email Draft</h2>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label for="subject" class="form-label fw-semibold">Subject</label>
                            <input
                                type="text"
                                name="subject"
                                id="subject"
                                class="form-control"
                                value="{{ old('subject', $draft->subject) }}"
                                maxlength="255"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="preheader" class="form-label fw-semibold">Preheader</label>
                            <input
                                type="text"
                                name="preheader"
                                id="preheader"
                                class="form-control"
                                value="{{ old('preheader', $draft->preheader) }}"
                                maxlength="255"
                                placeholder="Short preview text shown after subject in email inbox"
                            >
                            <div class="form-text">
                                Optional. Useful later when sending via Brevo or another email tool.
                            </div>
                        </div>
						
						@if($contacts->count())
							<div class="mb-3">
								<label for="prospect_contact_id" class="form-label fw-semibold">
									Contact
								</label>

								<select name="prospect_contact_id" id="prospect_contact_id" class="form-select">
									<option value="">No specific contact</option>

									@foreach($contacts as $contact)
										<option
											value="{{ $contact->id }}"
											@selected((string) old('prospect_contact_id', $draft->prospect_contact_id) === (string) $contact->id)
										>
											{{ $contact->name ?: 'Unnamed Contact' }}
											@if($contact->title)
												- {{ $contact->title }}
											@endif
											@if($contact->email)
												- {{ $contact->email }}
											@endif
										</option>
									@endforeach
								</select>

								<div class="form-text">
									This helps AI personalize the draft for the right person.
								</div>
							</div>
						@endif
						
                        <div class="mb-3">
                            <label for="body" class="form-label fw-semibold">Email Body</label>
                            <textarea
                                name="body"
                                id="body"
                                class="form-control"
                                rows="14"
                                required
                            >{{ old('body', $draft->body) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-white">
                        <h2 class="h5 fw-bold mb-0">LinkedIn and Follow-ups</h2>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label for="linkedin_connection_note" class="form-label fw-semibold">
                                LinkedIn Connection Note
                            </label>
                            <textarea
                                name="linkedin_connection_note"
                                id="linkedin_connection_note"
                                class="form-control"
                                rows="3"
                                maxlength="500"
                            >{{ old('linkedin_connection_note', $draft->linkedin_connection_note) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="linkedin_followup" class="form-label fw-semibold">
                                LinkedIn Follow-up
                            </label>
                            <textarea
                                name="linkedin_followup"
                                id="linkedin_followup"
                                class="form-control"
                                rows="5"
                            >{{ old('linkedin_followup', $draft->linkedin_followup) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="follow_up_1" class="form-label fw-semibold">
                                Email Follow-up 1
                            </label>
                            <textarea
                                name="follow_up_1"
                                id="follow_up_1"
                                class="form-control"
                                rows="6"
                            >{{ old('follow_up_1', $draft->follow_up_1) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="follow_up_2" class="form-label fw-semibold">
                                Email Follow-up 2
                            </label>
                            <textarea
                                name="follow_up_2"
                                id="follow_up_2"
                                class="form-control"
                                rows="6"
                            >{{ old('follow_up_2', $draft->follow_up_2) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                    <div class="card-header bg-white">
                        <h2 class="h5 fw-bold mb-0">Save Changes</h2>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-warning small">
                            Manual editing will clear the old AI quality score because the previous review may no longer match this draft.
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted">Current Status</div>
                            <div class="fw-semibold">{{ $draft->status ?? 'draft' }}</div>
                        </div>

                        @if($draft->ai_quality_score !== null)
                            <div class="mb-3">
                                <div class="small text-muted">Previous AI Quality</div>
                                <div class="fw-semibold">{{ $draft->ai_quality_score }}/100</div>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary w-100">
                            Save Manual Draft
                        </button>

                        <a href="{{ route('outreach-drafts.show', $draft) }}" class="btn btn-outline-secondary w-100 mt-2">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection