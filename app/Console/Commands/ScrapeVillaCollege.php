<?php

namespace App\Console\Commands;

use App\Models\KnowledgeBase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ScrapeVillaCollege extends Command
{
    protected $signature = 'scrape:villacollege';
    protected $description = 'Scrape Villa College website and populate knowledge base';

    private $baseUrl = 'https://villacollege.edu.mv';
    private $scrapedUrls = [];
    private $retryAttempts = 3;
    private $retryDelay = 2000000; // 2 seconds in microseconds

    public function handle()
    {
        $this->info('Starting Villa College website scraping...');
        $this->info('Base URL: ' . $this->baseUrl);
        $this->newLine();
        
        // Clear existing knowledge base
        if ($this->confirm('Do you want to clear existing knowledge base data?', true)) {
            $this->info('Clearing existing knowledge base...');
            KnowledgeBase::truncate();
            $this->info('✓ Knowledge base cleared');
            $this->newLine();
        }
        
        // Pages to scrape (actual Villa College structure discovered from site)
        $pagesToScrape = [
            '/' => 'Home',
            '/about-us' => 'About Us',
            '/why-villa-college' => 'Why Villa College',
            '/governance' => 'Governance',
            '/future-governance-mission' => 'Vision & Mission',
            '/programmes' => 'Programmes',
            '/programmes?programme=1' => 'Certificate Programmes',
            '/programmes?programme=4' => 'Diploma Programmes',
            '/programmes?programme=5' => 'Degree Programmes',
            '/adminssion-requirments' => 'Admission Requirements',
            '/financial-aid' => 'Financial Aid',
            '/student-life' => 'Student Life',
            '/student-life/career-services' => 'Career Services',
            '/facilities' => 'Facilities',
            '/campus' => 'Campus',
            '/library' => 'Library',
            '/faculties' => 'Faculties',
            '/international' => 'International Students',
            '/contacts' => 'Contact Us',
            '/media-center/news' => 'News',
        ];
        
        $bar = $this->output->createProgressBar(count($pagesToScrape));
        $bar->start();
        
        $totalChunks = 0;
        $successCount = 0;
        $failCount = 0;
        
        foreach ($pagesToScrape as $path => $title) {
            try {
                $url = $this->baseUrl . $path;
                
                // Skip if already scraped
                if (in_array($url, $this->scrapedUrls)) {
                    $bar->advance();
                    continue;
                }
                
                $this->scrapedUrls[] = $url;
                
                // Fetch the page with retry logic
                $html = $this->fetchPageWithRetry($url);
                
                if ($html === null) {
                    $this->newLine();
                    $this->error("✗ Failed to fetch $title after {$this->retryAttempts} attempts");
                    $failCount++;
                    $bar->advance();
                    continue;
                }
                
                // Extract and clean text
                $text = $this->extractText($html);
                
                if (empty($text) || strlen($text) < 100) {
                    $this->newLine();
                    $this->warn("⚠ Skipping $title - insufficient content (length: " . strlen($text) . ")");
                    $failCount++;
                    $bar->advance();
                    continue;
                }
                
                // Add title context
                $fullText = "Page: $title\n\n" . $text;
                
                // Chunk the text
                $chunks = $this->chunkText($fullText, 800);
                
                // Store chunks
                $chunkCount = 0;
                foreach ($chunks as $chunk) {
                    if (strlen(trim($chunk)) > 50) {
                        KnowledgeBase::create([
                            'content' => trim($chunk),
                            'source_url' => $url,
                            'embedding' => null, // Will be generated later
                        ]);
                        $chunkCount++;
                        $totalChunks++;
                    }
                }
                
                $this->newLine();
                $this->info("✓ $title: $chunkCount chunks");
                $successCount++;
                
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("✗ Error scraping $title: " . $e->getMessage());
                $failCount++;
            }
            
            $bar->advance();
            
            // Be respectful - delay between requests
            usleep(500000); // 0.5 second delay
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Summary
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('Scraping Summary:');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Pages Attempted', count($pagesToScrape)],
                ['Successful', $successCount],
                ['Failed', $failCount],
                ['Total Chunks Stored', $totalChunks],
                ['Average per Page', $successCount > 0 ? round($totalChunks / $successCount, 1) : 0],
            ]
        );
        
        $this->newLine();
        
        if ($totalChunks > 0) {
            $this->info('✓ Scraping completed successfully!');
            $this->newLine();
            $this->warn('⚠ Next Step: Generate embeddings');
            $this->line('Run: php artisan embeddings:generate');
        } else {
            $this->error('✗ No content was scraped. Check the website URL and internet connection.');
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Fetch page with retry logic
     */
    private function fetchPageWithRetry(string $url): ?string
    {
        $attempts = 0;
        
        while ($attempts < $this->retryAttempts) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                        'Accept-Language' => 'en-US,en;q=0.9',
                        'Accept-Encoding' => 'gzip, deflate',
                        'Connection' => 'keep-alive',
                    ])
                    ->get($url);
                
                if ($response->successful()) {
                    return $response->body();
                }
                
                // If 404 or other client error, don't retry
                if ($response->status() >= 400 && $response->status() < 500) {
                    return null;
                }
                
            } catch (\Exception $e) {
                // Log error for debugging
                $this->line("Attempt " . ($attempts + 1) . " failed: " . $e->getMessage());
            }
            
            $attempts++;
            
            if ($attempts < $this->retryAttempts) {
                usleep($this->retryDelay); // Wait before retry
            }
        }
        
        return null;
    }
    
    /**
     * Extract clean text from HTML
     */
    private function extractText(string $html): string
    {
        // Normalize encoding
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        
        // Remove scripts, styles, and other non-content elements
        $patterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/<style\b[^>]*>(.*?)<\/style>/is',
            '/<noscript\b[^>]*>(.*?)<\/noscript>/is',
            '/<iframe\b[^>]*>(.*?)<\/iframe>/is',
            '/<svg\b[^>]*>(.*?)<\/svg>/is',
            '/<!--(.|\s)*?-->/',
        ];
        
        foreach ($patterns as $pattern) {
            $html = preg_replace($pattern, '', $html);
        }
        
        // Remove navigation, header, footer, and sidebar elements
        $elementsToRemove = [
            '/<nav\b[^>]*>(.*?)<\/nav>/is',
            '/<header\b[^>]*>(.*?)<\/header>/is',
            '/<footer\b[^>]*>(.*?)<\/footer>/is',
            '/<aside\b[^>]*>(.*?)<\/aside>/is',
            '/<form\b[^>]*>(.*?)<\/form>/is',
        ];
        
        foreach ($elementsToRemove as $pattern) {
            $html = preg_replace($pattern, '', $html);
        }
        
        // Extract main content if possible
        if (preg_match('/<main\b[^>]*>(.*?)<\/main>/is', $html, $matches)) {
            $html = $matches[1];
        } elseif (preg_match('/<article\b[^>]*>(.*?)<\/article>/is', $html, $matches)) {
            $html = $matches[1];
        } elseif (preg_match('/<div[^>]*class=["\'][^"\']*content[^"\']*["\'][^>]*>(.*?)<\/div>/is', $html, $matches)) {
            $html = $matches[1];
        }
        
        // Add spacing around block elements
        $blockElements = ['p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'br'];
        foreach ($blockElements as $tag) {
            $html = preg_replace("/<\\/?\s*$tag\b[^>]*>/i", "\n", $html);
        }
        
        // Convert to plain text
        $text = strip_tags($html);
        
        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Clean up whitespace
        $text = preg_replace('/[ \t]+/', ' ', $text); // Multiple spaces to single
        $text = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $text); // Multiple newlines to double
        $text = preg_replace('/^\s+/m', '', $text); // Leading spaces on lines
        
        // Remove common noise patterns
        $noisePatterns = [
            '/Skip to (main )?content/i',
            '/Jump to navigation/i',
            '/Copyright ©.*$/im',
            '/All rights reserved\.?/i',
            '/Privacy Policy/i',
            '/Terms (of|and) (Service|Use)/i',
            '/Cookie (Policy|Settings)/i',
            '/Follow us on:?/i',
            '/Share this:?/i',
            '/Click here/i',
            '/Read more/i',
            '/Learn more/i',
        ];
        
        foreach ($noisePatterns as $pattern) {
            $text = preg_replace($pattern, '', $text);
        }
        
        // Remove URLs
        $text = preg_replace('/https?:\/\/[^\s]+/i', '', $text);
        
        // Remove email addresses (keep in content though - comment this if needed)
        // $text = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '', $text);
        
        // Final cleanup
        $text = trim($text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        
        return $text;
    }
    
    /**
     * Extract clean text from HTML
     */
    private function extractText_old(string $html): string
    {
        // Remove scripts and styles
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        $html = preg_replace('/<noscript\b[^>]*>(.*?)<\/noscript>/is', '', $html);
        
        // Remove HTML comments
        $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);
        
        // Convert to plain text
        $text = strip_tags($html);
        
        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Clean up whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/\n\s*\n/', "\n\n", $text);
        
        // Remove common navigation/footer text patterns
        $patterns = [
            '/Skip to (main )?content/i',
            '/Copyright ©.*$/im',
            '/All rights reserved\.?/i',
            '/Privacy Policy/i',
            '/Terms (of|and) (Service|Use)/i',
        ];
        
        foreach ($patterns as $pattern) {
            $text = preg_replace($pattern, '', $text);
        }
        
        return trim($text);
    }
    
    /**
     * Chunk text intelligently at sentence boundaries
     */
    private function chunkText(string $text, int $maxLength = 800): array
    {
        $chunks = [];
        
        // Split into sentences (simple approach)
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        $currentChunk = '';
        
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            
            if (empty($sentence)) {
                continue;
            }
            
            // If adding this sentence exceeds max length
            if (strlen($currentChunk . ' ' . $sentence) > $maxLength) {
                // Save current chunk if it has content
                if (!empty(trim($currentChunk))) {
                    $chunks[] = trim($currentChunk);
                    $currentChunk = '';
                }
                
                // If single sentence is longer than max, split it
                if (strlen($sentence) > $maxLength) {
                    $words = explode(' ', $sentence);
                    $tempChunk = '';
                    
                    foreach ($words as $word) {
                        if (strlen($tempChunk . ' ' . $word) > $maxLength) {
                            if (!empty($tempChunk)) {
                                $chunks[] = trim($tempChunk);
                            }
                            $tempChunk = $word;
                        } else {
                            $tempChunk .= ($tempChunk ? ' ' : '') . $word;
                        }
                    }
                    
                    if (!empty($tempChunk)) {
                        $currentChunk = $tempChunk;
                    }
                } else {
                    $currentChunk = $sentence;
                }
            } else {
                $currentChunk .= ($currentChunk ? ' ' : '') . $sentence;
            }
        }
        
        // Add remaining chunk
        if (!empty(trim($currentChunk))) {
            $chunks[] = trim($currentChunk);
        }
        
        return $chunks;
    }
}
