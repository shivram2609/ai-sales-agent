<?php

namespace App\Jobs;

use App\Models\DiscoveryCampaign;
use App\Services\Discovery\DiscoveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class RunDiscoveryCampaignJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 600;

    public int $tries = 1;

    public function __construct(
        public int $campaignId
    ) {
    }

    public function handle(DiscoveryService $discoveryService): void
    {
        $campaign = DiscoveryCampaign::find($this->campaignId);

        if (! $campaign) {
            Log::warning('Discovery campaign job skipped because campaign was not found.', [
                'campaign_id' => $this->campaignId,
            ]);

            return;
        }

        if ($campaign->status === 'completed') {
            return;
        }

        $campaign->update([
            'status' => 'running',
        ]);

        try {
            $discoveryService->runCampaign($campaign);

            $campaign->refresh();

            if ($campaign->status !== 'failed') {
                $campaign->update([
                    'status' => 'completed',
                ]);
            }
        } catch (Throwable $e) {
            $campaign->update([
                'status' => 'failed',
            ]);

            Log::error('Discovery campaign job failed.', [
                'campaign_id' => $this->campaignId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        DiscoveryCampaign::where('id', $this->campaignId)->update([
            'status' => 'failed',
        ]);

        Log::error('Discovery campaign job permanently failed.', [
            'campaign_id' => $this->campaignId,
            'error' => $exception->getMessage(),
        ]);
    }
}