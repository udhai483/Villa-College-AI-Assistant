<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DiscoverVillaPages extends Command
{
    protected $signature = 'scrape:discover';
    protected $description = 'Discover available pages on Villa College website';

    public function handle()
    {
        $this->info('Discovering Villa College pages...');
        
        $baseUrl = 'https://villacollege.edu.mv';
        
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ])
                ->get($baseUrl);
            
            if ($response->successful()) {
                $html = $response->body();
                
                // Extract all links
                preg_match_all('/<a\s+(?:[^>]*?\s+)?href="([^"]*)"/i', $html, $matches);
                
                $links = [];
                foreach ($matches[1] as $link) {
                    // Filter internal links
                    if (str_starts_with($link, '/') || str_starts_with($link, $baseUrl)) {
                        $path = str_starts_with($link, '/') ? $link : str_replace($baseUrl, '', $link);
                        
                        // Skip common patterns
                        if (!str_contains($path, '#') && 
                            !str_contains($path, 'javascript:') &&
                            !str_contains($path, 'mailto:') &&
                            !str_contains($path, '.pdf') &&
                            !str_contains($path, '.jpg') &&
                            !str_contains($path, '.png')) {
                            $links[] = $path;
                        }
                    }
                }
                
                $links = array_unique($links);
                sort($links);
                
                $this->info("\nFound " . count($links) . " unique internal links:");
                $this->newLine();
                
                foreach ($links as $link) {
                    $this->line($link);
                }
                
                $this->newLine();
                $this->info("You can add these to the \$pagesToScrape array in ScrapeVillaCollege command");
                
            } else {
                $this->error("Failed to fetch homepage");
            }
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
        
        return 0;
    }
}
