@php
    $closedStatuses = [
        \App\Models\CampaignMember::STATUS_REPLIED,
        \App\Models\CampaignMember::STATUS_NOT_INTERESTED,
        \App\Models\CampaignMember::STATUS_BOUNCED,
        \App\Models\CampaignMember::STATUS_PAUSED,
    ];

    $isClosed = in_array($member->status, $closedStatuses, true);

    $canMarkSent =
        ! $isClosed &&
        $member->draft &&
        ! $member->first_touch_at;

    $canMarkFu1Sent =
        ! $isClosed &&
        $member->first_touch_at &&
        $member->follow_up_1_due_at &&
        ! $member->follow_up_1_sent_at;

    $canMarkFu2Sent =
        ! $isClosed &&
        $member->follow_up_1_sent_at &&
        $member->follow_up_2_due_at &&
        ! $member->follow_up_2_sent_at;

    $canClose =
        ! $isClosed;

    $hasAnyAction =
        $canMarkSent ||
        $canMarkFu1Sent ||
        $canMarkFu2Sent ||
        $canClose;
@endphp

@if($hasAnyAction)
    <div class="d-flex flex-wrap gap-2">
        @if($canMarkSent)
            <form method="post" action="{{ route('campaign-members.mark-sent', $member) }}">
                @csrf
                <button
                    type="submit"
                    class="btn btn-sm btn-outline-success"
                    onclick="return confirm('Mark first email as sent manually?')"
                >
                    Mark Sent
                </button>
            </form>
        @endif

        @if($canMarkFu1Sent)
            <form method="post" action="{{ route('campaign-members.mark-follow-up-1-sent', $member) }}">
                @csrf
                <button
                    type="submit"
                    class="btn btn-sm btn-outline-primary"
                    onclick="return confirm('Mark follow-up 1 as sent?')"
                >
                    FU1 Sent
                </button>
            </form>
        @endif

        @if($canMarkFu2Sent)
            <form method="post" action="{{ route('campaign-members.mark-follow-up-2-sent', $member) }}">
                @csrf
                <button
                    type="submit"
                    class="btn btn-sm btn-outline-primary"
                    onclick="return confirm('Mark follow-up 2 as sent?')"
                >
                    FU2 Sent
                </button>
            </form>
        @endif

        @if($canClose)
            <form method="post" action="{{ route('campaign-members.mark-replied', $member) }}">
                @csrf
                <button
                    type="submit"
                    class="btn btn-sm btn-outline-success"
                    onclick="return confirm('Mark this contact as replied?')"
                >
                    Replied
                </button>
            </form>

            <form method="post" action="{{ route('campaign-members.mark-not-interested', $member) }}">
                @csrf
                <button
                    type="submit"
                    class="btn btn-sm btn-outline-dark"
                    onclick="return confirm('Mark this contact as not interested?')"
                >
                    Not Interested
                </button>
            </form>

            <form method="post" action="{{ route('campaign-members.mark-bounced', $member) }}">
                @csrf
                <button
                    type="submit"
                    class="btn btn-sm btn-outline-danger"
                    onclick="return confirm('Mark this contact as bounced?')"
                >
                    Bounced
                </button>
            </form>
        @endif
    </div>
@else
    <span class="text-muted small">No active actions</span>
@endif