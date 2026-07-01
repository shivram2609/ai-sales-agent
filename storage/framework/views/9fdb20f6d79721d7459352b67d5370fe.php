<?php $__env->startSection('title', 'Discovery Campaigns'); ?>
<?php $__env->startSection('subtitle', 'Find official agency and company websites using Smart Query Builder and SERPAPI.'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        function discovery_sort_link($label, $column, $sort, $direction) {
            $nextDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';
            $icon = '';

            if ($sort === $column) {
                $icon = $direction === 'asc' ? ' ↑' : ' ↓';
            }

            $url = request()->fullUrlWithQuery([
                'sort' => $column,
                'direction' => $nextDirection,
            ]);

            return '<a href="'.$url.'" class="text-decoration-none text-dark">'.$label.$icon.'</a>';
        }
    ?>

    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Discovery Campaigns</h1>
            <div class="page-subtitle">
                Build smart SERPAPI campaigns, reject directories/listicles, and convert only official websites.
            </div>
        </div>

        <div class="page-actions">
            <a href="<?php echo e(route('discovery.create')); ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Create Campaign
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="<?php echo e(route('discovery.index')); ?>" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label">Search</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="<?php echo e(request('search')); ?>"
                        placeholder="Campaign, service, region, audience..."
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
                    <label class="form-label">Query Mode</label>
                    <select name="query_mode" class="form-select">
                        <option value="">All modes</option>
                        <?php $__currentLoopData = $queryModes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($mode); ?>" <?php if(request('query_mode') === $mode): echo 'selected'; endif; ?>>
                                <?php echo e(str_replace('_', ' ', $mode)); ?>

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

                    <a href="<?php echo e(route('discovery.index')); ?>" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th><?php echo discovery_sort_link('Campaign', 'name', $sort, $direction); ?></th>
                            <th>Focus</th>
                            <th>Regions</th>
                            <th><?php echo discovery_sort_link('Mode', 'query_mode', $sort, $direction); ?></th>
                            <th><?php echo discovery_sort_link('Status', 'status', $sort, $direction); ?></th>
                            <th>Results</th>
                            <th><?php echo discovery_sort_link('Updated', 'updated_at', $sort, $direction); ?></th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo e($campaign->name); ?></div>
                                    <div class="small-text">
                                        <?php echo e($campaign->source_engine ?: 'serpapi_google'); ?>

                                    </div>
                                </td>

                                <td>
                                    <div class="small-text">
                                        <?php if(is_array($campaign->service_focus)): ?>
                                            <?php echo e(implode(', ', array_slice($campaign->service_focus, 0, 3))); ?>

                                        <?php else: ?>
                                            <?php echo e($campaign->service_focus ?: '—'); ?>

                                        <?php endif; ?>
                                    </div>

                                    <div class="small text-muted">
                                        <?php if(is_array($campaign->target_audience)): ?>
                                            <?php echo e(implode(', ', array_slice($campaign->target_audience, 0, 3))); ?>

                                        <?php else: ?>
                                            <?php echo e($campaign->target_audience ?: '—'); ?>

                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="small-text">
                                        <?php if(is_array($campaign->regions_json)): ?>
                                            <?php echo e(implode(', ', array_slice($campaign->regions_json, 0, 3))); ?>

                                        <?php else: ?>
                                            <?php echo e($campaign->regions_json ?: '—'); ?>

                                        <?php endif; ?>
                                    </div>

                                    <?php if(is_array($campaign->cities_json) && count($campaign->cities_json)): ?>
                                        <div class="small text-muted">
                                            Cities: <?php echo e(implode(', ', array_slice($campaign->cities_json, 0, 3))); ?>

                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="status-badge <?php echo e($campaign->query_mode); ?>">
                                        <?php echo e(str_replace('_', ' ', $campaign->query_mode)); ?>

                                    </span>
                                </td>

                                <td>
                                    <span class="status-badge <?php echo e($campaign->status); ?>">
                                        <?php echo e(str_replace('_', ' ', $campaign->status)); ?>

                                    </span>
                                </td>

                                <td>
                                    <span class="fw-bold"><?php echo e($campaign->results_count ?? 0); ?></span>
                                </td>

                                <td>
                                    <span class="small-text">
                                        <?php echo e($campaign->updated_at?->diffForHumans()); ?>

                                    </span>
                                </td>

                                <td class="text-end">
                                    <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                        <a href="<?php echo e(route('discovery.show', $campaign)); ?>" class="btn btn-sm btn-outline-primary">
                                            Open
                                        </a>

                                        <?php if(!in_array($campaign->status, ['queued', 'running'], true)): ?>
                                            <form method="post" action="<?php echo e(route('discovery.run', $campaign)); ?>" class="m-0">
                                                <?php echo csrf_field(); ?>
                                                <button class="btn btn-sm btn-primary">
                                                    Run
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                                Running
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-search"></i>
                                        </div>

                                        <h5 class="fw-bold mb-1">No discovery campaigns yet</h5>

                                        <p class="text-muted mb-3">
                                            Create your first Smart Query Builder campaign for official agency website discovery.
                                        </p>

                                        <a href="<?php echo e(route('discovery.create')); ?>" class="btn btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i>
                                            Create Campaign
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <?php echo e($campaigns->links()); ?>

    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\ai-sales-agent\resources\views/discovery/index.blade.php ENDPATH**/ ?>