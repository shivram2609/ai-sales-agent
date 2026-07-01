<?php

namespace App\Http\Controllers;

use App\Models\DiscoveryResult;
use Illuminate\Http\Request;

class DiscoveryResultController extends Controller
{
    public function show(DiscoveryResult $discoveryResult)
    {
        $discoveryResult->load([
            'campaign',
            'prospect',
        ]);

        return view('discovery-results.show', [
            'result' => $discoveryResult,
        ]);
    }

    public function reject(Request $request, DiscoveryResult $discoveryResult)
    {
        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($discoveryResult->converted_to_prospect && $discoveryResult->prospect_id) {
            return back()->with('error', 'This lead is already converted to a prospect. Open the prospect if you want to review it.');
        }

        $reason = trim($validated['rejection_reason'] ?? '');

        if ($reason === '') {
            $reason = 'Manually rejected by user.';
        }

        $previousReason = $discoveryResult->reason;

        $discoveryResult->update([
            'filter_status' => 'manual_rejected',
            'relevance_score' => 0,
            'reason' => trim('Manual rejection: ' . $reason . ($previousReason ? "\n\nPrevious reason: " . $previousReason : '')),
        ]);

        return back()->with('success', 'Discovery lead rejected successfully.');
    }
}