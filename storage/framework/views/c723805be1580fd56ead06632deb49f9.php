

<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('subtitle', 'AI Sales Agent operating overview for discovery, prospects, drafts and outreach readiness.'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <div class="page-subtitle">
                Track discovery quality, prospect pipeline, outreach drafts and knowledge assets from one place.
            </div>
        </div>

        <div class="page-actions">
            <a href="<?php echo e(route('discovery.create')); ?>" class="btn btn-primary">
                <i class="bi bi-search me-1"></i>
                New Discovery
            </a>

            <a href="<?php echo e(route('prospects.create')); ?>" class="btn btn-outline-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Add Prospect
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Total Prospects</div>
                    <div class="stat-value"><?php echo e($stats['prospects_total']); ?></div>
                    <div class="small text-muted">
                        <?php echo e($stats['prospects_approved']); ?> approved
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Discovery Candidates</div>
                    <div class="stat-value"><?php echo e($stats['discovery_results_total']); ?></div>
                    <div class="small text-muted">
                        <?php echo e($stats['discovery_results_converted']); ?> converted
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Outreach Drafts</div>
                    <div class="stat-value"><?php echo e($stats['drafts_total']); ?></div>
                    <div class="small text-muted">
                        <?php echo e($stats['drafts_quality_checked']); ?> quality checked
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Knowledge Assets</div>
                    <div class="stat-value"><?php echo e($stats['knowledge_assets_total']); ?></div>
                    <div class="small text-muted">
                        Proof links and case studies
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card mini-metric-card h-100">
                <div class="card-body">
                    <div class="mini-metric-label">Strong Leads</div>
                    <div class="mini-metric-value"><?php echo e($stats['discovery_results_strong']); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card mini-metric-card h-100">
                <div class="card-body">
                    <div class="mini-metric-label">Possible Leads</div>
                    <div class="mini-metric-value"><?php echo e($stats['discovery_results_possible']); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card mini-metric-card h-100">
                <div class="card-body">
                    <div class="mini-metric-label">Manual Rejects</div>
                    <div class="mini-metric-value"><?php echo e($stats['discovery_results_manual_rejected']); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card mini-metric-card h-100">
                <div class="card-body">
                    <div class="mini-metric-label">Running Campaigns</div>
                    <div class="mini-metric-value"><?php echo e($stats['discovery_campaigns_running']); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Recent Prospects</span>
                    <a href="<?php echo e(route('prospects.index')); ?>" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Company</th>
                                    <th>Status</th>
                                    <th>Fit</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $recentProspects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prospect): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold">
                                                <?php echo e($prospect->company_name ?: 'Unknown Company'); ?>

                                            </div>
                                            <div class="small text-muted">
                                                <?php echo e($prospect->domain); ?>

                                            </div>
                                        </td>

                                        <td>
                                            <span class="status-badge <?php echo e($prospect->status); ?>">
                                                <?php echo e(str_replace('_', ' ', $prospect->status)); ?>

                                            </span>
                                        </td>

                                        <td>
                                            <span class="fit-score">
                                                <?php echo e($prospect->fit_score ?? '—'); ?>

                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <a href="<?php echo e(route('prospects.show', $prospect)); ?>" class="btn btn-sm btn-outline-primary">
                                                Open
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4">
                                            <div class="empty-state">
                                                <div class="empty-state-icon">
                                                    <i class="bi bi-building"></i>
                                                </div>
                                                <h5 class="fw-bold mb-1">No prospects yet</h5>
                                                <p class="text-muted mb-0">
                                                    Add or convert discovery leads to start the pipeline.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Strong Discovery Candidates</span>
                    <a href="<?php echo e(route('discovery.index')); ?>" class="btn btn-sm btn-outline-primary">
                        Campaigns
                    </a>
                </div>

                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $strongDiscoveryResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="mini-record">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                                <div>
                                    <div class="fw-bold">
                                        <?php echo e($result->company_name_guess ?: $result->normalized_domain); ?>

                                    </div>

                                    <div class="small text-muted">
                                        <?php echo e($result->normalized_domain); ?> · Score <?php echo e($result->relevance_score ?? 0); ?>

                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="<?php echo e(route('discovery-results.show', $result)); ?>" class="btn btn-sm btn-outline-secondary">
                                        Details
                                    </a>

                                    <form method="post" action="<?php echo e(route('discovery-results.convert', $result)); ?>" class="m-0">
                                        <?php echo csrf_field(); ?>
                                        <button class="btn btn-sm btn-primary">
                                            Convert
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-muted">
                            No strong discovery candidates waiting for conversion.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Recent Discovery Campaigns</span>
                    <a href="<?php echo e(route('discovery.index')); ?>" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>

                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $recentCampaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="mini-record">
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <div class="fw-bold"><?php echo e($campaign->name); ?></div>
                                    <div class="small text-muted">
                                        <?php echo e(str_replace('_', ' ', $campaign->query_mode)); ?>

                                        · <?php echo e($campaign->updated_at?->diffForHumans()); ?>

                                    </div>
                                </div>

                                <div class="text-end">
                                    <div class="mb-2">
                                        <span class="status-badge <?php echo e($campaign->status); ?>">
                                            <?php echo e(str_replace('_', ' ', $campaign->status)); ?>

                                        </span>
                                    </div>

                                    <a href="<?php echo e(route('discovery.show', $campaign)); ?>" class="btn btn-sm btn-outline-primary">
                                        Open
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-muted">
                            No discovery campaigns yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Recent Drafts</span>
                    <a href="<?php echo e(route('outreach-drafts.index')); ?>" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>

                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $recentDrafts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $draft): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="mini-record">
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <div class="fw-bold">
                                        <?php echo e($draft->subject ?: 'No subject'); ?>

                                    </div>

                                    <div class="small text-muted">
                                        <?php if($draft->prospect): ?>
                                            <?php echo e($draft->prospect->company_name ?: $draft->prospect->domain); ?>

                                        <?php else: ?>
                                            No prospect
                                        <?php endif; ?>
                                        · <?php echo e($draft->created_at?->diffForHumans()); ?>

                                    </div>
                                </div>

                                <div class="text-end">
                                    <div class="mb-2">
                                        <span class="status-badge <?php echo e($draft->status); ?>">
                                            <?php echo e(str_replace('_', ' ', $draft->status)); ?>

                                        </span>
                                    </div>

                                    <a href="<?php echo e(route('outreach-drafts.show', $draft)); ?>" class="btn btn-sm btn-outline-primary">
                                        Open
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-muted">
                            No outreach drafts generated yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    Quick Workflow
                </div>

                <div class="card-body">
                    <div class="workflow-step">
                        <span>1</span>
                        <div>
                            <strong>Create discovery campaign</strong>
                            <p>Use Smart Query Builder with a narrow service + audience + region.</p>
                        </div>
                    </div>

                    <div class="workflow-step">
                        <span>2</span>
                        <div>
                            <strong>Review candidates</strong>
                            <p>Convert only official agency/company websites. Reject weak leads.</p>
                        </div>
                    </div>

                    <div class="workflow-step">
                        <span>3</span>
                        <div>
                            <strong>Run prospect pipeline</strong>
                            <p>Crawl, analyze, match proof, generate draft and quality check.</p>
                        </div>
                    </div>

                    <div class="workflow-step">
                        <span>4</span>
                        <div>
                            <strong>Human sends outreach</strong>
                            <p>Copy the final draft manually. No auto-spam.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\ai-sales-agent\resources\views/dashboard/index.blade.php ENDPATH**/ ?>