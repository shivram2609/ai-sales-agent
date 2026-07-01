<?php $__env->startSection('title', 'Prospects'); ?>
<?php $__env->startSection('subtitle', 'Manage leads before crawl, analysis, draft and review.'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Prospects</h1>
            <div class="page-subtitle">
                Search, filter, crawl, analyze and approve agency leads.
            </div>
        </div>

        <div class="page-actions">
            <a href="<?php echo e(route('prospects.create')); ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Add Prospect
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="<?php echo e(route('prospects.index')); ?>" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label">Search</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="<?php echo e(request('search')); ?>"
                        placeholder="Company, domain, category, region..."
                    >
                </div>

                <div class="col-lg-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status); ?>" <?php if(request('status') === $status): echo 'selected'; endif; ?>>
                                <?php echo e(str_replace('_', ' ', $status)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-lg-2">
                    <label class="form-label">Source</label>
                    <select name="source" class="form-select">
                        <option value="">All sources</option>
                        <?php $__currentLoopData = $sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($source); ?>" <?php if(request('source') === $source): echo 'selected'; endif; ?>>
                                <?php echo e($source); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-lg-2">
                    <label class="form-label">Per Page</label>
                    <select name="per_page" class="form-select">
                        <?php $__currentLoopData = [20, 50, 100]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($size); ?>" <?php if((int) request('per_page', 20) === $size): echo 'selected'; endif; ?>>
                                <?php echo e($size); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-lg-2 d-flex gap-2">
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>
                        Filter
                    </button>

                    <a href="<?php echo e(route('prospects.index')); ?>" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php echo $__env->make('prospects._table', ['prospects' => $prospects], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="mt-4">
        <?php echo e($prospects->links()); ?>

    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\ai-sales-agent\resources\views/prospects/index.blade.php ENDPATH**/ ?>