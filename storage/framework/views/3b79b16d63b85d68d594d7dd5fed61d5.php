<?php $__env->startSection('title', $campaign->name); ?>
<?php $__env->startSection('subtitle', 'Review generated smart queries, SERPAPI results, filtering decisions and prospect conversion.'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $sortLink = function ($label, $column) use ($sort, $direction) {
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
        };

        $serviceFocus = is_array($campaign->service_focus) ? $campaign->service_focus : [];
        $targetAudience = is_array($campaign->target_audience) ? $campaign->target_audience : [];
        $regions = is_array($campaign->regions_json) ? $campaign->regions_json : [];
        $cities = is_array($campaign->cities_json) ? $campaign->cities_json : [];
        $excludeTerms = is_array($campaign->exclude_terms_json) ? $campaign->exclude_terms_json : [];
    ?>

    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
        <div>
            <h1 class="page-title"><?php echo e($campaign->name); ?></h1>
            <div class="page-subtitle">
                <?php echo e(strtoupper($campaign->source_engine ?: 'serpapi_google')); ?>

                <span class="mx-1">·</span>
                <?php echo e(str_replace('_', ' ', $campaign->query_mode)); ?>

                <span class="mx-1">·</span>
                Created <?php echo e($campaign->created_at?->diffForHumans()); ?>

            </div>
        </div>

        <div class="page-actions">
            <a href="<?php echo e(route('discovery.index')); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back
            </a>

            <?php if(!in_array($campaign->status, ['queued', 'running'], true)): ?>
                <form method="post" action="<?php echo e(route('discovery.run', $campaign)); ?>" class="m-0">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-primary">
                        <i class="bi bi-play-fill me-1"></i>
                        Run Campaign
                    </button>
                </form>
            <?php else: ?>
                <button class="btn btn-outline-secondary" disabled>
                    <i class="bi bi-hourglass-split me-1"></i>
                    Campaign Running
                </button>
            <?php endif; ?>

            <?php if(($stats['strong'] ?? 0) > 0): ?>
                <form method="post" action="<?php echo e(route('discovery.convert-strong', $campaign)); ?>" class="m-0">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-arrow-right-circle me-1"></i>
                        Convert Strong
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Status</div>
                    <div class="mt-2">
                        <span class="status-badge <?php echo e($campaign->status); ?>">
                            <?php echo e(str_replace('_', ' ', $campaign->status)); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Total Results</div>
                    <div class="stat-value"><?php echo e($stats['total'] ?? 0); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Strong</div>
                    <div class="stat-value"><?php echo e($stats['strong'] ?? 0); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Possible</div>
                    <div class="stat-value"><?php echo e($stats['possible'] ?? 0); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Rejected</div>
                    <div class="stat-value"><?php echo e($stats['rejected'] ?? 0); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Converted</div>
                    <div class="stat-value"><?php echo e($stats['converted'] ?? 0); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <span>Generated Smart Queries</span>
                    <span class="small text-muted"><?php echo e(count($queries)); ?> query/queries</span>
                </div>

                <div class="card-body">
                    <?php if(count($queries)): ?>
                        <div class="query-list">
                            <?php $__currentLoopData = $queries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $query): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="query-item">
                                    <code><?php echo e($query); ?></code>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bi bi-search"></i>
                            </div>
                            <h5 class="fw-bold mb-1">No generated queries</h5>
                            <p class="text-muted mb-0">
                                Check campaign inputs or query mode.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    Campaign Inputs
                </div>

                <div class="card-body">
                    <div class="detail-list">
                        <div>
                            <span>Mode</span>
                            <strong><?php echo e(str_replace('_', ' ', $campaign->query_mode)); ?></strong>
                        </div>

                        <div>
                            <span>Provider</span>
                            <strong><?php echo e($campaign->source_engine ?: 'serpapi_google'); ?></strong>
                        </div>

                        <div>
                            <span>Max / Query</span>
                            <strong><?php echo e($campaign->max_results_per_query); ?></strong>
                        </div>

                        <div>
                            <span>Focus</span>
                            <strong><?php echo e(count($serviceFocus) ? implode(', ', $serviceFocus) : '—'); ?></strong>
                        </div>

                        <div>
                            <span>Audience</span>
                            <strong><?php echo e(count($targetAudience) ? implode(', ', $targetAudience) : '—'); ?></strong>
                        </div>

                        <div>
                            <span>Regions</span>
                            <strong><?php echo e(count($regions) ? implode(', ', $regions) : '—'); ?></strong>
                        </div>

                        <div>
                            <span>Cities</span>
                            <strong><?php echo e(count($cities) ? implode(', ', $cities) : '—'); ?></strong>
                        </div>
                    </div>

                    <?php if(count($excludeTerms)): ?>
                        <hr>
                        <div class="detail-label mb-2">Exclude Terms</div>
                        <div class="d-flex flex-wrap gap-2">
                            <?php $__currentLoopData = $excludeTerms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $term): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="badge text-bg-light border"><?php echo e($term); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>

                    <?php if($campaign->notes): ?>
                        <hr>
                        <div class="detail-label mb-2">Notes</div>
                        <div class="text-muted"><?php echo e($campaign->notes); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            Result Filters
        </div>

        <div class="card-body">
            <form method="get" action="<?php echo e(route('discovery.show', $campaign)); ?>" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label">Search Results</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="<?php echo e(request('search')); ?>"
                        placeholder="Company, domain, title, query, reason..."
                    >
                </div>

                <div class="col-lg-2">
                    <label class="form-label">Filter Status</label>
                    <select name="filter_status" class="form-select">
                        <option value="">All statuses</option>
                        <?php $__currentLoopData = $filterStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status); ?>" <?php if(request('filter_status') === $status): echo 'selected'; endif; ?>>
                                <?php echo e(str_replace('_', ' ', $status)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-lg-2">
                    <label class="form-label">Converted</label>
                    <select name="converted" class="form-select">
                        <option value="">All</option>
                        <option value="yes" <?php if(request('converted') === 'yes'): echo 'selected'; endif; ?>>Converted</option>
                        <option value="no" <?php if(request('converted') === 'no'): echo 'selected'; endif; ?>>Not Converted</option>
                    </select>
                </div>

                <div class="col-lg-1">
                    <label class="form-label">Min Score</label>
                    <input
                        type="number"
                        name="min_score"
                        class="form-control"
                        value="<?php echo e(request('min_score')); ?>"
                        min="0"
                        max="100"
                    >
                </div>

                <div class="col-lg-1">
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

                    <a href="<?php echo e(route('discovery.show', $campaign)); ?>" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card table-card mb-4">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <span>Discovery Results</span>
            <span class="small text-muted">
                Showing <?php echo e($results->firstItem() ?? 0); ?>–<?php echo e($results->lastItem() ?? 0); ?> of <?php echo e($results->total()); ?>

            </span>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 discovery-results-table">
                    <thead>
                        <tr>
                            <th><?php echo $sortLink('Score', 'relevance_score'); ?></th>
                            <th><?php echo $sortLink('Result', 'result_title'); ?></th>
                            <th><?php echo $sortLink('Domain', 'normalized_domain'); ?></th>
                            <th><?php echo $sortLink('Status', 'filter_status'); ?></th>
                            <th>Reason</th>
                            <th><?php echo $sortLink('Rank', 'rank'); ?></th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <span class="fit-score"><?php echo e($result->relevance_score ?? 0); ?></span>
                                </td>

                                <td>
                                    <div class="fw-bold result-title">
                                        <a href="<?php echo e($result->result_url); ?>" target="_blank" class="text-decoration-none">
                                            <?php echo e($result->result_title ?: 'Untitled result'); ?>

                                            <i class="bi bi-box-arrow-up-right ms-1"></i>
                                        </a>
                                    </div>

                                    <?php if($result->snippet): ?>
                                        <div class="small text-muted result-snippet">
                                            <?php echo e($result->snippet); ?>

                                        </div>
                                    <?php endif; ?>

                                    <?php if($result->source_query): ?>
                                        <div class="small text-muted mt-1">
                                            Query: <code><?php echo e(\Illuminate\Support\Str::limit($result->source_query, 120)); ?></code>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="fw-semibold"><?php echo e($result->normalized_domain); ?></div>
                                    <div class="small text-muted">
                                        <?php echo e($result->company_name_guess ?: 'No company guess'); ?>

                                    </div>
                                </td>

                                <td>
                                    <span class="status-badge <?php echo e($result->filter_status); ?>">
                                        <?php echo e(str_replace('_', ' ', $result->filter_status)); ?>

                                    </span>

                                    <?php if($result->converted_to_prospect): ?>
                                        <div class="mt-2">
                                            <span class="badge text-bg-success">Converted</span>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="small text-muted result-reason">
                                        <?php echo e($result->reason ?: '—'); ?>

                                    </div>
                                </td>

                                <td>
                                    <?php echo e($result->rank ?: '—'); ?>

                                </td>

                                <td class="text-end">
									<div class="d-inline-flex flex-wrap justify-content-end gap-2">
										<a href="<?php echo e(route('discovery-results.show', $result)); ?>" class="btn btn-sm btn-outline-secondary">
											Details
										</a>

										<?php if($result->converted_to_prospect && $result->prospect): ?>
											<a href="<?php echo e(route('prospects.show', $result->prospect)); ?>" class="btn btn-sm btn-outline-primary">
												Prospect
											</a>
										<?php elseif($result->filter_status === 'manual_rejected'): ?>
											<button class="btn btn-sm btn-outline-danger" disabled>
												Rejected
											</button>
										<?php elseif(in_array($result->filter_status, ['strong_candidate', 'possible_candidate'], true)): ?>
											<form method="post" action="<?php echo e(route('discovery-results.convert', $result)); ?>" class="m-0">
												<?php echo csrf_field(); ?>
												<button class="btn btn-sm btn-primary">
													Convert
												</button>
											</form>

											<form method="post" action="<?php echo e(route('discovery-results.reject', $result)); ?>" class="m-0">
												<?php echo csrf_field(); ?>
												<input type="hidden" name="rejection_reason" value="Rejected from discovery campaign results list.">
												<button
													class="btn btn-sm btn-outline-danger"
													onclick="return confirm('Reject this discovery lead?')"
												>
													Reject
												</button>
											</form>
										<?php else: ?>
											<button class="btn btn-sm btn-outline-secondary" disabled>
												Rejected
											</button>
										<?php endif; ?>
									</div>
								</td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-search"></i>
                                        </div>

                                        <h5 class="fw-bold mb-1">No discovery results found</h5>

                                        <p class="text-muted mb-3">
                                            Run the campaign or adjust filters.
                                        </p>

                                        <?php if(!in_array($campaign->status, ['queued', 'running'], true)): ?>
                                            <form method="post" action="<?php echo e(route('discovery.run', $campaign)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button class="btn btn-primary">
                                                    <i class="bi bi-play-fill me-1"></i>
                                                    Run Campaign
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <?php echo e($results->links()); ?>

    </div>

    <div class="card">
        <div class="card-header">
            Recent Discovery Logs
        </div>

        <div class="card-body">
            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="mini-record">
                    <div class="fw-bold"><?php echo e($log->step_name ?? 'Discovery Log'); ?></div>

                    <?php if($log->message ?? false): ?>
                        <div class="small text-muted"><?php echo e($log->message); ?></div>
                    <?php endif; ?>

                    <div class="small text-muted">
                        <?php echo e($log->created_at?->diffForHumans()); ?>

                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-muted">No discovery logs yet.</div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\ai-sales-agent\resources\views/discovery/show.blade.php ENDPATH**/ ?>