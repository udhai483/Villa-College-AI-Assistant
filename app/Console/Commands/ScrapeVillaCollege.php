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
        
        // Pages to scrape (expanded list)
        $pagesToScrape = [
            '/' => 'Home',
            '/about' => 'About Us',
            '/about/vision-mission' => 'Vision & Mission',
            '/about/history' => 'History',
            '/academics' => 'Academics',
            '/academics/programs' => 'Programs',
            '/academics/faculties' => 'Faculties',
            '/admissions' => 'Admissions',
            '/admissions/requirements' => 'Admission Requirements',
            '/admissions/how-to-apply' => 'How to Apply',
            '/student-life' => 'Student Life',
            '/student-life/facilities' => 'Facilities',
            '/student-life/clubs' => 'Clubs & Activities',
            '/research' => 'Research',
            '/contact' => 'Contact Us',
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
                
                // Fetch the page
                $response = Http::timeout(30)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                    ])
                    ->get($url);
                
                if ($response->successful()) {
                    $html = $response->body();
                    
                    // Extract and clean text
                    $text = $this->extractText($html);
                    
                    if (empty($text) || strlen($text) < 100) {
                        $this->newLine();
                        $this->warn("⚠ Skipping $title - insufficient content");
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
                    
                } else {
                    $this->newLine();
                    $this->error("✗ Failed to fetch $title (HTTP {$response->status()})");
                    $failCount++;
                }
                
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
     * Extract clean text from HTML
     */
    private function extractText(string $html): string
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
