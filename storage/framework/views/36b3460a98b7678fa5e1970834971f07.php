<?php $__env->startSection('title', 'Outreach Drafts'); ?>
<?php $__env->startSection('subtitle', 'Review AI-generated outreach drafts before sending manually.'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $nextDirection = $direction === 'asc' ? 'desc' : 'asc';

        $sortUrl = function ($field) use ($nextDirection) {
            return request()->fullUrlWithQuery([
                'sort' => $field,
                'direction' => $nextDirection,
            ]);
        };
    ?>

    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Outreach Drafts</h1>
            <div class="page-subtitle">
                Search, review and copy outreach drafts. Human approval stays mandatory.
            </div>
        </div>

        <div class="page-actions">
            <a href="<?php echo e(url('/prospects')); ?>" class="btn btn-outline-primary">
                <i class="bi bi-building me-1"></i>
                Prospects
            </a>

            <a href="<?php echo e(url('/review-queue')); ?>" class="btn btn-primary">
                <i class="bi bi-check2-square me-1"></i>
                Review Queue
            </a>
        </div>
    </div>

    <div class="card table-card mb-4">
        <div class="card-body">
            <form method="get" action="<?php echo e(route('outreach-drafts.index')); ?>" class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <label class="form-label">Search</label>
                    <input
                        type="text"
                        name="search"
                        value="<?php echo e(request('search')); ?>"
                        class="form-control"
                        placeholder="Search subject, draft text, company, domain..."
                    >
                </div>

                <div class="col-lg-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>

                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status); ?>" <?php if(request('status') === $status): echo 'selected'; endif; ?>>
                                <?php echo e(str_replace('_', ' ', $status)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-lg-2">
                    <label class="form-label">Per Page</label>
                    <select name="per_page" class="form-select">
                        <?php $__currentLoopData = [20, 50, 100]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php if((int) request('per_page', $perPage) === $value): echo 'selected'; endif; ?>>
                                <?php echo e($value); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-lg-2">
                    <div class="d-grid">
                        <button class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i>
                            Filter
                        </button>
                    </div>
                </div>

                <?php if(request()->hasAny(['search', 'status', 'per_page'])): ?>
                    <div class="col-12">
                        <a href="<?php echo e(route('outreach-drafts.index')); ?>" class="btn btn-sm btn-outline-secondary">
                            Clear Filters
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between gap-2">
            <div>
                <strong>Drafts</strong>
                <span class="text-muted">(<?php echo e($drafts->total()); ?>)</span>
            </div>

            <div class="text-muted small">
                Showing <?php echo e($drafts->firstItem() ?? 0); ?> - <?php echo e($drafts->lastItem() ?? 0); ?> of <?php echo e($drafts->total()); ?>

            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>
                                <a href="<?php echo e($sortUrl('subject')); ?>" class="table-sort-link">
                                    Subject
                                </a>
                            </th>

                            <th>Prospect</th>

                            <th>
                                <a href="<?php echo e($sortUrl('status')); ?>" class="table-sort-link">
                                    Status
                                </a>
                            </th>

                            <th>
                                <a href="<?php echo e($sortUrl('created_at')); ?>" class="table-sort-link">
                                    Created
                                </a>
                            </th>

                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $drafts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $draft): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <div class="fw-bold">
                                        <?php echo e($draft->subject ?: 'No subject'); ?>

                                    </div>

                                    <div class="small text-muted draft-snippet">
                                        <?php echo e(\Illuminate\Support\Str::limit(strip_tags($draft->body ?: ''), 120)); ?>

                                    </div>
                                </td>

                                <td>
                                    <?php if($draft->prospect): ?>
                                        <div class="fw-semibold">
                                            <?php echo e($draft->prospect->company_name ?: 'Unknown Company'); ?>

                                        </div>

                                        <div class="small text-muted">
                                            <?php echo e($draft->prospect->domain ?: $draft->prospect->website_url); ?>

                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No prospect</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="status-badge <?php echo e($draft->status); ?>">
                                        <?php echo e(str_replace('_', ' ', $draft->status ?: 'draft')); ?>

                                    </span>
                                </td>

                                <td>
                                    <div class="small">
                                        <?php echo e($draft->created_at?->format('M d, Y')); ?>

                                    </div>

                                    <div class="small text-muted">
                                        <?php echo e($draft->created_at?->diffForHumans()); ?>

                                    </div>
                                </td>

                                <td class="text-end">
                                    <div class="draft-list-actions">
                                        <?php if($draft->prospect): ?>
                                            <a href="<?php echo e(url('/prospects/' . $draft->prospect->id)); ?>" class="btn btn-sm btn-outline-secondary">
                                                Prospect
                                            </a>
                                        <?php endif; ?>

                                        <a href="<?php echo e(route('outreach-drafts.show', $draft)); ?>" class="btn btn-sm btn-primary">
                                            Open Draft
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-envelope-paper"></i>
                                        </div>

                                        <h5 class="fw-bold mb-1">No outreach drafts yet</h5>

                                        <p class="text-muted mb-0">
                                            Generate drafts from a prospect detail page after crawling, analyzing and matching proof.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($drafts->hasPages()): ?>
            <div class="card-footer">
                <?php echo e($drafts->links()); ?>

            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\ai-sales-agent\resources\views/outreach-drafts/index.blade.php ENDPATH**/ ?>