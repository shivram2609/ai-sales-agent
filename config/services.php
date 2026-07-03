<?php

return [
    'serpapi' => [
        'key' => env('SERPAPI_API_KEY'),
    ],
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],
    'brevo' => [
        'key' => env('BREVO_API_KEY'),
        'webhook_secret' => env('BREVO_WEBHOOK_SECRET'),
        'sender_email' => env('BREVO_SENDER_EMAIL'),
        'sender_name' => env('BREVO_SENDER_NAME', 'Zestminds'),
    ],
	
	'openai' => [
		'api_key' => env('OPENAI_API_KEY'),
		'model' => env('OPENAI_MODEL', 'gpt-5.4-mini'),
		'timeout' => (int) env('OPENAI_TIMEOUT', 45),
		'draft_prompt_version' => env('OPENAI_DRAFT_PROMPT_VERSION', 'v1'),
	],
];
