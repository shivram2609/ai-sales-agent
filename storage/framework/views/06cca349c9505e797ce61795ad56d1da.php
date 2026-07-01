<?php
    $statuses = \App\Models\ProspectContact::statuses();
    $sources = \App\Models\ProspectContact::sources();

    $badgeMap = [
        'active' => 'bg-success',
        'needs_research' => 'bg-warning text-dark',
        'wrong_person' => 'bg-secondary',
        'bounced' => 'bg-danger',
        'do_not_contact' => 'bg-dark',
    ];
?>

<div class="card shadow-sm border-0 mb-4 zm-contacts-card">
    <div class="card-header bg-white d-flex align-items-center justify-content-between gap-3">
        <div>
            <h2 class="h5 fw-bold mb-0">Prospect Contacts</h2>
            <div class="small text-muted">People inside this company we may contact later</div>
        </div>

        <span class="zm-contact-count">
			<?php echo e($prospect->contacts->count()); ?> contact<?php echo e($prospect->contacts->count() === 1 ? '' : 's'); ?>

		</span>
    </div>

    <div class="card-body">
        <?php if($prospect->contacts->count()): ?>
			<div class="zm-contact-list mb-4">
				<?php $__currentLoopData = $prospect->contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<div class="zm-contact-item">
						<div class="zm-contact-main">
							<div class="zm-contact-name-row">
								<div class="zm-contact-name">
									<?php echo e($contact->name ?: 'Unnamed Contact'); ?>

								</div>

								<?php if($contact->is_primary): ?>
									<span class="badge bg-primary">Primary</span>
								<?php endif; ?>

								<span class="badge <?php echo e($badgeMap[$contact->status] ?? 'bg-secondary'); ?>">
									<?php echo e($statuses[$contact->status] ?? $contact->status); ?>

								</span>
							</div>

							<?php if($contact->title): ?>
								<div class="zm-contact-title">
									<?php echo e($contact->title); ?>

								</div>
							<?php endif; ?>

							<div class="zm-contact-meta">
								<?php if($contact->email): ?>
									<a href="mailto:<?php echo e($contact->email); ?>">
										<?php echo e($contact->email); ?>

									</a>
								<?php endif; ?>

								<?php if($contact->phone): ?>
									<span><?php echo e($contact->phone); ?></span>
								<?php endif; ?>

								<?php if($contact->linkedin_url): ?>
									<a href="<?php echo e($contact->linkedin_url); ?>" target="_blank" rel="noopener">
										LinkedIn
									</a>
								<?php endif; ?>
							</div>

							<?php if($contact->notes): ?>
								<div class="zm-contact-notes">
									<?php echo e($contact->notes); ?>

								</div>
							<?php endif; ?>

							<div class="zm-contact-source">
								Source: <?php echo e($sources[$contact->source] ?? $contact->source); ?>

							</div>
						</div>

						<div class="zm-contact-actions">
							<form method="post" action="<?php echo e(route('prospect-contacts.drafts.store', $contact)); ?>">
								<?php echo csrf_field(); ?>
								<button type="submit" class="btn btn-outline-success">
									Create Draft
								</button>
							</form>

							<a href="<?php echo e(route('prospect-contacts.edit', $contact)); ?>" class="btn btn-outline-secondary">
								Edit
							</a>

							<?php if(! $contact->is_primary): ?>
								<form method="post" action="<?php echo e(route('prospect-contacts.make-primary', $contact)); ?>">
									<?php echo csrf_field(); ?>
									<button type="submit" class="btn btn-outline-primary">
										Make Primary
									</button>
								</form>
							<?php endif; ?>

							<form method="post" action="<?php echo e(route('prospect-contacts.destroy', $contact)); ?>">
								<?php echo csrf_field(); ?>
								<?php echo method_field('DELETE'); ?>

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
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
			</div>
		<?php else: ?>
			<div class="alert alert-light border">
				No contacts added yet. Add the first person for this prospect below.
			</div>
		<?php endif; ?>
        

        <form method="post" action="<?php echo e(route('prospects.contacts.store', $prospect)); ?>">
            <?php echo csrf_field(); ?>

            <h3 class="h6 fw-bold mb-3">Add Contact</h3>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo e(old('name')); ?>" placeholder="Sarah Johnson">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>" placeholder="sarah@example.com">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Title</label>
                    <input type="text" name="title" class="form-control" value="<?php echo e(old('title')); ?>" placeholder="Founder, CTO, Operations Lead">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">LinkedIn URL</label>
                    <input type="url" name="linkedin_url" class="form-control" value="<?php echo e(old('linkedin_url')); ?>" placeholder="https://www.linkedin.com/in/...">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo e(old('phone')); ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Source</label>
                    <select name="source" class="form-select">
                        <?php $__currentLoopData = $sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php if(old('source', 'manual') === $value): echo 'selected'; endif; ?>>
                                <?php echo e($label); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php if(old('status', 'active') === $value): echo 'selected'; endif; ?>>
                                <?php echo e($label); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Context, role fit, why this person matters"><?php echo e(old('notes')); ?></textarea>
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
</div><?php /**PATH D:\ai-sales-agent\resources\views/prospects/partials/contacts-card.blade.php ENDPATH**/ ?>