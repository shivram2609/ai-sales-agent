<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">@yield('page_heading', View::yieldContent('title'))</h1>
        @hasSection('page_subtitle')
            <div class="text-muted">@yield('page_subtitle')</div>
        @endif
    </div>

    @hasSection('page_actions')
        <div class="d-flex flex-wrap gap-2">
            @yield('page_actions')
        </div>
    @endif
</div>