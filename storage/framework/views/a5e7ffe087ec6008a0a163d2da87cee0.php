<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Campaigns</h1>
            <div class="text-muted">Organize outreach lists, drafts, and manual follow-up tracking.</div>
        </div>

        <a href="<?php echo e(route('campaigns.create')); ?>" class="btn btn-primary">
            Create Campaign
        </a>
    </div>

    <form method="get" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php if($selectedStatus === $value): echo 'selected'; endif; ?>>
                                <?php echo e($label); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn btn-outline-secondary w-100">
                        Filter
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if($campaigns->count()): ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Audience</th>
                                <th>Region</th>
                                <th>Status</th>
                                <th>Members</th>
                                <th>Created</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo e(route('campaigns.show', $campaign)); ?>" class="fw-bold">
                                            <?php echo e($campaign->name); ?>

                                        </a>
                                    </td>
                                    <td><?php echo e($campaign->audience_label ?: '-'); ?></td>
                                    <td><?php echo e($campaign->region_label ?: '-'); ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?php echo e($statuses[$campaign->status] ?? $campaign->status); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($campaign->members_count); ?></td>
                                    <td><?php echo e($campaign->created_at?->format('M d, Y')); ?></td>
                                    <td class="text-end">
                                        <a href="<?php echo e(route('campaigns.show', $campaign)); ?>" class="btn btn-sm btn-outline-primary">
                                            Open
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <?php echo e($campaigns->links()); ?>

            <?php else: ?>
                <div class="alert alert-light border mb-0">
                    No campaigns yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\ai-sales-agent\resources\views/campaigns/index.blade.php ENDPATH**/ ?>