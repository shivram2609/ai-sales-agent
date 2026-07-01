<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiClientService
{
    public function structuredResponse(
        string $systemPrompt,
        string $userPrompt,
        array $jsonSchema,
        string $schemaName,
        int $maxOutputTokens = 2500
    ): array {
        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model');
        $timeout = config('services.openai.timeout', 45);

        if (! $apiKey) {
            throw new RuntimeException('OPENAI_API_KEY is missing in .env');
        }

        if (! $model) {
            throw new RuntimeException('OPENAI_MODEL is missing in .env');
        }

        $payload = [
            'model' => $model,
            'instructions' => $systemPrompt,
            'input' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => $userPrompt,
                        ],
                    ],
                ],
            ],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => $schemaName,
                    'strict' => true,
                    'schema' => $jsonSchema,
                ],
            ],
            'max_output_tokens' => $maxOutputTokens,
            'store' => false,
        ];

        $response = Http::timeout($timeout)
            ->withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->post('https://api.openai.com/v1/responses', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('OpenAI API request failed: ' . $response->body());
        }

        $data = $response->json();
        $text = $this->extractOutputText($data);

        if (! $text) {
            throw new RuntimeException('OpenAI response did not include output text.');
        }

        $decoded = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('OpenAI response was not valid JSON: ' . json_last_error_msg());
        }

        return [
            'model' => $model,
            'raw' => $data,
            'json' => $decoded,
            'output_text' => $text,
        ];
    }

    private function extractOutputText(array $data): ?string
    {
        if (! empty($data['output_text']) && is_string($data['output_text'])) {
            return $data['output_text'];
        }

        foreach ($data['output'] ?? [] as $outputItem) {
            foreach ($outputItem['content'] ?? [] as $content) {
                if (($content['type'] ?? null) === 'output_text' && isset($content['text'])) {
                    return $content['text'];
                }
            }
        }

        return null;
    }
}