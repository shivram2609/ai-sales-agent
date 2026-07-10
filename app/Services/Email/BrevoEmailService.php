<?php

namespace App\Services\Email;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class BrevoEmailService
{
    private string $baseUrl = 'https://api.brevo.com/v3';

    public function sendTransactionalEmail(
        string $toEmail,
        string $subject,
        string $htmlContent,
        ?string $textContent = null,
        array $tags = [],
        ?string $toName = null,
        ?string $fromEmail = null,
        ?string $fromName = null
    ): array {
        $apiKey = (string) config('services.brevo.key');
        $senderEmail = $fromEmail ?: (string) config('services.brevo.sender_email');
        $senderName = $fromName ?: (string) config('services.brevo.sender_name', 'Zestminds');

        if (! $apiKey) {
            throw new RuntimeException('Brevo API key is not configured.');
        }

        if (! $senderEmail) {
            throw new RuntimeException('Brevo sender email is not configured.');
        }

        $payload = [
            'sender' => [
                'name' => $senderName,
                'email' => $senderEmail,
            ],
            'to' => [
                array_filter([
                    'email' => $toEmail,
                    'name' => $toName,
                ]),
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ];

        if ($textContent) {
            $payload['textContent'] = $textContent;
        }

        if (! empty($tags)) {
            $payload['tags'] = $tags;
        }

        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])
            ->timeout(60)
            ->asJson()
            ->post($this->baseUrl.'/smtp/email', $payload);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Brevo email send failed. HTTP '.$response->status().': '.$response->body()
            );
        }

        return $response->json() ?? [];
    }
}
