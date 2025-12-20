<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedKnowledge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vc:seed-knowledge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the knowledge base with Villa College data (scrapes website and generates embeddings)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('╔════════════════════════════════════════════════════╗');
        $this->info('║   Villa College Knowledge Base Seeding Process    ║');
        $this->info('╚════════════════════════════════════════════════════╝');
        $this->newLine();
        
        // Step 1: Scrape website
        $this->info('📡 Step 1/2: Scraping Villa College website...');
        $this->newLine();
        
        Artisan::call('scrape:villacollege');
        $this->line(Artisan::output());
        
        $this->info('✓ Website scraping completed');
        $this->newLine();
        
        // Step 2: Generate embeddings
        $this->info('🧠 Step 2/2: Generating AI embeddings for semantic search...');
        $this->newLine();
        
        Artisan::call('embeddings:generate');
        $this->line(Artisan::output());
        
        $this->info('✓ Embedding generation completed');
        $this->newLine();
        
        // Summary
        $this->info('╔════════════════════════════════════════════════════╗');
        $this->info('║          Knowledge Base Seeding Complete!         ║');
        $this->info('╚════════════════════════════════════════════════════╝');
        $this->newLine();
        
        $this->info('The AI assistant is now ready to answer questions about Villa College.');
        $this->info('You can test it by visiting: http://localhost:8080');
        $this->newLine();
        
        return Command::SUCCESS;
    }
}
