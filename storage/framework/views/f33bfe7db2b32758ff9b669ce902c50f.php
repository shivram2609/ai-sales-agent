<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-title">AI Sales Agent</div>
        <div class="sidebar-brand-subtitle">Zestminds Outreach OS</div>
    </div>

    <nav class="sidebar-nav">
        <a href="<?php echo e(url('/')); ?>" class="<?php echo e(request()->is('/') ? 'active' : ''); ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <a href="<?php echo e(url('/prospects')); ?>" class="<?php echo e(request()->is('prospects*') ? 'active' : ''); ?>">
            <i class="bi bi-building"></i>
            <span>Prospects</span>
        </a>

        <a href="<?php echo e(url('/discovery')); ?>" class="<?php echo e(request()->is('discovery*') ? 'active' : ''); ?>">
            <i class="bi bi-search"></i>
            <span>Discovery</span>
        </a>

        <a href="<?php echo e(url('/review-queue')); ?>" class="<?php echo e(request()->is('review-queue*') ? 'active' : ''); ?>">
            <i class="bi bi-check2-square"></i>
            <span>Review Queue</span>
        </a>

        <a href="<?php echo e(url('/outreach-drafts')); ?>" class="<?php echo e(request()->is('outreach-drafts*') ? 'active' : ''); ?>">
            <i class="bi bi-envelope-paper"></i>
            <span>Drafts</span>
        </a>

        <a href="<?php echo e(url('/campaigns')); ?>" class="<?php echo e(request()->is('campaigns*') ? 'active' : ''); ?>">
			<i class="bi bi-megaphone"></i>
			<span>Campaigns</span>
		</a>
		
		<a href="<?php echo e(url('/outreach-queue')); ?>" class="<?php echo e(request()->is('outreach-queue*') ? 'active' : ''); ?>">
			<i class="bi bi-list-check"></i>
			<span>Outreach Queue</span>
		</a>
		
		<a href="<?php echo e(url('/sending-queue')); ?>" class="<?php echo e(request()->is('sending-queue*') ? 'active' : ''); ?>">
			<i class="bi bi-envelope-check"></i>
			<span>Sending Queue</span>
		</a>
		
		
    </nav>

    <div class="sidebar-footer">
        <strong>Rule:</strong><br>
        AI prepares. Human approves. No auto-spam.
    </div>
</aside><?php /**PATH D:\ai-sales-agent\resources\views/partials/sidebar.blade.php ENDPATH**/ ?>