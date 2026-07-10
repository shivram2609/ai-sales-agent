<?php

return [
    // Days after the previous email before the next one in the sequence
    // is queued, for campaign members on the "full_sequence" mode.
    'follow_up_1_days' => env('SENDING_FOLLOWUP_1_DAYS', 3),
    'follow_up_2_days' => env('SENDING_FOLLOWUP_2_DAYS', 6),

    // How long to wait between two actual sends, system-wide (not per
    // contact - across ALL outbound emails). A random value between min
    // and max is used each time so sends don't happen on a suspiciously
    // exact fixed interval. Values are in seconds; 120-180 = 2-3 minutes.
    'min_gap_seconds' => env('SENDING_MIN_GAP_SECONDS', 120),
    'max_gap_seconds' => env('SENDING_MAX_GAP_SECONDS', 180),

    // Multiple "from" addresses to rotate between, so outreach isn't all
    // sent from a single address. Add as many as you have verified in
    // Brevo. Falls back to a single sender (services.brevo.sender_email)
    // if this list is empty.
    'senders' => array_values(array_filter([
        env('BREVO_SENDER_EMAIL_1') ? [
            'email' => env('BREVO_SENDER_EMAIL_1'),
            'name' => env('BREVO_SENDER_NAME_1', env('BREVO_SENDER_NAME', 'ABC')),
        ] : null,
        env('BREVO_SENDER_EMAIL_2') ? [
            'email' => env('BREVO_SEDER_EMAIL_2'),
            'name' => env('BREVO_SENDER_NAME_2', env('BREVO_SENDER_NAME', 'ABC')),
        ] : null,
        env('BREVO_SENDER_EMAIL_3') ? [
            'email' => env('BREVO_SENDER_EMAIL_3'),
            'name' => env('BREVO_SENDER_NAME_3', env('BREVO_SENDER_NAME', 'ABC')),
        ] : null,
        env('BREVO_SENDER_EMAIL_4') ? [
            'email' => env('BREVO_SENDER_EMAIL_4'),
            'name' => env('BREVO_SENDER_NAME_4', env('BREVO_SENDER_NAME', 'ABC')),
        ] : null,
    ])),
];
