<?php

namespace App\Console\Commands;

use App\Models\KnowledgeBase;
use Illuminate\Console\Command;
use Smalot\PdfParser\Parser;

class ImportPdf extends Command
{
    protected $signature = 'knowledge:import-pdf {path : Path to PDF file or directory}';
    protected $description = 'Import PDF documents into knowledge base';

    public function handle()
    {
        $path = $this->argument('path');
        
        // Check if path exists
        if (!file_exists($path)) {
            $this->error("Path not found: {$path}");
            return Command::FAILURE;
        }
        
        $this->info('Starting PDF import...');
        $this->newLine();
        
        $files = [];
        
        // Check if it's a file or directory
        if (is_file($path)) {
            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf') {
                $files[] = $path;
            } else {
                $this->error('File is not a PDF: ' . $path);
                return Command::FAILURE;
            }
        } elseif (is_dir($path)) {
            // Find all PDF files in directory
            $files = glob($path . '/*.pdf');
            if (empty($files)) {
                $this->warn('No PDF files found in directory: ' . $path);
                return Command::SUCCESS;
            }
        }
        
        $this->info('Found ' . count($files) . ' PDF file(s) to process');
        $this->newLine();
        
        $bar = $this->output->createProgressBar(count($files));
        $bar->start();
        
        $totalChunks = 0;
        $successCount = 0;
        $failCount = 0;
        
        foreach ($files as $filePath) {
            try {
                $fileName = basename($filePath);
                
                // Check if already imported
                $exists = KnowledgeBase::where('source_url', 'LIKE', "%{$fileName}%")->exists();
                if ($exists) {
                    $this->newLine();
                    $this->warn("⚠ Skipping {$fileName} - already imported");
                    $bar->advance();
                    continue;
                }
                
                // Parse PDF
                $parser = new Parser();
                $pdf = $parser->parseFile($filePath);
                
                // Extract text
                $text = $pdf->getText();
                
                if (empty(trim($text))) {
                    $this->newLine();
                    $this->warn("⚠ Skipping {$fileName} - no text content");
                    $failCount++;
                    $bar->advance();
                    continue;
                }
                
                // Clean text
                $text = $this->cleanText($text);
                
                if (strlen($text) < 100) {
                    $this->newLine();
                    $this->warn("⚠ Skipping {$fileName} - insufficient content");
                    $failCount++;
                    $bar->advance();
                    continue;
                }
                
                // Get PDF metadata
                $details = $pdf->getDetails();
                $title = $details['Title'] ?? pathinfo($fileName, PATHINFO_FILENAME);
                
                // Add title context
                $fullText = "Document: {$title}\n\n{$text}";
                
                // Chunk the text (500-800 chars)
                $chunks = $this->chunkText($fullText, 650);
                
                // Store chunks
                $chunkCount = 0;
                foreach ($chunks as $chunk) {
                    if (strlen(trim($chunk)) > 50) {
                        KnowledgeBase::create([
                            'content' => trim($chunk),
                            'source_url' => "pdf://{$fileName}",
                            'embedding' => null,
                        ]);
                        $chunkCount++;
                        $totalChunks++;
                    }
                }
                
                $this->newLine();
                $this->info("✓ {$fileName}: {$chunkCount} chunks");
                $successCount++;
                
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("✗ Error processing {$fileName}: " . $e->getMessage());
                $failCount++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Summary
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('PDF Import Summary:');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->table(
            ['Metric', 'Value'],
            [
                ['PDFs Attempted', count($files)],
                ['Successful', $successCount],
                ['Failed', $failCount],
                ['Total Chunks Stored', $totalChunks],
                ['Average per PDF', $successCount > 0 ? round($totalChunks / $successCount, 1) : 0],
            ]
        );
        
        $this->newLine();
        
        if ($totalChunks > 0) {
            $this->info('✓ PDF import completed successfully!');
            $this->info('Total knowledge base entries: ' . KnowledgeBase::count());
        } else {
            $this->error('✗ No content was imported.');
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Clean extracted PDF text
     */
    private function cleanText(string $text): string
    {
        // Normalize whitespace
        $text = preg_replace('/\r\n|\r/', "\n", $text);
        
        // Remove form feed characters
        $text = str_replace("\f", "\n", $text);
        
        // Fix hyphenated words at line breaks
        $text = preg_replace('/(\w)-\s*\n\s*(\w)/', '$1$2', $text);
        
        // Remove excessive whitespace
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $text);
        
        // Remove page numbers (common patterns)
        $text = preg_replace('/^\s*\d+\s*$/m', '', $text);
        $text = preg_replace('/^Page \d+ of \d+$/mi', '', $text);
        
        // Remove headers/footers (repeated short lines)
        $lines = explode("\n", $text);
        $lineCounts = array_count_values(array_map('trim', $lines));
        $repeatedLines = array_filter($lineCounts, fn($count) => $count > 3);
        
        foreach (array_keys($repeatedLines) as $repeatedLine) {
            if (strlen($repeatedLine) < 50) {
                $text = str_replace($repeatedLine, '', $text);
            }
        }
        
        // Final cleanup
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = trim($text);
        
        return $text;
    }
    
    /**
     * Chunk text intelligently at sentence boundaries
     */
    private function chunkText(string $text, int $targetLength = 650): array
    {
        $chunks = [];
        $minLength = 500;
        $maxLength = 800;
        
        // Split into sentences
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        $currentChunk = '';
        
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            
            if (empty($sentence)) {
                continue;
            }
            
            $potentialChunk = $currentChunk . ($currentChunk ? ' ' : '') . $sentence;
            
            // If adding this sentence would exceed max length
            if (strlen($potentialChunk) > $maxLength) {
                // Save current chunk if it meets minimum length
                if (strlen($currentChunk) >= $minLength) {
                    $chunks[] = trim($currentChunk);
                    $currentChunk = $sentence;
                } else {
                    // Current chunk too short, but adding sentence makes it too long
                    // Split the sentence
                    if (strlen($sentence) > $maxLength) {
                        // Very long sentence - split by words
                        $words = explode(' ', $sentence);
                        $tempChunk = $currentChunk;
                        
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
                        $currentChunk = $tempChunk;
                    } else {
                        $currentChunk = $potentialChunk;
                    }
                }
            } else {
                $currentChunk = $potentialChunk;
                
                // If we've reached target length and have a good break point
                if (strlen($currentChunk) >= $targetLength) {
                    $chunks[] = trim($currentChunk);
                    $currentChunk = '';
                }
            }
        }
        
        // Add remaining chunk
        if (!empty(trim($currentChunk))) {
            $chunks[] = trim($currentChunk);
        }
        
        return $chunks;
    }
}
