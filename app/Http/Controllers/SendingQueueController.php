<?php

namespace App\Http\Controllers;

use App\Models\OutboundEmailJob;
use App\Services\Sending\ControlledSendingService;
use Illuminate\Http\Request;
use Throwable;

class SendingQueueController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');

        $jobs = OutboundEmailJob::query()
            ->with(['campaign', 'prospect', 'contact', 'draft', 'campaignMember'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByRaw('scheduled_at is null')
            ->orderBy('scheduled_at')
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('sending-queue.index', [
            'jobs' => $jobs,
            'statuses' => OutboundEmailJob::statuses(),
            'types' => OutboundEmailJob::types(),
            'selectedStatus' => $status,
        ]);
    }

    public function cancel(Request $request, OutboundEmailJob $outboundEmailJob, ControlledSendingService $service)
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string'],
        ]);

        try {
            $service->cancelJob($outboundEmailJob, $validated['reason'] ?? null);

            return back()->with('success', 'Queued email job cancelled.');
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', $e->getMessage());
        }
    }
}