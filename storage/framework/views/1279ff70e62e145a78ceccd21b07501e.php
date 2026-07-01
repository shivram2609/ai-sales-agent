

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Sending Control</h1>
            <div class="text-muted">
                Approve, schedule, pause, resume, or stop this outreach sequence.
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="<?php echo e(route('campaigns.show', $member->campaign_id)); ?>" class="btn btn-outline-secondary">
                Back to Campaign
            </a>

            <a href="<?php echo e(route('sending-queue.index')); ?>" class="btn btn-outline-primary">
                Sending Queue
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

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

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h2 class="h5 fw-bold mb-0">Campaign Member</h2>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <div class="small text-muted">Campaign</div>
                        <div class="fw-semibold"><?php echo e($member->campaign?->name ?: '-'); ?></div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Company</div>
                        <div class="fw-semibold">
                            <?php if($member->prospect): ?>
                                <a href="<?php echo e(route('prospects.show', $member->prospect)); ?>">
                                    <?php echo e($member->prospect->company_name ?? $member->prospect->domain); ?>

                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Contact</div>
                        <div class="fw-semibold"><?php echo e($member->contact?->name ?: 'No Contact'); ?></div>

                        <?php if($member->contact?->title): ?>
                            <div class="small text-muted"><?php echo e($member->contact->title); ?></div>
                        <?php endif; ?>

                        <?php if($member->contact?->email): ?>
                            <div class="small">
                                <a href="mailto:<?php echo e($member->contact->email); ?>"><?php echo e($member->contact->email); ?></a>
                            </div>
                        <?php endif; ?>

                        <div class="small text-muted mt-1">
                            Contact status: <?php echo e($member->contact?->status ?: '-'); ?>

                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Current Member Status</div>
                        <div class="fw-semibold"><?php echo e(str_replace('_', ' ', $member->status)); ?></div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Sequence Mode</div>
                        <div class="fw-semibold">
                            <?php echo e($sequenceModes[$member->sequence_mode] ?? $member->sequence_mode ?? 'Manual Only'); ?>

                        </div>
                    </div>

                    <?php if($member->approved_for_sending_at): ?>
                        <div class="mb-3">
                            <div class="small text-muted">Approved At</div>
                            <div class="fw-semibold"><?php echo e($member->approved_for_sending_at->format('M d, Y h:i A')); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if($member->sequence_paused_at): ?>
                        <div class="alert alert-warning">
                            This sequence is paused.
                        </div>
                    <?php endif; ?>

                    <?php if($member->sequence_stopped_at): ?>
                        <div class="alert alert-danger">
                            This sequence was stopped.
                            <?php if($member->stop_reason): ?>
                                <div class="small mt-1"><?php echo e($member->stop_reason); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 fw-bold mb-0">Linked Draft Preview</h2>
                </div>

                <div class="card-body">
                    <?php if($member->draft): ?>
                        <div class="mb-3">
                            <div class="small text-muted">Subject</div>
                            <div class="fw-semibold"><?php echo e($member->draft->subject); ?></div>
                        </div>

                        <?php if($member->draft->preheader): ?>
                            <div class="mb-3">
                                <div class="small text-muted">Preheader</div>
                                <div><?php echo e($member->draft->preheader); ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <div class="small text-muted">Email Body</div>
                            <div class="border rounded bg-light p-3" style="white-space: pre-wrap;"><?php echo e($member->draft->body); ?></div>
                        </div>

                        <a href="<?php echo e(route('outreach-drafts.show', $member->draft)); ?>" class="btn btn-outline-primary">
                            Open Draft
                        </a>
                    <?php else: ?>
                        <div class="alert alert-light border mb-0">
                            No draft linked. Create and approve a draft before using automated sending.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h2 class="h5 fw-bold mb-0">Approve and Queue</h2>
                </div>

                <div class="card-body">
                    <form method="post" action="<?php echo e(route('campaign-members.approve-sending', $member)); ?>">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sequence Mode</label>
                            <select name="sequence_mode" class="form-select" required>
                                <?php $__currentLoopData = $sequenceModes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>" <?php if(old('sequence_mode', $member->sequence_mode ?: 'manual_only') === $value): echo 'selected'; endif; ?>>
                                        <?php echo e($label); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>

                            <div class="form-text">
                                Manual Only will approve but will not create an email queue job.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">First Email Scheduled At</label>
                            <input
                                type="datetime-local"
                                name="scheduled_at"
                                class="form-control"
                                value="<?php echo e(old('scheduled_at', now()->format('Y-m-d\TH:i'))); ?>"
                            >

                            <div class="form-text">
                                Used only for First Email Only or Full Sequence mode.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Approval Notes</label>
                            <textarea name="approval_notes" class="form-control" rows="3"><?php echo e(old('approval_notes', $member->approval_notes)); ?></textarea>
                        </div>

                        <button class="btn btn-primary w-100" onclick="return confirm('Approve this campaign member for controlled sending?')">
                            Approve and Queue
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h2 class="h5 fw-bold mb-0">Manual Controls</h2>
                </div>

                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if($member->status === \App\Models\CampaignMember::STATUS_PAUSED): ?>
                            <form method="post" action="<?php echo e(route('campaign-members.resume-sequence', $member)); ?>">
                                <?php echo csrf_field(); ?>
                                <button class="btn btn-outline-success w-100">
                                    Resume Sequence
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="<?php echo e(route('campaign-members.pause-sequence', $member)); ?>">
                                <?php echo csrf_field(); ?>
                                <button class="btn btn-outline-warning w-100">
                                    Pause Sequence
                                </button>
                            </form>
                        <?php endif; ?>

                        <form method="post" action="<?php echo e(route('campaign-members.stop-sequence', $member)); ?>">
                            <?php echo csrf_field(); ?>

                            <textarea name="stop_reason" class="form-control mb-2" rows="2" placeholder="Reason for stopping"><?php echo e(old('stop_reason')); ?></textarea>

                            <button
                                class="btn btn-outline-danger w-100"
                                onclick="return confirm('Stop this sequence and cancel queued emails?')"
                            >
                                Stop Sequence
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 fw-bold mb-0">Email Jobs</h2>
                </div>

                <div class="card-body">
                    <?php if($member->outboundEmailJobs->count()): ?>
                        <div class="list-group">
                            <?php $__currentLoopData = $member->outboundEmailJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between gap-3">
                                        <div>
                                            <div class="fw-semibold"><?php echo e(str_replace('_', ' ', $job->email_type)); ?></div>
                                            <div class="small text-muted">
                                                <?php echo e($job->status); ?>

                                                <?php if($job->scheduled_at): ?>
                                                    - <?php echo e($job->scheduled_at->format('M d, Y h:i A')); ?>

                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if(in_array($job->status, ['queued', 'failed'], true)): ?>
                                            <form method="post" action="<?php echo e(route('outbound-email-jobs.cancel', $job)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button class="btn btn-sm btn-outline-danger">
                                                    Cancel
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border mb-0">
                            No email jobs yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\ai-sales-agent\resources\views/campaign-members/sending-control.blade.php ENDPATH**/ ?>