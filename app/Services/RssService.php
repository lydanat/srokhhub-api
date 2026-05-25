<?php

namespace App\Services;

use App\Models\ExternalNews;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RssService
{

    protected array $feeds = [
        'bbc' => 'http://feeds.bbci.co.uk/news/world/rss.xml',
        'reuters' => 'https://news.google.com/rss/search?q=when:24h+allinurl:reuters.com&ceid=US:en&hl=en-US&gl=US',
        'guardian' => 'https://www.theguardian.com/world/rss',
        'aljazeera' => 'https://www.aljazeera.com/xml/rss/all.xml',
    ];

    // Fetch and store all feeds
    public function fetchAll(): void
    {
        foreach ($this->feeds as $source => $url) {
            try {
                $this->fetchAndStore($source, $url);
            } catch (\Exception $e) {
                // Log error but continue with other sources
                Log::error("RSS fetch failed for {$source}: " . $e->getMessage());
            }
        }
    }

    // Fetch a single feed and store in database
    protected function fetchAndStore(string $source, string $url): void
    {
        // Fetch the XML with a 10 second timeout
        $response = Http::timeout(10)->get($url);

        if (! $response->successful()) {
            Log::warning("RSS feed returned non-200 for {$source}: " . $response->status());
            return;
        }

        libxml_set_streams_context(null);
        $previous = libxml_use_internal_errors(true);

        // Parse XML safely
        $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET | LIBXML_NOERROR);

        libxml_use_internal_errors($previous);
        libxml_clear_errors();

        if (! $xml) {
            Log::warning("Failed to parse XML for {$source}");
            return;
        }

        $items = $xml->channel->item ?? [];

        foreach ($items as $item) {
            $this->storeItem($source, $item);
        }
    }

    // Normalize and store a single RSS item
    protected function storeItem(string $source, \SimpleXMLElement $item): void
    {
        $url = trim((string) $item->link);

        if (empty($url)) {
            return;
        }

        // updateOrCreate prevents duplicates based on URL
        ExternalNews::updateOrCreate(
            ['url' => $url],
            [
                'title'        => trim((string) $item->title),
                'content'      => trim(strip_tags((string) $item->description)),
                'image'        => $this->extractImage($item),
                'source'       => $source,
                'category'     => $this->extractCategory($item),
                'published_at' => $this->parseDate((string) $item->pubDate),
            ]
        );
    }

    // Try to extract image from RSS item
    protected function extractImage(\SimpleXMLElement $item): ?string
    {
        // Try media:content (most common)
        $media = $item->children('media', true);
        if (isset($media->content)) {
            return (string) $media->content->attributes()->url;
        }

        // Try enclosure tag
        if (isset($item->enclosure)) {
            $type = (string) $item->enclosure->attributes()->type;
            if (str_starts_with($type, 'image/')) {
                return (string) $item->enclosure->attributes()->url;
            }
        }

        return null;
    }

    // Try to extract category from RSS item
    protected function extractCategory(\SimpleXMLElement $item): ?string
    {
        if (isset($item->category)) {
            return trim((string) $item->category);
        }

        return null;
    }

    // Parse RSS date format to Carbon-compatible format
    protected function parseDate(string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($date)->toDateTimeString();
        } catch (\Exception $e) {
            return null;
        }
    }
}