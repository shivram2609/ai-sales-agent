@php
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
@endphp

<div class="card table-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{!! sort_link('Company', 'company_name', $sort, $direction) !!}</th>
                        <th>{!! sort_link('Domain', 'domain', $sort, $direction) !!}</th>
                        <th>{!! sort_link('Status', 'status', $sort, $direction) !!}</th>
                        <th>{!! sort_link('Fit', 'fit_score', $sort, $direction) !!}</th>
                        <th>{!! sort_link('Source', 'source', $sort, $direction) !!}</th>
                        <th>{!! sort_link('Updated', 'updated_at', $sort, $direction) !!}</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($prospects as $prospect)
                        <tr>
                            <td>
                                <div class="fw-bold">
                                    {{ $prospect->company_name ?: 'Unknown Company' }}
                                </div>
                                <div class="small-text">
                                    {{ $prospect->category_guess ?: 'No category yet' }}
                                </div>
                            </td>

                            <td>
                                <a href="{{ $prospect->website_url }}" target="_blank" class="text-decoration-none fw-semibold">
                                    {{ $prospect->domain }}
                                </a>
                            </td>

                            <td>
                                <span class="status-badge {{ $prospect->status }}">
                                    {{ str_replace('_', ' ', $prospect->status) }}
                                </span>
                            </td>

                            <td>
                                <span class="fit-score">
                                    {{ $prospect->fit_score ?? '—' }}
                                </span>
                            </td>

                            <td>
                                {{ $prospect->source ?: '—' }}
                            </td>

                            <td>
                                <span class="small-text">
                                    {{ $prospect->updated_at?->diffForHumans() }}
                                </span>
                            </td>

                            <td class="text-end">
                                <a href="{{ route('prospects.show', $prospect) }}" class="btn btn-sm btn-outline-primary">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
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
                                    <a href="{{ route('prospects.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i>
                                        Add Prospect
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>