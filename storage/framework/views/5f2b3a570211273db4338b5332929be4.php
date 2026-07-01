<?php $__env->startSection('title', $prospect->company_name ?: $prospect->domain); ?>
<?php $__env->startSection('subtitle', 'Prospect pipeline detail, actions, analysis, drafts and logs.'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $latestAnalysis = $prospect->analyses->first();
        $latestDraft = $prospect->drafts->first();
    ?>

    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
        <div>
            <h1 class="page-title"><?php echo e($prospect->company_name ?: $prospect->domain); ?></h1>

            <div class="page-subtitle">
                <a href="<?php echo e($prospect->website_url); ?>" target="_blank" class="text-decoration-none">
                    <?php echo e($prospect->website_url); ?>

                    <i class="bi bi-box-arrow-up-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="page-actions">
            <a href="<?php echo e(route('prospects.index')); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back
            </a>

            <a href="<?php echo e($prospect->website_url); ?>" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-globe me-1"></i>
                Visit Site
            </a>
        </div>
    </div>
	<?php echo $__env->make('prospects.partials.contacts-card', ['prospect' => $prospect], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Status</div>
                    <div class="mt-2">
                        <span class="status-badge <?php echo e($prospect->status); ?>">
                            <?php echo e(str_replace('_', ' ', $prospect->status)); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Fit Score</div>
                    <div class="stat-value"><?php echo e($prospect->fit_score ?? '—'); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Category</div>
                    <div class="fw-bold mt-2"><?php echo e($prospect->category_guess ?: '—'); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Region</div>
                    <div class="fw-bold mt-2"><?php echo e($prospect->country_guess ?: '—'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <span>Pipeline Actions</span>
            <span class="small text-muted">Run in order from left to right</span>
        </div>

        <div class="card-body">
            <div class="pipeline-actions">
                <form method="post" action="<?php echo e(route('prospects.crawl', $prospect)); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-download me-1"></i>
                        Crawl Homepage
                    </button>
                </form>

                <form method="post" action="<?php echo e(route('prospects.analyze', $prospect)); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-graph-up me-1"></i>
                        Analyze
                    </button>
                </form>

                <form method="post" action="<?php echo e(route('prospects.proof-match', $prospect)); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-link-45deg me-1"></i>
                        Match Proof
                    </button>
                </form>

                <form method="post" action="<?php echo e(route('prospects.generate-draft', $prospect)); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-envelope-plus me-1"></i>
                        Generate Draft
                    </button>
                </form>

                <form method="post" action="<?php echo e(route('prospects.quality-latest-draft', $prospect)); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-primary">
                        <i class="bi bi-shield-check me-1"></i>
                        Quality Check Latest Draft
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card mb-4">
                <div class="card-header">
                    Latest Analysis
                </div>

                <div class="card-body">
                    <?php if($latestAnalysis): ?>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="detail-label">Company Type</div>
                                <div class="detail-value"><?php echo e($latestAnalysis->company_type ?: '—'); ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="detail-label">Recommended Action</div>
                                <div class="detail-value">
                                    <span class="status-badge <?php echo e($latestAnalysis->recommended_action); ?>">
                                        <?php echo e(str_replace('_', ' ', $latestAnalysis->recommended_action ?: '—')); ?>

                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="detail-label">Final Score</div>
                                <div class="detail-value fw-bold"><?php echo e($latestAnalysis->final_score ?? '—'); ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="detail-label">Technical Depth</div>
                                <div class="detail-value"><?php echo e($latestAnalysis->visible_technical_depth ?: '—'); ?></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="detail-label">Likely Gap</div>
                            <div class="detail-value"><?php echo e($latestAnalysis->likely_gap ?: '—'); ?></div>
                        </div>

                        <div class="mb-3">
                            <div class="detail-label">Partnership Angle</div>
                            <div class="detail-value"><?php echo e($latestAnalysis->partnership_angle ?: '—'); ?></div>
                        </div>

                        <div>
                            <div class="detail-label">Reasoning Summary</div>
                            <div class="detail-value"><?php echo e($latestAnalysis->reasoning_summary ?: '—'); ?></div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <h5 class="fw-bold mb-1">No analysis yet</h5>
                            <p class="text-muted mb-3">Crawl the homepage first, then run analyzer.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Drafts
                </div>

                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $prospect->drafts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $draft): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="draft-box">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
                                <div>
                                    <div class="fw-bold"><?php echo e($draft->subject ?: 'No subject'); ?></div>
                                    <div class="small text-muted">
                                        <?php echo e($draft->created_at?->diffForHumans()); ?>

                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <span class="status-badge <?php echo e($draft->status); ?>">
                                        <?php echo e(str_replace('_', ' ', $draft->status)); ?>

                                    </span>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary js-copy-text"
                                        data-copy-target="#draft-copy-<?php echo e($draft->id); ?>"
                                    >
                                        <i class="bi bi-clipboard me-1"></i>
                                        Copy Draft
                                    </button>

                                    <a href="<?php echo e(route('outreach-drafts.show', $draft)); ?>" class="btn btn-sm btn-outline-primary">
                                        Open Draft
                                    </a>
                                </div>
                            </div>

                            <?php if($draft->ai_quality_score !== null): ?>
								<div class="small text-muted mb-2">
									AI Quality:
									<span class="status-badge <?php echo e($draft->ai_quality_score >= 70 ? 'active' : 'manual_rejected'); ?>">
										<?php echo e($draft->ai_quality_score); ?>/100
									</span>
								</div>

								<?php if($draft->ai_personalization_score !== null): ?>
									<div class="small text-muted mb-2">
										Personalization:
										<span class="status-badge <?php echo e($draft->ai_personalization_score >= 60 ? 'active' : 'manual_rejected'); ?>">
											<?php echo e($draft->ai_personalization_score); ?>/100
										</span>
									</div>
								<?php endif; ?>

								<?php if($draft->ai_relevance_score !== null): ?>
									<div class="small text-muted mb-2">
										Relevance:
										<span class="status-badge <?php echo e($draft->ai_relevance_score >= 60 ? 'active' : 'manual_rejected'); ?>">
											<?php echo e($draft->ai_relevance_score); ?>/100
										</span>
									</div>
								<?php endif; ?>

								<?php if($draft->ai_spam_risk_score !== null): ?>
									<div class="small text-muted mb-2">
										Spam Risk:
										<span class="status-badge <?php echo e($draft->ai_spam_risk_score <= 40 ? 'active' : 'manual_rejected'); ?>">
											<?php echo e($draft->ai_spam_risk_score); ?>/100
										</span>
									</div>
								<?php endif; ?>
							<?php endif; ?>

                            <textarea id="draft-copy-<?php echo e($draft->id); ?>" class="copy-source" readonly>
								<?php if($draft->subject): ?>
								Subject: <?php echo e($draft->subject); ?>


								<?php endif; ?>
								<?php echo e($draft->body); ?>

							</textarea>

                            <pre class="draft-preview"><?php echo e($draft->body); ?></pre>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bi bi-envelope-paper"></i>
                            </div>

                            <h5 class="fw-bold mb-1">No drafts yet</h5>

                            <p class="text-muted mb-3">
                                Generate a draft after analysis and proof matching.
                            </p>

                            <form method="post" action="<?php echo e(route('prospects.generate-draft', $prospect)); ?>">
                                <?php echo csrf_field(); ?>
                                <button class="btn btn-primary">Generate Draft</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card mb-4">
                <div class="card-header">
                    Prospect Info
                </div>

                <div class="card-body">
                    <div class="detail-list">
                        <div>
                            <span>Domain</span>
                            <strong><?php echo e($prospect->domain); ?></strong>
                        </div>

                        <div>
                            <span>Source</span>
                            <strong><?php echo e($prospect->source ?: '—'); ?></strong>
                        </div>

                        <div>
                            <span>Created</span>
                            <strong><?php echo e($prospect->created_at?->format('M d, Y h:i A')); ?></strong>
                        </div>

                        <div>
                            <span>Updated</span>
                            <strong><?php echo e($prospect->updated_at?->format('M d, Y h:i A')); ?></strong>
                        </div>
                    </div>

                    <?php if($prospect->notes): ?>
                        <hr>
                        <div class="detail-label mb-1">Notes</div>
                        <div class="text-muted"><?php echo e($prospect->notes); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Crawled Pages
                </div>

                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $prospect->pages->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="mini-record">
                            <div class="fw-bold"><?php echo e($page->title ?: 'Untitled page'); ?></div>
                            <div class="small text-muted">
                                <?php echo e($page->page_type); ?> · <?php echo e($page->created_at?->diffForHumans()); ?>

                            </div>
                            <div class="small text-muted">
                                Text length: <?php echo e(strlen($page->main_text ?? '')); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-muted">No crawled pages yet.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Review Actions
                </div>

                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $prospect->reviewActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="mini-record">
                            <div>
                                <span class="status-badge <?php echo e($action->action); ?>">
                                    <?php echo e(str_replace('_', ' ', $action->action)); ?>

                                </span>
                            </div>
                            <div class="small text-muted mt-1">
                                <?php echo e($action->notes ?: 'No notes'); ?>

                            </div>
                            <div class="small text-muted">
                                <?php echo e($action->created_at?->diffForHumans()); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-muted">No review actions yet.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    Recent Agent Logs
                </div>

                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $prospect->agentLogs->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="mini-record">
                            <div class="fw-bold"><?php echo e($log->step_name); ?></div>
                            <div class="small text-muted"><?php echo e($log->created_at?->diffForHumans()); ?></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-muted">No logs yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\ai-sales-agent\resources\views/prospects/show.blade.php ENDPATH**/ ?>