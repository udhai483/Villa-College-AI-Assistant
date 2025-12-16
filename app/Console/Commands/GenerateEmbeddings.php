<?php

namespace App\Console\Commands;

use App\Models\KnowledgeBase;
use Illuminate\Console\Command;
use OpenAI\Laravel\Facades\OpenAI;

class GenerateEmbeddings extends Command
{
    protected $signature = 'embeddings:generate {--batch=50 : Number of records to process in each batch}';
    protected $description = 'Generate OpenAI embeddings for knowledge base content';

    public function handle()
    {
        $this->info('Starting embedding generation...');
        $this->newLine();
        
        // Check if OpenAI API key is configured
        if (empty(config('openai.api_key')) || config('openai.api_key') === 'your-openai-api-key') {
            $this->error('✗ OpenAI API key not configured!');
            $this->warn('Please add your OpenAI API key to .env file:');
            $this->line('OPENAI_API_KEY=sk-your-actual-key-here');
            return Command::FAILURE;
        }
        
        // Get records without embeddings
        $batchSize = (int) $this->option('batch');
        $totalRecords = KnowledgeBase::whereNull('embedding')->count();
        
        if ($totalRecords === 0) {
            $this->info('✓ All knowledge base entries already have embeddings!');
            return Command::SUCCESS;
        }
        
        $this->info("Found $totalRecords records without embeddings");
        $this->info("Processing in batches of $batchSize");
        $this->newLine();
        
        $bar = $this->output->createProgressBar($totalRecords);
        $bar->start();
        
        $processedCount = 0;
        $errorCount = 0;
        
        // Process in batches
        KnowledgeBase::whereNull('embedding')
            ->chunk($batchSize, function ($records) use (&$processedCount, &$errorCount, $bar) {
                foreach ($records as $record) {
                    try {
                        // Generate embedding using OpenAI
                        $response = OpenAI::embeddings()->create([
                            'model' => 'text-embedding-ada-002',
                            'input' => $record->content,
                        ]);
                        
                        // Extract embedding vector
                        $embedding = $response->embeddings[0]->embedding;
                        
                        // Store embedding as JSON
                        $record->update([
                            'embedding' => $embedding
                        ]);
                        
                        $processedCount++;
                        
                    } catch (\Exception $e) {
                        $this->newLine();
                        $this->error("✗ Error processing record {$record->id}: " . $e->getMessage());
                        $errorCount++;
                    }
                    
                    $bar->advance();
                    
                    // Rate limiting - small delay between API calls
                    usleep(100000); // 0.1 second
                }
            });
        
        $bar->finish();
        $this->newLine(2);
        
        // Summary
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('Embedding Generation Summary:');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Records', $totalRecords],
                ['Successfully Processed', $processedCount],
                ['Errors', $errorCount],
                ['Completion Rate', $totalRecords > 0 ? round(($processedCount / $totalRecords) * 100, 1) . '%' : '0%'],
            ]
        );
        
        $this->newLine();
        
        if ($processedCount > 0) {
            $this->info('✓ Embedding generation completed!');
            $this->info('✓ RAG system is now ready for semantic search');
        }
        
        if ($errorCount > 0) {
            $this->warn("⚠ $errorCount records failed to process. You can re-run this command to retry.");
        }
        
        return Command::SUCCESS;
    }
}
