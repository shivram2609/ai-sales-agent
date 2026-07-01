

<?php $__env->startSection('content'); ?>
<?php
    $closedLabels = [
        'replied' => 'Replied',
        'not_interested' => 'Not Interested',
        'bounced' => 'Bounced',
        'paused' => 'Paused',
    ];

    $statusBadgeMap = [
        'not_started' => 'bg-secondary',
        'draft_ready' => 'bg-info text-dark',
        'approved' => 'bg-primary',
        'sent_manually' => 'bg-success',
        'follow_up_1_due' => 'bg-warning text-dark',
        'follow_up_1_sent' => 'bg-success',
        'follow_up_2_due' => 'bg-warning text-dark',
        'follow_up_2_sent' => 'bg-success',
        'replied' => 'bg-success',
        'not_interested' => 'bg-dark',
        'bounced' => 'bg-danger',
        'paused' => 'bg-secondary',
    ];
?>

<div class="container-fluid py-4">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Outreach Queue</h1>
            <div class="text-muted">
                Daily execution view for first touches, follow-ups, missing drafts, and replies.
            </div>
        </div>

        <a href="<?php echo e(route('campaigns.index')); ?>" class="btn btn-outline-secondary">
            Open Campaigns
        </a>
    </div>
	<?php if(session('success')): ?>
		<div class="alert alert-success">
			<?php echo e(session('success')); ?>

		</div>
	<?php endif; ?>

	<?php if(session('error')): ?>
		<div class="alert alert-danger">
			<?php echo e(session('error')); ?>

		</div>
	<?php endif; ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">Follow-ups Due</div>
                    <div class="h3 fw-bold mb-0"><?php echo e($followUpsDue->count()); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">Ready First Touch</div>
                    <div class="h3 fw-bold mb-0"><?php echo e($readyForFirstTouch->count()); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">Missing Drafts</div>
                    <div class="h3 fw-bold mb-0"><?php echo e($missingDrafts->count()); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">Recent Replies</div>
                    <div class="h3 fw-bold mb-0"><?php echo e($recentReplies->count()); ?></div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h5 fw-bold mb-0">Follow-ups Due</h2>
                <div class="small text-muted">Contacts where FU1 or FU2 is due today or overdue.</div>
            </div>
        </div>

        <div class="card-body">
            <?php if($followUpsDue->count()): ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Contact</th>
                                <th>Company</th>
                                <th>Campaign</th>
                                <th>Due</th>
                                <th>Status</th>
                                <th>Draft</th>
                                <th>Notes</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__currentLoopData = $followUpsDue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $dueType = null;
                                    $dueDate = null;

                                    if ($member->follow_up_1_due_at && ! $member->follow_up_1_sent_at) {
                                        $dueType = 'FU1';
                                        $dueDate = $member->follow_up_1_due_at;
                                    } elseif ($member->follow_up_2_due_at && ! $member->follow_up_2_sent_at) {
                                        $dueType = 'FU2';
                                        $dueDate = $member->follow_up_2_due_at;
                                    }

                                    $isOverdue = $dueDate && $dueDate->startOfDay()->lt($today);
                                ?>

                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo e($member->contact?->name ?: 'No Contact'); ?></div>

                                        <?php if($member->contact?->title): ?>
                                            <div class="small text-muted"><?php echo e($member->contact->title); ?></div>
                                        <?php endif; ?>

                                        <?php if($member->contact?->email): ?>
                                            <div class="small">
                                                <a href="mailto:<?php echo e($member->contact->email); ?>">
                                                    <?php echo e($member->contact->email); ?>

                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if($member->prospect): ?>
                                            <a href="<?php echo e(route('prospects.show', $member->prospect)); ?>" class="fw-semibold">
                                                <?php echo e($member->prospect->company_name ?? $member->prospect->domain); ?>

                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if($member->campaign): ?>
                                            <a href="<?php echo e(route('campaigns.show', $member->campaign)); ?>">
                                                <?php echo e($member->campaign->name); ?>

                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if($dueDate): ?>
                                            <span class="badge <?php echo e($isOverdue ? 'bg-danger' : 'bg-warning text-dark'); ?>">
                                                <?php echo e($dueType); ?>: <?php echo e($dueDate->format('M d, Y')); ?>

                                            </span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="badge <?php echo e($statusBadgeMap[$member->status] ?? 'bg-secondary'); ?>">
                                            <?php echo e(str_replace('_', ' ', ucfirst($member->status))); ?>

                                        </span>
                                    </td>

                                    <td>
                                        <?php if($member->draft): ?>
                                            <a href="<?php echo e(route('outreach-drafts.show', $member->draft)); ?>" class="btn btn-sm btn-outline-primary">
                                                Open Draft
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td style="min-width: 220px;">
                                        <div class="small text-muted">
                                            <?php echo e($member->notes ?: '-'); ?>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-light border mb-0">
                    No follow-ups due right now.
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h2 class="h5 fw-bold mb-0">Ready for First Touch</h2>
            <div class="small text-muted">Draft exists and contact has not been marked as sent yet.</div>
        </div>

        <div class="card-body">
            <?php if($readyForFirstTouch->count()): ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Contact</th>
                                <th>Company</th>
                                <th>Campaign</th>
                                <th>Status</th>
                                <th>Draft</th>
                                <th>Notes</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__currentLoopData = $readyForFirstTouch; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo e($member->contact?->name ?: 'No Contact'); ?></div>

                                        <?php if($member->contact?->email): ?>
                                            <div class="small">
                                                <a href="mailto:<?php echo e($member->contact->email); ?>">
                                                    <?php echo e($member->contact->email); ?>

                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if($member->prospect): ?>
                                            <a href="<?php echo e(route('prospects.show', $member->prospect)); ?>" class="fw-semibold">
                                                <?php echo e($member->prospect->company_name ?? $member->prospect->domain); ?>

                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if($member->campaign): ?>
                                            <a href="<?php echo e(route('campaigns.show', $member->campaign)); ?>">
                                                <?php echo e($member->campaign->name); ?>

                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="badge <?php echo e($statusBadgeMap[$member->status] ?? 'bg-secondary'); ?>">
                                            <?php echo e(str_replace('_', ' ', ucfirst($member->status))); ?>

                                        </span>
                                    </td>

                                    <td>
                                        <?php if($member->draft): ?>
                                            <a href="<?php echo e(route('outreach-drafts.show', $member->draft)); ?>" class="btn btn-sm btn-outline-primary">
                                                Open Draft
                                            </a>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="small text-muted">
                                            <?php echo e($member->notes ?: '-'); ?>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-light border mb-0">
                    No first-touch items ready right now.
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h2 class="h5 fw-bold mb-0">Missing Drafts</h2>
            <div class="small text-muted">Campaign members added but no draft linked yet.</div>
        </div>

        <div class="card-body">
            <?php if($missingDrafts->count()): ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Contact</th>
                                <th>Company</th>
                                <th>Campaign</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__currentLoopData = $missingDrafts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo e($member->contact?->name ?: 'No Contact'); ?></div>

                                        <?php if($member->contact?->email): ?>
                                            <div class="small">
                                                <a href="mailto:<?php echo e($member->contact->email); ?>">
                                                    <?php echo e($member->contact->email); ?>

                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if($member->prospect): ?>
                                            <a href="<?php echo e(route('prospects.show', $member->prospect)); ?>" class="fw-semibold">
                                                <?php echo e($member->prospect->company_name ?? $member->prospect->domain); ?>

                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if($member->campaign): ?>
                                            <a href="<?php echo e(route('campaigns.show', $member->campaign)); ?>">
                                                <?php echo e($member->campaign->name); ?>

                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="badge <?php echo e($statusBadgeMap[$member->status] ?? 'bg-secondary'); ?>">
                                            <?php echo e(str_replace('_', ' ', ucfirst($member->status))); ?>

                                        </span>
                                    </td>

                                    <td>
                                        <form method="post" action="<?php echo e(route('campaign-members.draft.store', $member)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button class="btn btn-sm btn-outline-success">
                                                Create Draft
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-light border mb-0">
                    No missing drafts.
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h2 class="h5 fw-bold mb-0">Recent Replies</h2>
            <div class="small text-muted">Campaign members marked as replied.</div>
        </div>

        <div class="card-body">
            <?php if($recentReplies->count()): ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Contact</th>
                                <th>Company</th>
                                <th>Campaign</th>
                                <th>Replied</th>
                                <th>Notes</th>
								<th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__currentLoopData = $recentReplies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo e($member->contact?->name ?: 'No Contact'); ?></div>

                                        <?php if($member->contact?->email): ?>
                                            <div class="small">
                                                <a href="mailto:<?php echo e($member->contact->email); ?>">
                                                    <?php echo e($member->contact->email); ?>

                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if($member->prospect): ?>
                                            <a href="<?php echo e(route('prospects.show', $member->prospect)); ?>" class="fw-semibold">
                                                <?php echo e($member->prospect->company_name ?? $member->prospect->domain); ?>

                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if($member->campaign): ?>
                                            <a href="<?php echo e(route('campaigns.show', $member->campaign)); ?>">
                                                <?php echo e($member->campaign->name); ?>

                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php echo e($member->replied_at?->format('M d, Y') ?: '-'); ?>

                                    </td>

                                    <td>
                                        <div class="small text-muted">
                                            <?php echo e($member->notes ?: '-'); ?>

                                        </div>
                                    </td>
									<td style="min-width: 260px;">
										<?php echo $__env->make('campaign-members.partials.quick-actions', ['member' => $member], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
									</td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-light border mb-0">
                    No replies marked yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\ai-sales-agent\resources\views/outreach-queue/index.blade.php ENDPATH**/ ?>