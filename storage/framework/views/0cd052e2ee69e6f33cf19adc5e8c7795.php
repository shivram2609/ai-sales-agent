<?php $__env->startSection('title', $draft->subject ?: 'Outreach Draft'); ?>
<?php $__env->startSection('subtitle', 'Review and copy this draft manually. No automatic sending.'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $prospect = $draft->prospect;

        $fullDraft = trim(
            ($draft->subject ? "Subject: {$draft->subject}\n\n" : '') .
            "EMAIL:\n" . ($draft->body ?: '') . "\n\n" .
            "LINKEDIN CONNECTION NOTE:\n" . ($draft->linkedin_connection_note ?: '') . "\n\n" .
            "LINKEDIN FOLLOW-UP:\n" . ($draft->linkedin_followup ?: '') . "\n\n" .
            "FOLLOW-UP 1:\n" . ($draft->follow_up_1 ?: '') . "\n\n" .
            "FOLLOW-UP 2:\n" . ($draft->follow_up_2 ?: '')
        );
    ?>

    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-start gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <?php echo e($draft->subject ?: 'Outreach Draft'); ?>

            </h1>
			
			<?php if($draft->contact): ?>
				<div class="mb-3">
					<div class="small text-muted fw-semibold">Assigned Contact</div>

					<div class="p-3 bg-light border rounded">
						<div class="fw-bold">
							<?php echo e($draft->contact->name ?: 'Unnamed Contact'); ?>


							<?php if($draft->contact->is_primary): ?>
								<span class="badge bg-primary ms-1">Primary</span>
							<?php endif; ?>
						</div>

						<?php if($draft->contact->title): ?>
							<div class="text-muted small"><?php echo e($draft->contact->title); ?></div>
						<?php endif; ?>

						<?php if($draft->contact->email): ?>
							<div class="small">
								<a href="mailto:<?php echo e($draft->contact->email); ?>">
									<?php echo e($draft->contact->email); ?>

								</a>
							</div>
						<?php endif; ?>

						<?php if($draft->contact->linkedin_url): ?>
							<div class="small">
								<a href="<?php echo e($draft->contact->linkedin_url); ?>" target="_blank" rel="noopener">
									LinkedIn
								</a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

            <div class="page-subtitle">
                <?php if($prospect): ?>
                    Draft for
                    <a href="<?php echo e(url('/prospects/' . $prospect->id)); ?>" class="text-decoration-none">
                        <?php echo e($prospect->company_name ?: $prospect->domain); ?>

                    </a>
                <?php else: ?>
                    Draft is not linked with a prospect.
                <?php endif; ?>
            </div>
        </div>

        <div class="draft-action-bar">
            <a href="<?php echo e(route('outreach-drafts.index')); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Drafts
            </a>

            <?php if($prospect): ?>
                <a href="<?php echo e(url('/prospects/' . $prospect->id)); ?>" class="btn btn-outline-primary">
                    <i class="bi bi-building me-1"></i>
                    Open Prospect
                </a>
            <?php endif; ?>
			
			<a href="<?php echo e(route('outreach-drafts.edit', $draft)); ?>" class="btn btn-outline-secondary">
				<i class="bi bi-pencil-square me-1"></i>
				Edit Draft
			</a>
			

            <form method="post" action="<?php echo e(route('outreach-drafts.improve-ai', $draft)); ?>">
				<?php echo csrf_field(); ?>

				<?php
					$hasAiFeedback = ! empty($draft->ai_quality_notes);
				?>

				<button
					class="btn btn-outline-primary"
					onclick="return confirm('<?php echo e($hasAiFeedback ? 'Rewrite this draft using AI quality feedback? Current draft text will be updated.' : 'Improve this draft with AI? Current draft text will be updated.'); ?>')"
				>
					<i class="bi bi-stars me-1"></i>
					<?php echo e($hasAiFeedback ? 'Rewrite using AI Feedback' : 'Improve with AI'); ?>

				</button>
			</form>

            <form method="post" action="<?php echo e(route('outreach-drafts.quality-check-ai', $draft)); ?>">
                <?php echo csrf_field(); ?>
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-shield-check me-1"></i>
                    AI Quality Check
                </button>
            </form>

            <button
                type="button"
                class="btn btn-primary js-copy-text"
                data-copy-target="#copy-full-draft-<?php echo e($draft->id); ?>"
            >
                <i class="bi bi-clipboard me-1"></i>
                Copy Full Draft
            </button>
        </div>
    </div>

    <textarea id="copy-full-draft-<?php echo e($draft->id); ?>" class="copy-source" readonly><?php echo e($fullDraft); ?></textarea>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Email Draft</span>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary js-copy-text"
                        data-copy-target="#copy-email-draft-<?php echo e($draft->id); ?>"
                    >
                        <i class="bi bi-clipboard me-1"></i>
                        Copy Email
                    </button>
                </div>

                <div class="card-body">
                    <textarea id="copy-email-draft-<?php echo e($draft->id); ?>" class="copy-source" readonly><?php if($draft->subject): ?>
Subject: <?php echo e($draft->subject); ?>


<?php endif; ?><?php echo e($draft->body); ?></textarea>

                    <?php if($draft->subject): ?>
                        <div class="mb-3">
                            <div class="detail-label">Subject</div>
                            <div class="draft-subject-preview">
                                <?php echo e($draft->subject); ?>

                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="detail-label mb-2">Body</div>

                    <div class="draft-box">
                        <?php echo nl2br(e($draft->body ?: 'No email body available.')); ?>

                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>LinkedIn Connection Note</span>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary js-copy-text"
                        data-copy-target="#copy-linkedin-note-<?php echo e($draft->id); ?>"
                    >
                        <i class="bi bi-clipboard me-1"></i>
                        Copy
                    </button>
                </div>

                <div class="card-body">
                    <textarea id="copy-linkedin-note-<?php echo e($draft->id); ?>" class="copy-source" readonly><?php echo e($draft->linkedin_connection_note); ?></textarea>

                    <div class="draft-box light-preview">
                        <?php echo nl2br(e($draft->linkedin_connection_note ?: 'No LinkedIn connection note generated.')); ?>

                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>LinkedIn Follow-up</span>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary js-copy-text"
                        data-copy-target="#copy-linkedin-followup-<?php echo e($draft->id); ?>"
                    >
                        <i class="bi bi-clipboard me-1"></i>
                        Copy
                    </button>
                </div>

                <div class="card-body">
                    <textarea id="copy-linkedin-followup-<?php echo e($draft->id); ?>" class="copy-source" readonly><?php echo e($draft->linkedin_followup); ?></textarea>

                    <div class="draft-box light-preview">
                        <?php echo nl2br(e($draft->linkedin_followup ?: 'No LinkedIn follow-up generated.')); ?>

                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Email Follow-up 1</span>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary js-copy-text"
                        data-copy-target="#copy-follow-up-1-<?php echo e($draft->id); ?>"
                    >
                        <i class="bi bi-clipboard me-1"></i>
                        Copy
                    </button>
                </div>

                <div class="card-body">
                    <textarea id="copy-follow-up-1-<?php echo e($draft->id); ?>" class="copy-source" readonly><?php echo e($draft->follow_up_1); ?></textarea>

                    <div class="draft-box light-preview">
                        <?php echo nl2br(e($draft->follow_up_1 ?: 'No follow-up 1 generated.')); ?>

                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Email Follow-up 2</span>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary js-copy-text"
                        data-copy-target="#copy-follow-up-2-<?php echo e($draft->id); ?>"
                    >
                        <i class="bi bi-clipboard me-1"></i>
                        Copy
                    </button>
                </div>

                <div class="card-body">
                    <textarea id="copy-follow-up-2-<?php echo e($draft->id); ?>" class="copy-source" readonly><?php echo e($draft->follow_up_2); ?></textarea>

                    <div class="draft-box light-preview">
                        <?php echo nl2br(e($draft->follow_up_2 ?: 'No follow-up 2 generated.')); ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    Draft Summary
                </div>

                <div class="card-body">
                    <div class="detail-list">
                        <div>
                            <span>Status</span>
                            <strong><?php echo e(str_replace('_', ' ', $draft->status ?: 'draft')); ?></strong>
                        </div>

                        <div>
                            <span>Created</span>
                            <strong><?php echo e($draft->created_at?->format('M d, Y h:i A')); ?></strong>
                        </div>

                        <div>
                            <span>Updated</span>
                            <strong><?php echo e($draft->updated_at?->format('M d, Y h:i A')); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    AI Review
                </div>

                <div class="card-body">
                    <?php if($draft->ai_quality_score !== null): ?>
                        <div class="ai-score-grid mb-3">
                            <div>
                                <span>Overall</span>
                                <strong><?php echo e($draft->ai_quality_score); ?>/100</strong>
                            </div>

                            <div>
                                <span>Personalization</span>
                                <strong><?php echo e($draft->ai_personalization_score ?? '—'); ?>/100</strong>
                            </div>

                            <div>
                                <span>Relevance</span>
                                <strong><?php echo e($draft->ai_relevance_score ?? '—'); ?>/100</strong>
                            </div>

                            <div>
                                <span>Proof</span>
                                <strong><?php echo e($draft->ai_proof_score ?? '—'); ?>/100</strong>
                            </div>

                            <div>
                                <span>Spam Risk</span>
                                <strong><?php echo e($draft->ai_spam_risk_score ?? '—'); ?>/100</strong>
                            </div>
                        </div>

                        <?php if($draft->ai_quality_notes): ?>
                            <div class="detail-label mb-2">Quality Notes</div>
                            <div class="ai-notes-box">
                                <?php echo nl2br(e($draft->ai_quality_notes)); ?>

                            </div>
                        <?php endif; ?>

                        <?php if($draft->ai_improvement_notes): ?>
                            <div class="detail-label mb-2 mt-3">Improvement Notes</div>
                            <div class="ai-notes-box">
                                <?php echo nl2br(e($draft->ai_improvement_notes)); ?>

                            </div>
                        <?php endif; ?>

                        <div class="small text-muted mt-3">
                            Model: <?php echo e($draft->ai_model_used ?: '—'); ?><br>
                            Checked: <?php echo e($draft->ai_last_checked_at?->diffForHumans() ?: '—'); ?>

                        </div>
                    <?php else: ?>
                        <div class="text-muted">
                            No AI review yet. Run Improve with AI or AI Quality Check.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($prospect): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        Prospect Context
                    </div>

                    <div class="card-body">
                        <div class="detail-list mb-3">
                            <div>
                                <span>Company</span>
                                <strong><?php echo e($prospect->company_name ?: 'Unknown'); ?></strong>
                            </div>

                            <div>
                                <span>Domain</span>
                                <strong><?php echo e($prospect->domain ?: '—'); ?></strong>
                            </div>

                            <div>
                                <span>Status</span>
                                <strong><?php echo e(str_replace('_', ' ', $prospect->status ?: 'new')); ?></strong>
                            </div>

                            <div>
                                <span>Fit Score</span>
                                <strong><?php echo e($prospect->fit_score ?? '—'); ?></strong>
                            </div>
                        </div>

                        <a href="<?php echo e(url('/prospects/' . $prospect->id)); ?>" class="btn btn-outline-primary w-100">
                            Open Prospect
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    Sending Rule
                </div>

                <div class="card-body">
                    <div class="rule-box warning">
                        <div class="rule-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>

                        <div>
                            <strong>Manual approval required</strong>
                            <p>
                                Review personalization, proof links, tone and relevance before sending. This system should prepare drafts, not send spam.
                            </p>
                        </div>
                    </div>

                    <div class="rule-box good">
                        <div class="rule-icon">
                            <i class="bi bi-check2-circle"></i>
                        </div>

                        <div>
                            <strong>Best use</strong>
                            <p>
                                Copy email manually, lightly personalize first line, then send from the right inbox.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\ai-sales-agent\resources\views/outreach-drafts/show.blade.php ENDPATH**/ ?>