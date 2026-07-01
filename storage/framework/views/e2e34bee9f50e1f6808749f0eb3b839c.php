

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Edit Outreach Draft</h1>

            <div class="text-muted">
                <?php if($draft->prospect): ?>
                    <?php echo e($draft->prospect->company_name ?? $draft->prospect->domain); ?>

                <?php else: ?>
                    Draft #<?php echo e($draft->id); ?>

                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="<?php echo e(route('outreach-drafts.show', $draft)); ?>" class="btn btn-outline-secondary">
                Back to Draft
            </a>

            <?php if($draft->prospect): ?>
                <a href="<?php echo e(route('prospects.show', $draft->prospect)); ?>" class="btn btn-outline-secondary">
                    Open Prospect
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <strong>Please fix these issues:</strong>
            <ul class="mb-0 mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo e(route('outreach-drafts.update', $draft)); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

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
                                value="<?php echo e(old('subject', $draft->subject)); ?>"
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
                                value="<?php echo e(old('preheader', $draft->preheader)); ?>"
                                maxlength="255"
                                placeholder="Short preview text shown after subject in email inbox"
                            >
                            <div class="form-text">
                                Optional. Useful later when sending via Brevo or another email tool.
                            </div>
                        </div>
						
						<?php if($contacts->count()): ?>
							<div class="mb-3">
								<label for="prospect_contact_id" class="form-label fw-semibold">
									Contact
								</label>

								<select name="prospect_contact_id" id="prospect_contact_id" class="form-select">
									<option value="">No specific contact</option>

									<?php $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
										<option
											value="<?php echo e($contact->id); ?>"
											<?php if((string) old('prospect_contact_id', $draft->prospect_contact_id) === (string) $contact->id): echo 'selected'; endif; ?>
										>
											<?php echo e($contact->name ?: 'Unnamed Contact'); ?>

											<?php if($contact->title): ?>
												- <?php echo e($contact->title); ?>

											<?php endif; ?>
											<?php if($contact->email): ?>
												- <?php echo e($contact->email); ?>

											<?php endif; ?>
										</option>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</select>

								<div class="form-text">
									This helps AI personalize the draft for the right person.
								</div>
							</div>
						<?php endif; ?>
						
                        <div class="mb-3">
                            <label for="body" class="form-label fw-semibold">Email Body</label>
                            <textarea
                                name="body"
                                id="body"
                                class="form-control"
                                rows="14"
                                required
                            ><?php echo e(old('body', $draft->body)); ?></textarea>
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
                            ><?php echo e(old('linkedin_connection_note', $draft->linkedin_connection_note)); ?></textarea>
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
                            ><?php echo e(old('linkedin_followup', $draft->linkedin_followup)); ?></textarea>
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
                            ><?php echo e(old('follow_up_1', $draft->follow_up_1)); ?></textarea>
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
                            ><?php echo e(old('follow_up_2', $draft->follow_up_2)); ?></textarea>
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
                            <div class="fw-semibold"><?php echo e($draft->status ?? 'draft'); ?></div>
                        </div>

                        <?php if($draft->ai_quality_score !== null): ?>
                            <div class="mb-3">
                                <div class="small text-muted">Previous AI Quality</div>
                                <div class="fw-semibold"><?php echo e($draft->ai_quality_score); ?>/100</div>
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary w-100">
                            Save Manual Draft
                        </button>

                        <a href="<?php echo e(route('outreach-drafts.show', $draft)); ?>" class="btn btn-outline-secondary w-100 mt-2">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\ai-sales-agent\resources\views/outreach-drafts/edit.blade.php ENDPATH**/ ?>