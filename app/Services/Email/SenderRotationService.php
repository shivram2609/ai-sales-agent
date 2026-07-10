<?php

namespace App\Services\Email;

use Illuminate\Support\Facades\Cache;

class SenderRotationService
{
    private const CACHE_KEY = 'sending:sender_rotation_index';

    /**
     * Returns ['email' => ..., 'name' => ...] for the next sender to use,
     * rotating fairly across whatever is configured in sending.senders.
     * Falls back to the single default Brevo sender if none are configured.
     */
    public function next(): array
    {
        $senders = config('sending.senders', []);

        if (empty($senders)) {
            return [
                'email' => config('services.brevo.sender_email'),
                'name' => config('services.brevo.sender_name', 'Zestminds'),
            ];
        }

        if (count($senders) === 1) {
            return $senders[0];
        }

        // Cache-backed counter so rotation is fair across scheduler runs,
        // not just within a single run.
        $index = Cache::get(self::CACHE_KEY, 0);
        $sender = $senders[$index % count($senders)];
        Cache::forever(self::CACHE_KEY, $index + 1);

        return $sender;
    }
}
