<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBase;
use App\Models\ChatMetric;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    public function index()
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => []
        ];
        
        try {
            // Check database connection
            DB::connection()->getPdo();
            $health['checks']['database'] = [
                'status' => 'ok',
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            $health['status'] = 'unhealthy';
            $health['checks']['database'] = [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
        
        try {
            // Check knowledge base
            $kbCount = KnowledgeBase::count();
            $kbWithEmbeddings = KnowledgeBase::whereNotNull('embedding')->count();
            
            $health['checks']['knowledge_base'] = [
                'status' => $kbCount > 0 ? 'ok' : 'warning',
                'total_entries' => $kbCount,
                'with_embeddings' => $kbWithEmbeddings,
                'embedding_coverage' => $kbCount > 0 ? round(($kbWithEmbeddings / $kbCount) * 100, 2) . '%' : '0%'
            ];
            
            if ($kbCount === 0) {
                $health['status'] = 'degraded';
            }
        } catch (\Exception $e) {
            $health['status'] = 'unhealthy';
            $health['checks']['knowledge_base'] = [
                'status' => 'error',
                'message' => 'Failed to check knowledge base: ' . $e->getMessage()
            ];
        }
        
        try {
            // Check recent activity
            $lastQuery = ChatMetric::latest()->first();
            
            $health['checks']['activity'] = [
                'status' => 'ok',
                'last_query_time' => $lastQuery ? $lastQuery->created_at->toIso8601String() : null,
                'last_query_ago' => $lastQuery ? $lastQuery->created_at->diffForHumans() : 'No queries yet'
            ];
        } catch (\Exception $e) {
            $health['checks']['activity'] = [
                'status' => 'error',
                'message' => 'Failed to check activity: ' . $e->getMessage()
            ];
        }
        
        // Set HTTP status code based on health
        $statusCode = match($health['status']) {
            'healthy' => 200,
            'degraded' => 200,
            'unhealthy' => 503,
            default => 500,
        };
        
        return response()->json($health, $statusCode);
    }
}
