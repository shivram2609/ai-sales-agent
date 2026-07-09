<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-title">AI Sales Agent</div>
        <div class="sidebar-brand-subtitle">Zestminds Outreach OS</div>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ url('/prospects') }}" class="{{ request()->is('prospects*') ? 'active' : '' }}">
            <i class="bi bi-building"></i>
            <span>Prospects</span>
        </a>

        <a href="{{ url('/discovery') }}" class="{{ request()->is('discovery*') ? 'active' : '' }}">
            <i class="bi bi-search"></i>
            <span>Discovery</span>
        </a>

        <a href="{{ url('/review-queue') }}" class="{{ request()->is('review-queue*') ? 'active' : '' }}">
            <i class="bi bi-check2-square"></i>
            <span>Review Queue</span>
        </a>

        <a href="{{ url('/outreach-drafts') }}" class="{{ request()->is('outreach-drafts*') ? 'active' : '' }}">
            <i class="bi bi-envelope-paper"></i>
            <span>Drafts</span>
        </a>

        <a href="{{ url('/campaigns') }}" class="{{ request()->is('campaigns*') ? 'active' : '' }}">
			<i class="bi bi-megaphone"></i>
			<span>Campaigns</span>
		</a>
		
		<a href="{{ url('/outreach-queue') }}" class="{{ request()->is('outreach-queue*') ? 'active' : '' }}">
			<i class="bi bi-list-check"></i>
			<span>Outreach Queue</span>
		</a>
		
		<a href="{{ url('/sending-queue') }}" class="{{ request()->is('sending-queue*') ? 'active' : '' }}">
			<i class="bi bi-envelope-check"></i>
			<span>Sending Queue</span>
		</a>
		
		
    </nav>

    <div class="sidebar-footer">
        <strong>Rule:</strong><br>
        AI prepares. Human approves. No auto-spam.
        <hr class="my-2">
        <div class="d-flex justify-content-between align-items-center">
            <span class="small text-muted">{{ auth()->user()->name ?? '' }}</span>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-link btn-sm p-0">Logout</button>
            </form>
        </div>
    </div>
</aside>