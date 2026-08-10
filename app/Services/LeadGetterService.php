<?php

namespace App\Services;

use App\Models\Lead;
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

        $filters = $task->filters ?? [];
        $extractEmails = $filters['extract_emails'] ?? true;
        $uniqueOnly = $filters['unique_only'] ?? true;
        $websiteFilter = $filters['website_filter'] ?? 'all';
        $phoneFilter = $filters['phone_filter'] ?? 'all';

        $results = [];
        $start = 0;
        $maxPages = 3; // Fetch up to 3 pages of results (60 results)

        for ($page = 0; $page < $maxPages; $page++) {
            // Combine query and location for a natural search (e.g., "Web design in New York")
            $searchQuery = "{$task->query} in {$task->location}";

            $response = Http::timeout(30)->get('https://serpapi.com/search.json', [
                'engine' => 'google_maps',
                'q' => $searchQuery,
                'type' => 'search',
                'api_key' => $apiKey,
                'start' => $start,
                'hl' => 'en',
                'google_domain' => 'google.com',
            ]);

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
                $parsed = $this->parseSerpApiResult($item, $task);

                // Filter 1: Website Filter
                if ($websiteFilter === 'no_website' && !empty($parsed['website'])) {
                    continue;
                }
                if ($websiteFilter === 'has_website' && empty($parsed['website'])) {
                    continue;
                }

                // Filter 2: Phone Filter
                if ($phoneFilter === 'require_phone' && empty($parsed['phone'])) {
                    continue;
                }

                // Filter 3: Unique Records Filter (Deduplication)
                if ($uniqueOnly) {
                    $isDuplicate = LeadGetterResult::whereHas('task', function ($q) use ($task) {
                        $q->where('lead_getter_group_id', $task->lead_getter_group_id);
                    })->where(function ($q) use ($parsed) {
                        $q->where('title', $parsed['title']);
                        if (!empty($parsed['website'])) {
                            $q->orWhere('website', $parsed['website']);
                        }
                    })->exists();

                    if (!$isDuplicate) {
                        // Also check CRM leads
                        $isDuplicate = Lead::where('title', $parsed['title'])
                            ->orWhere('company', $parsed['title'])
                            ->exists();
                    }

                    if ($isDuplicate) {
                        continue;
                    }
                }

                // Feature 4: Website Email Scraping
                if ($extractEmails && !empty($parsed['website']) && empty($parsed['email'])) {
                    $extractedEmail = $this->extractEmailFromWebsite($parsed['website']);
                    if ($extractedEmail) {
                        $parsed['email'] = $extractedEmail;
                    }
                }

                $results[] = $parsed;
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

        // Try to extract phone from extensions or service_options if missing
        if (!$phone && isset($item['extensions']) && is_array($item['extensions'])) {
            foreach ($item['extensions'] as $ext) {
                if (is_string($ext) && preg_match('/^[\+\d\s\-\(\)]+$/', $ext)) {
                    $phone = $ext;
                    break;
                }
            }
        }

        // Process WhatsApp Number and WhatsApp URL
        $whatsappNumber = null;
        $whatsappUrl = null;
        if ($phone) {
            $digits = preg_replace('/[^\d]/', '', $phone);
            if (strlen($digits) >= 7) {
                $whatsappNumber = '+' . $digits;
                $whatsappUrl = "https://wa.me/{$digits}";
            }
        }

        // Keep CRM data clean and prevent 1406 'Data too long' errors for varchar(255) column limit
        if ($website && strlen($website) > 255) {
            $parsedUrl = parse_url($website);
            if (isset($parsedUrl['scheme']) && isset($parsedUrl['host'])) {
                $website = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . ($parsedUrl['path'] ?? '');
            }
            $website = substr($website, 0, 255);
        }

        return [
            'lead_getter_task_id' => $task->id,
            'title' => $item['title'] ?? 'Unknown Business',
            'company' => $item['title'] ?? null,
            'contact_name' => null,
            'email' => null,
            'phone' => $phone,
            'whatsapp_number' => $whatsappNumber,
            'whatsapp_url' => $whatsappUrl,
            'website' => $website,
            'address' => $address ?? ($item['snippet'] ?? null),
            'rating' => $item['rating'] ?? null,
            'reviews_count' => $item['reviews'] ?? null,
            'category' => $item['type'] ?? ($item['types'][0] ?? null),
            'raw_data' => $item,
            'status' => 'new',
        ];
    }

    /**
     * Scrape website homepage to extract contact email.
     */
    protected function extractEmailFromWebsite(string $website): ?string
    {
        try {
            $url = str_starts_with($website, 'http://') || str_starts_with($website, 'https://')
                ? $website
                : "https://{$website}";

            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
                ])
                ->get($url);

            if ($response->successful()) {
                $html = $response->body();
                if (preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/i', $html, $matches)) {
                    foreach ($matches[0] as $candidate) {
                        $candidateLower = strtolower($candidate);
                        // Exclude static assets or mock domains
                        if (preg_match('/\.(png|jpg|jpeg|gif|svg|webp|css|js|woff|woff2|ttf|eot)$/i', $candidateLower)) {
                            continue;
                        }
                        if (str_contains($candidateLower, 'example.com') ||
                            str_contains($candidateLower, 'domain.com') ||
                            str_contains($candidateLower, 'wixpress.com') ||
                            str_contains($candidateLower, 'sentry.io') ||
                            str_contains($candidateLower, 'bootstrap.com') ||
                            str_contains($candidateLower, 'schema.org')) {
                            continue;
                        }
                        return $candidateLower;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore scraping failures (timeouts, SSL errors, DNS failure)
        }

        return null;
    }
}
