<?php

namespace App\Services;

use App\Models\LeadGetterResult;
use App\Models\LeadGetterTask;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LeadGetterService
{
    /**
     * Fetch leads from the configured API provider.
     */
    public function fetchLeads(LeadGetterTask $task): int
    {
        return match ($task->api_provider) {
            'serpapi' => $this->fetchFromSerpApi($task),
            default => throw new \Exception("Unsupported API provider: {$task->api_provider}"),
        };
    }

    /**
     * Fetch leads from SerpApi Google Maps API.
     */
    protected function fetchFromSerpApi(LeadGetterTask $task): int
    {
        $apiKey = Setting::getValue('serpapi_key');

        if (empty($apiKey)) {
            throw new \Exception('SerpApi API key is not configured. Please set it in Settings → Lead Getter.');
        }

        $results = [];
        $start = 0;
        $maxPages = 3; // Fetch up to 3 pages of results (60 results)

        for ($page = 0; $page < $maxPages; $page++) {
            $response = Http::timeout(30)->get('https://serpapi.com/search.json', [
                'engine' => 'google_maps',
                'q' => $task->query,
                'll' => null, // We use text-based location
                'type' => 'search',
                'api_key' => $apiKey,
                'start' => $start,
                'hl' => 'en',
                'google_domain' => 'google.com',
            ]);

            // Also try with location parameter for better results
            if ($page === 0) {
                $response = Http::timeout(30)->get('https://serpapi.com/search.json', [
                    'engine' => 'google_maps',
                    'q' => $task->query,
                    'location' => $task->location,
                    'type' => 'search',
                    'api_key' => $apiKey,
                    'start' => $start,
                    'hl' => 'en',
                ]);
            }

            if (!$response->successful()) {
                Log::error('SerpApi request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'task_id' => $task->id,
                ]);

                if ($page === 0) {
                    throw new \Exception('SerpApi request failed: ' . $response->body());
                }
                break;
            }

            $data = $response->json();
            $localResults = $data['local_results'] ?? $data['place_results'] ?? [];

            if (empty($localResults)) {
                break;
            }

            foreach ($localResults as $item) {
                $results[] = $this->parseSerpApiResult($item, $task);
            }

            // Check if there are more pages
            if (!isset($data['serpapi_pagination']['next'])) {
                break;
            }

            $start += 20;
        }

        // Bulk insert results
        foreach ($results as $result) {
            LeadGetterResult::create($result);
        }

        return count($results);
    }

    /**
     * Parse a single SerpApi result into our format.
     */
    protected function parseSerpApiResult(array $item, LeadGetterTask $task): array
    {
        $phone = $item['phone'] ?? null;
        $website = $item['website'] ?? null;
        $address = $item['address'] ?? null;

        // Try to extract from extensions or service_options
        if (!$phone && isset($item['extensions'])) {
            foreach ($item['extensions'] as $ext) {
                if (preg_match('/^[\+\d\s\-\(\)]+$/', $ext)) {
                    $phone = $ext;
                    break;
                }
            }
        }

        return [
            'lead_getter_task_id' => $task->id,
            'title' => $item['title'] ?? 'Unknown Business',
            'company' => $item['title'] ?? null,
            'contact_name' => null, // Google Maps doesn't provide contact names
            'email' => null, // Google Maps doesn't provide emails directly
            'phone' => $phone,
            'website' => $website,
            'address' => $address ?? ($item['snippet'] ?? null),
            'rating' => $item['rating'] ?? null,
            'reviews_count' => $item['reviews'] ?? null,
            'category' => $item['type'] ?? ($item['types'][0] ?? null),
            'raw_data' => $item,
            'status' => 'new',
        ];
    }
}
