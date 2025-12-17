<?php

namespace App\Console\Commands;

use App\Models\ChatMetric;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ViewMetrics extends Command
{
    protected $signature = 'metrics:view {--period=24 : Hours to look back}';
    protected $description = 'View chatbot performance metrics and statistics';

    public function handle()
    {
        $hours = (int) $this->option('period');
        $since = now()->subHours($hours);
        
        $this->info("📊 Chatbot Performance Metrics (Last {$hours} hours)");
        $this->newLine();
        
        // Overall stats
        $totalQueries = ChatMetric::where('created_at', '>=', $since)->count();
        
        if ($totalQueries === 0) {
            $this->warn('No queries in the selected time period.');
            return 0;
        }
        
        $this->info("🔍 QUERY VOLUME");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Queries', number_format($totalQueries)],
                ['Queries/Hour', number_format($totalQueries / max($hours, 1), 2)],
            ]
        );
        $this->newLine();
        
        // Performance metrics
        $avgLatency = ChatMetric::where('created_at', '>=', $since)->avg('response_time_ms');
        $maxLatency = ChatMetric::where('created_at', '>=', $since)->max('response_time_ms');
        $minLatency = ChatMetric::where('created_at', '>=', $since)->min('response_time_ms');
        
        $this->info("⚡ PERFORMANCE");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Avg Response Time', number_format($avgLatency, 0) . ' ms'],
                ['Min Response Time', number_format($minLatency, 0) . ' ms'],
                ['Max Response Time', number_format($maxLatency, 0) . ' ms'],
            ]
        );
        $this->newLine();
        
        // Search method breakdown
        $searchMethods = ChatMetric::where('created_at', '>=', $since)
            ->select('search_method', DB::raw('count(*) as count'))
            ->groupBy('search_method')
            ->get();
        
        $this->info("🔎 SEARCH METHODS");
        $methodData = $searchMethods->map(function($method) use ($totalQueries) {
            return [
                'Method' => ucfirst($method->search_method),
                'Count' => number_format($method->count),
                'Percentage' => number_format(($method->count / $totalQueries) * 100, 1) . '%',
            ];
        });
        $this->table(['Method', 'Count', 'Percentage'], $methodData);
        $this->newLine();
        
        // Fallback rate
        $fallbackCount = ChatMetric::where('created_at', '>=', $since)
            ->where('had_fallback', true)
            ->count();
        $fallbackRate = $totalQueries > 0 ? ($fallbackCount / $totalQueries) * 100 : 0;
        
        $this->info("🔄 FALLBACK STATISTICS");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Fallback Count', number_format($fallbackCount)],
                ['Fallback Rate', number_format($fallbackRate, 2) . '%'],
            ]
        );
        $this->newLine();
        
        // Error rate
        $errorCount = ChatMetric::where('created_at', '>=', $since)
            ->whereNotNull('error')
            ->count();
        $errorRate = $totalQueries > 0 ? ($errorCount / $totalQueries) * 100 : 0;
        
        $this->info("❌ ERROR STATISTICS");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Errors', number_format($errorCount)],
                ['Error Rate', number_format($errorRate, 2) . '%'],
            ]
        );
        
        if ($errorCount > 0) {
            $this->newLine();
            $this->warn('Recent errors:');
            $recentErrors = ChatMetric::where('created_at', '>=', $since)
                ->whereNotNull('error')
                ->latest()
                ->limit(5)
                ->get(['created_at', 'query', 'error']);
            
            foreach ($recentErrors as $error) {
                $this->line("  • [{$error->created_at->format('Y-m-d H:i:s')}] \"{$error->query}\"");
                $this->line("    Error: " . Str::limit($error->error, 100));
            }
        }
        $this->newLine();
        
        // Empty results
        $emptyCount = ChatMetric::where('created_at', '>=', $since)
            ->where('result_count', 0)
            ->count();
        $emptyRate = $totalQueries > 0 ? ($emptyCount / $totalQueries) * 100 : 0;
        
        $this->info("🔍 RESULT QUALITY");
        $this->table(
            ['Metric', 'Value'],
            [
                ['No Results Found', number_format($emptyCount)],
                ['Empty Rate', number_format($emptyRate, 2) . '%'],
                ['Avg Results/Query', number_format(ChatMetric::where('created_at', '>=', $since)->avg('result_count'), 1)],
            ]
        );
        $this->newLine();
        
        // Top queries
        $topQueries = ChatMetric::where('created_at', '>=', $since)
            ->select('query', DB::raw('count(*) as count'))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit(10)
            ->get();
        
        if ($topQueries->isNotEmpty()) {
            $this->info("🔥 TOP 10 QUERIES");
            $this->table(
                ['Count', 'Query'],
                $topQueries->map(fn($q) => [$q->count, Str::limit($q->query, 70)])
            );
        }
        
        $this->newLine();
        $this->info('✅ Metrics generated successfully!');
        $this->comment("💡 Tip: Use --period=168 for weekly stats, --period=1 for last hour");
        
        return 0;
    }
}
