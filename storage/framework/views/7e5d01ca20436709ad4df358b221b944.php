

<?php $__env->startSection('title', $result->company_name_guess ?: $result->normalized_domain); ?>
<?php $__env->startSection('subtitle', 'Review discovery result details before converting to prospect.'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $isConverted = (bool) $result->converted_to_prospect;
        $isManualRejected = $result->filter_status === 'manual_rejected';
        $canConvert = ! $isConverted && ! $isManualRejected && in_array($result->filter_status, ['strong_candidate', 'possible_candidate'], true);

        $rawData = $result->raw;

        if (is_string($rawData)) {
            $decodedRaw = json_decode($rawData, true);
            $rawData = json_last_error() === JSON_ERROR_NONE ? $decodedRaw : $rawData;
        }
    ?>

    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-start gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <?php echo e($result->company_name_guess ?: $result->normalized_domain); ?>

            </h1>

            <div class="page-subtitle">
                <?php if($result->campaign): ?>
                    <a href="<?php echo e(route('discovery.show', $result->campaign)); ?>" class="text-decoration-none">
                        <?php echo e($result->campaign->name); ?>

                    </a>
                    <span class="mx-1">·</span>
                <?php endif; ?>

                <a href="<?php echo e($result->result_url); ?>" target="_blank" class="text-decoration-none">
                    <?php echo e($result->normalized_domain); ?>

                    <i class="bi bi-box-arrow-up-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="lead-action-bar">
            <?php if($result->campaign): ?>
                <a href="<?php echo e(route('discovery.show', $result->campaign)); ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Campaign
                </a>
            <?php endif; ?>

            <a href="<?php echo e($result->result_url); ?>" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-globe me-1"></i>
                Open Website
            </a>

            <?php if($isConverted && $result->prospect): ?>
                <a href="<?php echo e(route('prospects.show', $result->prospect)); ?>" class="btn btn-primary">
                    <i class="bi bi-building me-1"></i>
                    Open Prospect
                </a>
            <?php elseif($isManualRejected): ?>
                <button class="btn btn-outline-danger" disabled>
                    <i class="bi bi-x-circle me-1"></i>
                    Rejected
                </button>
            <?php else: ?>
                <?php if($canConvert): ?>
                    <form method="post" action="<?php echo e(route('discovery-results.convert', $result)); ?>">
                        <?php echo csrf_field(); ?>
                        <button class="btn btn-primary">
                            <i class="bi bi-arrow-right-circle me-1"></i>
                            Convert to Prospect
                        </button>
                    </form>
                <?php endif; ?>

                <a href="#reject-lead-card" class="btn btn-outline-danger">
                    <i class="bi bi-x-circle me-1"></i>
                    Reject Lead
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Score</div>
                    <div class="stat-value"><?php echo e($result->relevance_score ?? 0); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Rank</div>
                    <div class="stat-value"><?php echo e($result->rank ?: '—'); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Filter Status</div>
                    <div class="mt-2">
                        <span class="status-badge <?php echo e($result->filter_status); ?>">
                            <?php echo e(str_replace('_', ' ', $result->filter_status)); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Converted</div>
                    <div class="mt-2">
                        <?php if($isConverted): ?>
                            <span class="badge text-bg-success">Yes</span>
                        <?php else: ?>
                            <span class="badge text-bg-light border">No</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Source</div>
                    <div class="fw-bold mt-2">
                        <?php echo e($result->source_engine ?: 'serpapi'); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    Result Details
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <div class="detail-label">Result Title</div>
                        <div class="fw-bold fs-5">
                            <a href="<?php echo e($result->result_url); ?>" target="_blank" class="text-decoration-none">
                                <?php echo e($result->result_title ?: 'Untitled result'); ?>

                                <i class="bi bi-box-arrow-up-right ms-1"></i>
                            </a>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="detail-label">URL</div>
                        <a href="<?php echo e($result->result_url); ?>" target="_blank" class="text-decoration-none">
                            <?php echo e($result->result_url); ?>

                        </a>
                    </div>

                    <div class="mb-3">
                        <div class="detail-label">Snippet</div>
                        <div class="lead-snippet">
                            <?php echo e($result->snippet ?: 'No snippet available.'); ?>

                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="detail-label">Reason</div>
                        <div class="lead-reason">
                            <?php echo e($result->reason ?: 'No filtering reason available.'); ?>

                        </div>
                    </div>

                    <div>
                        <div class="detail-label">Source Query</div>
                        <pre class="query-preview"><?php echo e($result->source_query ?: 'No source query stored.'); ?></pre>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    Raw SERPAPI Data
                </div>

                <div class="card-body">
                    <?php if($rawData): ?>
                        <?php if(is_array($rawData)): ?>
                            <pre class="raw-json-preview"><?php echo e(json_encode($rawData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?></pre>
                        <?php else: ?>
                            <pre class="raw-json-preview"><?php echo e($rawData); ?></pre>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-muted">No raw data stored.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    Lead Summary
                </div>

                <div class="card-body">
                    <div class="detail-list">
                        <div>
                            <span>Domain</span>
                            <strong><?php echo e($result->normalized_domain ?: '—'); ?></strong>
                        </div>

                        <div>
                            <span>Company Guess</span>
                            <strong><?php echo e($result->company_name_guess ?: '—'); ?></strong>
                        </div>

                        <div>
                            <span>Category Guess</span>
                            <strong><?php echo e($result->category_guess ?: '—'); ?></strong>
                        </div>

                        <div>
                            <span>Region Guess</span>
                            <strong><?php echo e($result->region_guess ?: '—'); ?></strong>
                        </div>

                        <div>
                            <span>Created</span>
                            <strong><?php echo e($result->created_at?->format('M d, Y h:i A')); ?></strong>
                        </div>

                        <div>
                            <span>Updated</span>
                            <strong><?php echo e($result->updated_at?->format('M d, Y h:i A')); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Decision Helper
                </div>

                <div class="card-body">
                    <?php if($isManualRejected): ?>
                        <div class="rule-box bad">
                            <div class="rule-icon">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div>
                                <strong>Rejected</strong>
                                <p>This result was manually rejected and should not be converted unless reviewed again.</p>
                            </div>
                        </div>
                    <?php elseif($canConvert): ?>
                        <div class="rule-box good">
                            <div class="rule-icon">
                                <i class="bi bi-check2-circle"></i>
                            </div>
                            <div>
                                <strong>Can Convert</strong>
                                <p>This result passed the current discovery filter. Still verify the website before outreach.</p>
                            </div>
                        </div>
                    <?php elseif($isConverted): ?>
                        <div class="rule-box good">
                            <div class="rule-icon">
                                <i class="bi bi-building-check"></i>
                            </div>
                            <div>
                                <strong>Already Converted</strong>
                                <p>This discovery result has already been converted into a prospect.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="rule-box bad">
                            <div class="rule-icon">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div>
                                <strong>Rejected by Filter</strong>
                                <p>This result should not be converted unless the filter logic is wrong.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="rule-box warning">
                        <div class="rule-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div>
                            <strong>Check Manually</strong>
                            <p>Open the website and confirm it is an official agency/company website, not a template, list, directory, or social page.</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if(! $isConverted && ! $isManualRejected): ?>
                <div class="card mb-4 border-danger-subtle" id="reject-lead-card">
                    <div class="card-header text-danger">
                        Reject Lead
                    </div>

                    <div class="card-body">
                        <p class="text-muted small">
                            Use this if the result is not an official agency/company website, is a Webflow staging/template URL, directory, social page, irrelevant company, or low-quality lead.
                        </p>

                        <form method="post" action="<?php echo e(route('discovery-results.reject', $result)); ?>">
                            <?php echo csrf_field(); ?>

                            <div class="mb-3">
                                <label class="form-label">Rejection Reason</label>
                                <textarea
                                    name="rejection_reason"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Example: Webflow staging/template URL, not an official agency website."
                                ></textarea>
                            </div>

                            <button
                                class="btn btn-outline-danger w-100"
                                onclick="return confirm('Reject this discovery lead?')"
                            >
                                <i class="bi bi-x-circle me-1"></i>
                                Reject This Lead
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($isManualRejected): ?>
                <div class="card mb-4 border-danger-subtle">
                    <div class="card-header text-danger">
                        Lead Rejected
                    </div>

                    <div class="card-body">
                        <div class="rule-box bad">
                            <div class="rule-icon">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div>
                                <strong>Manual Rejection</strong>
                                <p>This lead was rejected manually and should not be converted unless reviewed again.</p>
                            </div>
                        </div>

                        <div class="detail-label mb-2">Reason</div>
                        <div class="lead-reason">
                            <?php echo e($result->reason ?: 'No reason stored.'); ?>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($isConverted && $result->prospect): ?>
                <div class="card">
                    <div class="card-header">
                        Converted Prospect
                    </div>

                    <div class="card-body">
                        <div class="detail-list mb-3">
                            <div>
                                <span>Company</span>
                                <strong><?php echo e($result->prospect->company_name ?: 'Unknown'); ?></strong>
                            </div>

                            <div>
                                <span>Status</span>
                                <strong><?php echo e(str_replace('_', ' ', $result->prospect->status)); ?></strong>
                            </div>

                            <div>
                                <span>Fit Score</span>
                                <strong><?php echo e($result->prospect->fit_score ?? '—'); ?></strong>
                            </div>
                        </div>

                        <a href="<?php echo e(route('prospects.show', $result->prospect)); ?>" class="btn btn-primary w-100">
                            Open Prospect
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\ai-sales-agent\resources\views/discovery-results/show.blade.php ENDPATH**/ ?>