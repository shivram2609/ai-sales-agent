

<?php $__env->startSection('content'); ?>
<?php
    $badgeMap = [
        'queued' => 'bg-info text-dark',
        'sending' => 'bg-warning text-dark',
        'sent' => 'bg-success',
        'failed' => 'bg-danger',
        'cancelled' => 'bg-secondary',
        'skipped' => 'bg-dark',
    ];
?>

<div class="container-fluid py-4">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Sending Queue</h1>
            <div class="text-muted">
                Controlled queue for approved outbound emails. Actual provider sending will be connected next.
            </div>
        </div>

        <a href="<?php echo e(route('campaigns.index')); ?>" class="btn btn-outline-secondary">
            Open Campaigns
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

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
            <?php if($jobs->count()): ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Scheduled</th>
                                <th>Type</th>
                                <th>To</th>
                                <th>Company</th>
                                <th>Campaign</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Draft</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <?php echo e($job->scheduled_at?->format('M d, Y h:i A') ?: '-'); ?>

                                    </td>

                                    <td>
                                        <?php echo e($types[$job->email_type] ?? $job->email_type); ?>

                                    </td>

                                    <td>
                                        <div class="fw-semibold"><?php echo e($job->to_name ?: '-'); ?></div>
                                        <div class="small">
                                            <a href="mailto:<?php echo e($job->to_email); ?>"><?php echo e($job->to_email); ?></a>
                                        </div>
                                    </td>

                                    <td>
                                        <?php if($job->prospect): ?>
                                            <a href="<?php echo e(route('prospects.show', $job->prospect)); ?>">
                                                <?php echo e($job->prospect->company_name ?? $job->prospect->domain); ?>

                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if($job->campaign): ?>
                                            <a href="<?php echo e(route('campaigns.show', $job->campaign)); ?>">
                                                <?php echo e($job->campaign->name); ?>

                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td style="min-width: 260px;">
                                        <div class="fw-semibold"><?php echo e($job->subject); ?></div>

                                        <?php if($job->preheader): ?>
                                            <div class="small text-muted"><?php echo e($job->preheader); ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="badge <?php echo e($badgeMap[$job->status] ?? 'bg-secondary'); ?>">
                                            <?php echo e($statuses[$job->status] ?? $job->status); ?>

                                        </span>
                                    </td>

                                    <td>
                                        <?php if($job->draft): ?>
                                            <a href="<?php echo e(route('outreach-drafts.show', $job->draft)); ?>" class="btn btn-sm btn-outline-primary">
                                                Open Draft
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <?php if($job->campaignMember): ?>
                                                <a href="<?php echo e(route('campaign-members.sending-control', $job->campaignMember)); ?>" class="btn btn-sm btn-outline-secondary">
                                                    Control
                                                </a>
                                            <?php endif; ?>

                                            <?php if(in_array($job->status, ['queued', 'failed'], true)): ?>
                                                <form method="post" action="<?php echo e(route('outbound-email-jobs.cancel', $job)); ?>">
                                                    <?php echo csrf_field(); ?>

                                                    <input type="hidden" name="reason" value="Cancelled from Sending Queue">

                                                    <button
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Cancel this queued email?')"
                                                    >
                                                        Cancel
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <?php echo e($jobs->links()); ?>

            <?php else: ?>
                <div class="alert alert-light border mb-0">
                    No outbound email jobs found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\ai-sales-agent\resources\views/sending-queue/index.blade.php ENDPATH**/ ?>