<?php

namespace App\Http\Controllers;

use App\Models\CampaignMember;
use Illuminate\Http\Request;

class OutreachQueueController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->startOfDay();

        $closedStatuses = [
            CampaignMember::STATUS_REPLIED,
            CampaignMember::STATUS_NOT_INTERESTED,
            CampaignMember::STATUS_BOUNCED,
            CampaignMember::STATUS_PAUSED,
        ];

        $followUpsDue = CampaignMember::query()
            ->with(['campaign', 'prospect', 'contact', 'draft'])
            ->whereNotIn('status', $closedStatuses)
            ->where(function ($query) use ($today) {
                $query->where(function ($q) use ($today) {
                    $q->whereNotNull('follow_up_1_due_at')
                        ->whereNull('follow_up_1_sent_at')
                        ->whereDate('follow_up_1_due_at', '<=', $today);
                })
                ->orWhere(function ($q) use ($today) {
                    $q->whereNotNull('follow_up_2_due_at')
                        ->whereNull('follow_up_2_sent_at')
                        ->whereDate('follow_up_2_due_at', '<=', $today);
                });
            })
            ->orderByRaw('COALESCE(follow_up_1_due_at, follow_up_2_due_at) asc')
            ->limit(100)
            ->get();

        $readyForFirstTouch = CampaignMember::query()
            ->with(['campaign', 'prospect', 'contact', 'draft'])
            ->whereNotIn('status', $closedStatuses)
            ->whereNotNull('outreach_draft_id')
            ->whereNull('first_touch_at')
            ->whereIn('status', [
                CampaignMember::STATUS_DRAFT_READY,
                CampaignMember::STATUS_APPROVED,
            ])
            ->latest()
            ->limit(100)
            ->get();

        $missingDrafts = CampaignMember::query()
            ->with(['campaign', 'prospect', 'contact'])
            ->whereNotIn('status', $closedStatuses)
            ->whereNull('outreach_draft_id')
            ->latest()
            ->limit(100)
            ->get();

        $recentReplies = CampaignMember::query()
            ->with(['campaign', 'prospect', 'contact', 'draft'])
            ->where(function ($query) {
                $query->where('status', CampaignMember::STATUS_REPLIED)
                    ->orWhereNotNull('replied_at');
            })
            ->latest('replied_at')
            ->latest()
            ->limit(25)
            ->get();

        return view('outreach-queue.index', [
            'followUpsDue' => $followUpsDue,
            'readyForFirstTouch' => $readyForFirstTouch,
            'missingDrafts' => $missingDrafts,
            'recentReplies' => $recentReplies,
            'today' => $today,
        ]);
    }
}