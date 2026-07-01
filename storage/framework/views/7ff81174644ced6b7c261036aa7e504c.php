<?php $__env->startSection('content'); ?>
<?php
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
            <h1 class="h3 fw-bold mb-1"><?php echo e($campaign->name); ?></h1>

            <div class="text-muted">
                <?php echo e($campaign->audience_label ?: 'No audience label'); ?>

                <?php if($campaign->region_label): ?>
                    - <?php echo e($campaign->region_label); ?>

                <?php endif; ?>
            </div>

            <div class="mt-2">
                <span class="badge bg-light text-dark">
                    <?php echo e($campaignStatuses[$campaign->status] ?? $campaign->status); ?>

                </span>

                <span class="badge bg-light text-dark">
                    <?php echo e($campaign->members->count()); ?> member<?php echo e($campaign->members->count() === 1 ? '' : 's'); ?>

                </span>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="<?php echo e(route('campaigns.index')); ?>" class="btn btn-outline-secondary">
                Back
            </a>

            <a href="<?php echo e(route('campaigns.edit', $campaign)); ?>" class="btn btn-outline-primary">
                Edit Campaign
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <?php if($campaign->description || $campaign->notes): ?>
        <div class="row g-4 mb-4">
            <?php if($campaign->description): ?>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h2 class="h6 fw-bold mb-0">Description</h2>
                        </div>
                        <div class="card-body">
                            <?php echo e($campaign->description); ?>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($campaign->notes): ?>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h2 class="h6 fw-bold mb-0">Internal Notes</h2>
                        </div>
                        <div class="card-body">
                            <?php echo e($campaign->notes); ?>

                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h2 class="h5 fw-bold mb-0">Add Contact to Campaign</h2>
        </div>

        <div class="card-body">
            <form method="post" action="<?php echo e(route('campaigns.members.store', $campaign)); ?>">
                <?php echo csrf_field(); ?>

                <div class="row g-3 align-items-end">
                    <div class="col-lg-9">
                        <label class="form-label fw-semibold">Contact</label>
                        <select name="prospect_contact_id" class="form-select" required>
                            <option value="">Select contact</option>

                            <?php $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($contact->id); ?>">
                                    <?php echo e($contact->name ?: 'Unnamed Contact'); ?>

                                    <?php if($contact->title): ?>
                                        - <?php echo e($contact->title); ?>

                                    <?php endif; ?>
                                    <?php if($contact->prospect): ?>
                                        - <?php echo e($contact->prospect->company_name ?? $contact->prospect->domain); ?>

                                    <?php endif; ?>
                                    <?php if($contact->email): ?>
                                        - <?php echo e($contact->email); ?>

                                    <?php endif; ?>
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <button class="btn btn-primary w-100">
                            Add Contact
                        </button>
                    </div>
                </div>

                <div class="form-text mt-2">
                    V1 adds contacts one by one. Bulk add can come later after this is stable.
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h2 class="h5 fw-bold mb-0">Campaign Members</h2>
        </div>

        <div class="card-body">
            <?php if($campaign->members->count()): ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Contact</th>
                                <th>Company</th>
                                <th>Status</th>
                                <th>Draft</th>
                                <th>Follow-up Due</th>
                                <th>Notes</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__currentLoopData = $campaign->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold">
                                            <?php echo e($member->contact?->name ?: 'No Contact'); ?>

                                        </div>

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
                                        <span class="badge <?php echo e($statusBadgeMap[$member->status] ?? 'bg-secondary'); ?>">
                                            <?php echo e($memberStatuses[$member->status] ?? $member->status); ?>

                                        </span>
                                    </td>

                                    <td>
                                        <?php if($member->draft): ?>
                                            <a href="<?php echo e(route('outreach-drafts.show', $member->draft)); ?>" class="btn btn-sm btn-outline-primary">
                                                Open Draft
                                            </a>
                                        <?php else: ?>
                                            <form method="post" action="<?php echo e(route('campaign-members.draft.store', $member)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button class="btn btn-sm btn-outline-success">
                                                    Create Draft
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if($member->follow_up_1_due_at): ?>
                                            <div class="small">
                                                FU1: <?php echo e($member->follow_up_1_due_at->format('M d, Y')); ?>

                                            </div>
                                        <?php endif; ?>

                                        <?php if($member->follow_up_2_due_at): ?>
                                            <div class="small">
                                                FU2: <?php echo e($member->follow_up_2_due_at->format('M d, Y')); ?>

                                            </div>
                                        <?php endif; ?>

                                        <?php if(! $member->follow_up_1_due_at && ! $member->follow_up_2_due_at): ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td style="min-width: 220px;">
                                        <form method="post" action="<?php echo e(route('campaign-members.update', $member)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>

                                            <select name="status" class="form-select form-select-sm mb-2">
                                                <?php $__currentLoopData = $memberStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($value); ?>" <?php if($member->status === $value): echo 'selected'; endif; ?>>
                                                        <?php echo e($label); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>

                                            <textarea name="notes" class="form-control form-control-sm mb-2" rows="2" placeholder="Notes"><?php echo e($member->notes); ?></textarea>

                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <input type="date" name="first_touch_at" class="form-control form-control-sm" value="<?php echo e($member->first_touch_at?->format('Y-m-d')); ?>">
                                                </div>

                                                <div class="col-6">
                                                    <input type="date" name="follow_up_1_due_at" class="form-control form-control-sm" value="<?php echo e($member->follow_up_1_due_at?->format('Y-m-d')); ?>">
                                                </div>
                                            </div>

                                            <input type="hidden" name="follow_up_1_sent_at" value="<?php echo e($member->follow_up_1_sent_at?->format('Y-m-d')); ?>">
                                            <input type="hidden" name="follow_up_2_due_at" value="<?php echo e($member->follow_up_2_due_at?->format('Y-m-d')); ?>">
                                            <input type="hidden" name="follow_up_2_sent_at" value="<?php echo e($member->follow_up_2_sent_at?->format('Y-m-d')); ?>">
                                            <input type="hidden" name="replied_at" value="<?php echo e($member->replied_at?->format('Y-m-d')); ?>">
                                            <input type="hidden" name="last_interaction_at" value="<?php echo e($member->last_interaction_at?->format('Y-m-d')); ?>">

                                            <button class="btn btn-sm btn-outline-secondary mt-2">
                                                Save
                                            </button>
                                        </form>
                                    </td>

                                    
									
									<td class="text-end">
										<div class="mb-2">
											<?php echo $__env->make('campaign-members.partials.quick-actions', ['member' => $member], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
										</div>

										<form method="post" action="<?php echo e(route('campaign-members.destroy', $member)); ?>">
											<?php echo csrf_field(); ?>
											<?php echo method_field('DELETE'); ?>

											<button
												class="btn btn-sm btn-outline-danger"
												onclick="return confirm('Remove this contact from campaign?')"
											>
												Remove
											</button>
										</form>
										<a href="<?php echo e(route('campaign-members.sending-control', $member)); ?>" class="btn btn-sm btn-outline-warning mb-2">
											Sending Control
										</a>
									</td>
									
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-light border mb-0">
                    No contacts added to this campaign yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\ai-sales-agent\resources\views/campaigns/show.blade.php ENDPATH**/ ?>