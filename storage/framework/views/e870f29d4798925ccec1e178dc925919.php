<?php
    function sort_link($label, $column, $sort, $direction) {
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

<div class="card table-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th><?php echo sort_link('Company', 'company_name', $sort, $direction); ?></th>
                        <th><?php echo sort_link('Domain', 'domain', $sort, $direction); ?></th>
                        <th><?php echo sort_link('Status', 'status', $sort, $direction); ?></th>
                        <th><?php echo sort_link('Fit', 'fit_score', $sort, $direction); ?></th>
                        <th><?php echo sort_link('Source', 'source', $sort, $direction); ?></th>
                        <th><?php echo sort_link('Updated', 'updated_at', $sort, $direction); ?></th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $prospects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prospect): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div class="fw-bold">
                                    <?php echo e($prospect->company_name ?: 'Unknown Company'); ?>

                                </div>
                                <div class="small-text">
                                    <?php echo e($prospect->category_guess ?: 'No category yet'); ?>

                                </div>
                            </td>

                            <td>
                                <a href="<?php echo e($prospect->website_url); ?>" target="_blank" class="text-decoration-none fw-semibold">
                                    <?php echo e($prospect->domain); ?>

                                </a>
                            </td>

                            <td>
                                <span class="status-badge <?php echo e($prospect->status); ?>">
                                    <?php echo e(str_replace('_', ' ', $prospect->status)); ?>

                                </span>
                            </td>

                            <td>
                                <span class="fit-score">
                                    <?php echo e($prospect->fit_score ?? '—'); ?>

                                </span>
                            </td>

                            <td>
                                <?php echo e($prospect->source ?: '—'); ?>

                            </td>

                            <td>
                                <span class="small-text">
                                    <?php echo e($prospect->updated_at?->diffForHumans()); ?>

                                </span>
                            </td>

                            <td class="text-end">
                                <a href="<?php echo e(route('prospects.show', $prospect)); ?>" class="btn btn-sm btn-outline-primary">
                                    Open
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <h5 class="fw-bold mb-1">No prospects found</h5>
                                    <p class="text-muted mb-3">
                                        Try changing your filters or add your first prospect.
                                    </p>
                                    <a href="<?php echo e(route('prospects.create')); ?>" class="btn btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i>
                                        Add Prospect
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div><?php /**PATH D:\ai-sales-agent\resources\views/prospects/_table.blade.php ENDPATH**/ ?>