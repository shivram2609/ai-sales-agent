<?php

return [
    // Days after the previous email before the next one in the sequence
    // is queued, for campaign members on the "full_sequence" mode.
    'follow_up_1_days' => env('SENDING_FOLLOWUP_1_DAYS', 3),
    'follow_up_2_days' => env('SENDING_FOLLOWUP_2_DAYS', 6),

    // Safety cap: how many jobs the queue processor sends in one run.
    // Keeps a single scheduler tick from blasting out a huge batch if the
    // worker was down for a while.
    'max_per_run' => env('SENDING_MAX_PER_RUN', 25),
];
