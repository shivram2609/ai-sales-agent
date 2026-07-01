@php
    $statuses = \App\Models\ProspectContact::statuses();
    $sources = \App\Models\ProspectContact::sources();

    $badgeMap = [
        'active' => 'bg-success',
        'needs_research' => 'bg-warning text-dark',
        'wrong_person' => 'bg-secondary',
        'bounced' => 'bg-danger',
        'do_not_contact' => 'bg-dark',
    ];
@endphp

<div class="card shadow-sm border-0 mb-4 zm-contacts-card">
    <div class="card-header bg-white d-flex align-items-center justify-content-between gap-3">
        <div>
            <h2 class="h5 fw-bold mb-0">Prospect Contacts</h2>
            <div class="small text-muted">People inside this company we may contact later</div>
        </div>

        <span class="zm-contact-count">
			{{ $prospect->contacts->count() }} contact{{ $prospect->contacts->count() === 1 ? '' : 's' }}
		</span>
    </div>

    <div class="card-body">
        @if($prospect->contacts->count())
			<div class="zm-contact-list mb-4">
				@foreach($prospect->contacts as $contact)
					<div class="zm-contact-item">
						<div class="zm-contact-main">
							<div class="zm-contact-name-row">
								<div class="zm-contact-name">
									{{ $contact->name ?: 'Unnamed Contact' }}
								</div>

								@if($contact->is_primary)
									<span class="badge bg-primary">Primary</span>
								@endif

								<span class="badge {{ $badgeMap[$contact->status] ?? 'bg-secondary' }}">
									{{ $statuses[$contact->status] ?? $contact->status }}
								</span>
							</div>

							@if($contact->title)
								<div class="zm-contact-title">
									{{ $contact->title }}
								</div>
							@endif

							<div class="zm-contact-meta">
								@if($contact->email)
									<a href="mailto:{{ $contact->email }}">
										{{ $contact->email }}
									</a>
								@endif

								@if($contact->phone)
									<span>{{ $contact->phone }}</span>
								@endif

								@if($contact->linkedin_url)
									<a href="{{ $contact->linkedin_url }}" target="_blank" rel="noopener">
										LinkedIn
									</a>
								@endif
							</div>

							@if($contact->notes)
								<div class="zm-contact-notes">
									{{ $contact->notes }}
								</div>
							@endif

							<div class="zm-contact-source">
								Source: {{ $sources[$contact->source] ?? $contact->source }}
							</div>
						</div>

						<div class="zm-contact-actions">
							<form method="post" action="{{ route('prospect-contacts.drafts.store', $contact) }}">
								@csrf
								<button type="submit" class="btn btn-outline-success">
									Create Draft
								</button>
							</form>

							<a href="{{ route('prospect-contacts.edit', $contact) }}" class="btn btn-outline-secondary">
								Edit
							</a>

							@if(! $contact->is_primary)
								<form method="post" action="{{ route('prospect-contacts.make-primary', $contact) }}">
									@csrf
									<button type="submit" class="btn btn-outline-primary">
										Make Primary
									</button>
								</form>
							@endif

							<form method="post" action="{{ route('prospect-contacts.destroy', $contact) }}">
								@csrf
								@method('DELETE')

								<button
									type="submit"
									class="btn btn-outline-danger"
									onclick="return confirm('Delete this contact?')"
								>
									Delete
								</button>
							</form>
						</div>
					</div>
				@endforeach
			</div>
		@else
			<div class="alert alert-light border">
				No contacts added yet. Add the first person for this prospect below.
			</div>
		@endif
        

        <form method="post" action="{{ route('prospects.contacts.store', $prospect) }}">
            @csrf

            <h3 class="h6 fw-bold mb-3">Add Contact</h3>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Sarah Johnson">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="sarah@example.com">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Title</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Founder, CTO, Operations Lead">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">LinkedIn URL</label>
                    <input type="url" name="linkedin_url" class="form-control" value="{{ old('linkedin_url') }}" placeholder="https://www.linkedin.com/in/...">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Source</label>
                    <select name="source" class="form-select">
                        @foreach($sources as $value => $label)
                            <option value="{{ $value }}" @selected(old('source', 'manual') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', 'active') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Context, role fit, why this person matters">{{ old('notes') }}</textarea>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="is_primary" value="1" class="form-check-input" id="is_primary">
                        <label for="is_primary" class="form-check-label">
                            Mark as primary contact
                        </label>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        Add Contact
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>