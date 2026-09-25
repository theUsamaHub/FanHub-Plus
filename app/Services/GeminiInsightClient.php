<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Optional Gemini enrichment for dashboard insights.
 * Key lives in env (GEMINI_API_KEY) — never hardcode or expose to the browser.
 * Falls back silently when the key is missing or the API fails.
 */
class GeminiInsightClient
{
    public function enabled(): bool
    {
        return (bool) config('services.gemini.key');
    }

    /**
     * @param  array<int, array{tone: string, title: string, body: string}>  $metrics
     * @return array<int, array{tone: string, title: string, body: string}>
     */
    public function enrich(array $metrics): array
    {
        if (! $this->enabled() || $metrics === []) {
            return $metrics;
        }

        $model = config('services.gemini.model', 'gemini-3.5-flash');
        $key = config('services.gemini.key');

        $prompt = 'You are a concise product analyst for a fandom admin dashboard. '
            .'Given these JSON metrics, return STRICT JSON: an array of 2 to 4 objects '
            .'with keys "tone" (success|info|warning|danger), "title" (max 8 words), '
            .'"body" (max 18 words). Plain text only, no markdown. '
            ."Metrics: ".json_encode($metrics);

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $key])
                ->acceptJson()
                ->timeout(8)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'contents' => [[
                        'parts' => [['text' => $prompt]],
                    ]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature' => 0.4,
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('Gemini insight call failed', ['status' => $response->status()]);

                return $metrics;
            }

            $text = $response->json('candidates.0.content.parts.0.text') ?? '';
            $decoded = json_decode($text, true);

            if (! is_array($decoded) || $decoded === []) {
                return $metrics;
            }

            $clean = [];
            foreach ($decoded as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $clean[] = [
                    'tone' => in_array($row['tone'] ?? '', ['success', 'info', 'warning', 'danger'], true)
                        ? $row['tone']
                        : 'info',
                    'title' => (string) ($row['title'] ?? ''),
                    'body' => (string) ($row['body'] ?? ''),
                ];
            }

            return $clean !== [] ? $clean : $metrics;
        } catch (\Throwable $e) {
            Log::warning('Gemini insight exception: '.$e->getMessage());

            return $metrics;
        }
    }
}
