<?php

namespace App\Http\Controllers;

use App\Models\CampaignMember;
use App\Services\Sending\ControlledSendingService;
use Illuminate\Http\Request;
use Throwable;

class CampaignMemberSendingController extends Controller
{
    public function edit(CampaignMember $campaignMember)
    {
        $campaignMember->load([
            'campaign',
            'prospect',
            'contact',
            'draft',
            'outboundEmailJobs' => fn ($query) => $query->latest(),
        ]);

        return view('campaign-members.sending-control', [
            'member' => $campaignMember,
            'sequenceModes' => CampaignMember::sequenceModes(),
        ]);
    }

    public function approve(Request $request, CampaignMember $campaignMember, ControlledSendingService $service)
    {
        $validated = $request->validate([
            'sequence_mode' => ['required', 'string'],
            'scheduled_at' => ['nullable', 'date'],
            'approval_notes' => ['nullable', 'string'],
        ]);

        try {
            $service->approveAndQueue(
                $campaignMember,
                $validated['sequence_mode'],
                $validated['scheduled_at'] ?? null,
                $validated['approval_notes'] ?? null
            );

            return redirect()
                ->route('campaign-members.sending-control', $campaignMember)
                ->with('success', 'Sending control updated and queue prepared.');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function pause(CampaignMember $campaignMember, ControlledSendingService $service)
    {
        try {
            $service->pause($campaignMember);

            return back()->with('success', 'Sequence paused.');
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', $e->getMessage());
        }
    }

    public function resume(CampaignMember $campaignMember, ControlledSendingService $service)
    {
        try {
            $service->resume($campaignMember);

            return back()->with('success', 'Sequence resumed.');
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', $e->getMessage());
        }
    }

    public function stop(Request $request, CampaignMember $campaignMember, ControlledSendingService $service)
    {
        $validated = $request->validate([
            'stop_reason' => ['nullable', 'string'],
        ]);

        try {
            $service->stop($campaignMember, $validated['stop_reason'] ?? null);

            return back()->with('success', 'Sequence stopped and queued emails cancelled.');
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', $e->getMessage());
        }
    }
}