<?php

namespace App\Services\AI;

class DraftTextSanitizerService
{
    public function sanitizeString(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $text = (string) $text;

        $replacements = [
            '—' => '-',
            '–' => '-',
            '“' => '"',
            '”' => '"',
            '‘' => "'",
            '’' => "'",
            '…' => '...',
            '•' => '-',
            '→' => '-',
            '⇒' => '-',
            '↳' => '-',
            '&amp;' => 'and',
            '&' => 'and',
        ];

        $text = str_replace(array_keys($replacements), array_values($replacements), $text);

        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text);
    }

    public function sanitizeArray(array $items): array
    {
        return collect($items)
            ->map(fn ($item) => $this->sanitizeString((string) $item))
            ->filter()
            ->values()
            ->all();
    }
}