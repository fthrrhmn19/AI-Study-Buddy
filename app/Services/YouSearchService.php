<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class YouSearchService
{
    private string $apiKey;
    private string $baseUrl = 'https://ydc-index.io/v1';

    public function __construct()
    {
        $this->apiKey = (string) config('services.you.key');
    }

    /**
     * Search the web via You.com API and return structured snippets.
     *
     * @return array{snippets: string, sources: array<int, array{title: string, url: string, description: string}>}
     */
    public function search(string $query, int $count = 5): array
    {
        if (blank($this->apiKey)) {
            return ['snippets' => '', 'sources' => []];
        }

        try {
            $response = Http::timeout(12)
                ->withHeaders(['X-API-Key' => $this->apiKey])
                ->get($this->baseUrl . '/search', [
                    'query' => $query,
                    'count' => $count,
                ]);

            if ($response->failed()) {
                Log::warning('You.com API error: ' . $response->status());
                return ['snippets' => '', 'sources' => []];
            }

            $json = $response->json();
            $webResults = data_get($json, 'results.web', []);
            $sources = [];
            $allSnippets = [];

            foreach ($webResults as $result) {
                $title = data_get($result, 'title', '');
                $url = data_get($result, 'url', '');
                $description = data_get($result, 'description', '');
                $snippets = data_get($result, 'snippets', []);

                $sources[] = [
                    'title' => $title,
                    'url' => $url,
                    'description' => $description,
                ];

                foreach ($snippets as $snippet) {
                    $allSnippets[] = $snippet;
                }
            }

            // Combine snippets into a single context string (max ~3000 chars)
            $combinedText = implode("\n\n", array_slice($allSnippets, 0, 6));
            if (strlen($combinedText) > 3000) {
                $combinedText = substr($combinedText, 0, 3000) . '...';
            }

            return [
                'snippets' => $combinedText,
                'sources' => array_slice($sources, 0, 5),
            ];
        } catch (Throwable $e) {
            Log::warning('You.com search failed: ' . $e->getMessage());
            return ['snippets' => '', 'sources' => []];
        }
    }

    /**
     * Build a context-enriched query from title + subject.
     */
    public function buildSearchQuery(string $title, string $subject): string
    {
        return trim("{$title} {$subject} materi kuliah penjelasan");
    }

    /**
     * Check if API is configured.
     */
    public function isConfigured(): bool
    {
        return !blank($this->apiKey);
    }
}
