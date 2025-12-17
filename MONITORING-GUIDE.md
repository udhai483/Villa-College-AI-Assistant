# Production Monitoring Guide

## 📊 Overview

Comprehensive logging and monitoring system for production safety.

## ✅ What's Implemented

### **1. Centralized Error Logging**
All errors are logged to Laravel's log file with full context:

- **Search failures**: When no results found
- **GPT errors**: OpenAI API failures (quota, network, etc.)
- **Semantic search failures**: Embedding generation errors
- **Empty answers**: Queries with zero results
- **Complete failures**: Unexpected system errors

**Log Location**: `storage/logs/laravel.log`

**Log Levels**:
- `ERROR`: Critical failures (complete search failure, GPT errors)
- `WARNING`: Fallback triggers (semantic → keyword)
- `INFO`: Empty results, no matches found

### **2. Request/Response Metrics**

Every query is tracked in `chat_metrics` table:

```sql
CREATE TABLE chat_metrics (
    id BIGINT PRIMARY KEY,
    user_id BIGINT (who asked),
    conversation_id BIGINT (which conversation),
    query TEXT (what they asked),
    response_time_ms INT (how long it took),
    search_method ENUM('semantic', 'keyword', 'failed'),
    result_count INT (how many results found),
    error TEXT (error message if any),
    had_fallback BOOLEAN (did it fallback?),
    created_at TIMESTAMP
);
```

**Tracked Metrics**:
- ⚡ **Latency**: Response time in milliseconds
- 🔎 **Search Method**: Which method was used (semantic/keyword/failed)
- 📊 **Result Count**: How many results were found
- 🔄 **Fallback Rate**: How often semantic falls back to keyword
- ❌ **Error Rate**: Percentage of failed queries
- 📝 **Top Queries**: Most frequently asked questions

### **3. Health Check Endpoint**

**URL**: `http://localhost:8080/api/health`

**Response** (HTTP 200 = healthy, 503 = unhealthy):
```json
{
  "status": "healthy",
  "timestamp": "2025-12-17T12:53:12+00:00",
  "checks": {
    "database": {
      "status": "ok",
      "message": "Database connection successful"
    },
    "knowledge_base": {
      "status": "ok",
      "total_entries": 95,
      "with_embeddings": 0,
      "embedding_coverage": "0%"
    },
    "activity": {
      "status": "ok",
      "last_query_time": "2025-12-17T12:30:00+00:00",
      "last_query_ago": "23 minutes ago"
    }
  }
}
```

**Status Codes**:
- `healthy`: All systems operational
- `degraded`: Working but with warnings (e.g., no KB entries)
- `unhealthy`: Critical failure (503 status code)

**Use Cases**:
- Monitoring dashboards (Datadog, New Relic, etc.)
- Kubernetes liveness/readiness probes
- Uptime monitoring (UptimeRobot, Pingdom)
- Load balancer health checks

## 🔧 Commands

### **View Performance Metrics**

```bash
# Last 24 hours (default)
docker compose exec app php artisan metrics:view

# Last hour
docker compose exec app php artisan metrics:view --period=1

# Last week
docker compose exec app php artisan metrics:view --period=168

# Last month
docker compose exec app php artisan metrics:view --period=720
```

**Sample Output**:
```
📊 Chatbot Performance Metrics (Last 24 hours)

🔍 QUERY VOLUME
+----------------+-------+
| Metric         | Value |
+----------------+-------+
| Total Queries  | 1,234 |
| Queries/Hour   | 51.42 |
+----------------+-------+

⚡ PERFORMANCE
+-------------------+----------+
| Metric            | Value    |
+-------------------+----------+
| Avg Response Time | 1,245 ms |
| Min Response Time | 245 ms   |
| Max Response Time | 5,678 ms |
+-------------------+----------+

🔎 SEARCH METHODS
+----------+-------+------------+
| Method   | Count | Percentage |
+----------+-------+------------+
| Keyword  | 1,234 | 100.0%     |
| Semantic | 0     | 0.0%       |
+----------+-------+------------+

🔄 FALLBACK STATISTICS
+----------------+-------+
| Metric         | Value |
+----------------+-------+
| Fallback Count | 0     |
| Fallback Rate  | 0.00% |
+----------------+-------+

❌ ERROR STATISTICS
+--------------+-------+
| Metric       | Value |
+--------------+-------+
| Total Errors | 5     |
| Error Rate   | 0.41% |
+--------------+-------+

🔍 RESULT QUALITY
+-------------------+-------+
| Metric            | Value |
+-------------------+-------+
| No Results Found  | 23    |
| Empty Rate        | 1.86% |
| Avg Results/Query | 4.2   |
+-------------------+-------+

🔥 TOP 10 QUERIES
+-------+-----------------------------------------------------+
| Count | Query                                               |
+-------+-----------------------------------------------------+
| 45    | How do I apply for admission?                       |
| 32    | What programs do you offer?                         |
| 28    | What are the tuition fees?                          |
| 21    | Where is the campus located?                        |
| 18    | Do you offer online courses?                        |
+-------+-----------------------------------------------------+
```

### **View Logs**

```bash
# Real-time log monitoring
docker compose exec app tail -f storage/logs/laravel.log

# Last 100 lines
docker compose exec app tail -n 100 storage/logs/laravel.log

# Search for errors
docker compose exec app grep "ERROR" storage/logs/laravel.log

# Search for specific query
docker compose exec app grep "admission" storage/logs/laravel.log
```

## 📈 What Gets Logged

### **Every Query**
```php
// Automatic metrics logging in ChatInterface.php
ChatMetric::create([
    'user_id' => auth()->id(),
    'query' => "How do I apply?",
    'response_time_ms' => 1234,
    'search_method' => 'keyword',
    'result_count' => 5,
    'error' => null,
    'had_fallback' => false,
]);
```

### **Search Failures**
```log
[2025-12-17 12:30:00] INFO: No results found for query
  query: "underwater basket weaving course"
  search_method: keyword
  user_id: 1
```

### **GPT Errors**
```log
[2025-12-17 12:30:00] ERROR: GPT response generation failed
  query: "What programs do you offer?"
  error: "You exceeded your current quota"
  user_id: 1
```

### **Semantic Fallback**
```log
[2025-12-17 12:30:00] WARNING: Semantic search failed, falling back to keyword search
  query: "admission requirements"
  error: "Connection timeout"
  user_id: 1
```

### **Complete Failure**
```log
[2025-12-17 12:30:00] ERROR: Complete search failure
  query: "fees"
  error: "Database connection lost"
  trace: <full stack trace>
  user_id: 1
```

## 🚨 Production Monitoring Setup

### **1. Health Check Monitoring**

**UptimeRobot / Pingdom**:
- Monitor: `http://your-domain.com/api/health`
- Interval: Every 5 minutes
- Alert on: Status code 503 or response time > 5s

**Kubernetes**:
```yaml
livenessProbe:
  httpGet:
    path: /api/health
    port: 8080
  initialDelaySeconds: 30
  periodSeconds: 10
  
readinessProbe:
  httpGet:
    path: /api/health
    port: 8080
  initialDelaySeconds: 10
  periodSeconds: 5
```

### **2. Log Aggregation**

**Datadog / New Relic / Splunk**:
```bash
# Forward Laravel logs
# storage/logs/laravel.log → Log aggregation service
```

**Alert Rules**:
- Error rate > 5% in last 5 minutes
- Avg response time > 3000ms in last 10 minutes
- Fallback rate > 50% in last 5 minutes
- Zero queries in last 30 minutes (during business hours)

### **3. Metrics Dashboard**

**Grafana / Custom Dashboard**:
```sql
-- Query for metrics dashboard
SELECT 
    DATE(created_at) as date,
    COUNT(*) as total_queries,
    AVG(response_time_ms) as avg_latency,
    SUM(CASE WHEN search_method = 'keyword' THEN 1 ELSE 0 END) as keyword_count,
    SUM(CASE WHEN search_method = 'semantic' THEN 1 ELSE 0 END) as semantic_count,
    SUM(CASE WHEN had_fallback = 1 THEN 1 ELSE 0 END) as fallback_count,
    SUM(CASE WHEN error IS NOT NULL THEN 1 ELSE 0 END) as error_count,
    SUM(CASE WHEN result_count = 0 THEN 1 ELSE 0 END) as empty_count
FROM chat_metrics
WHERE created_at >= NOW() - INTERVAL 30 DAY
GROUP BY DATE(created_at)
ORDER BY date DESC;
```

### **4. Scheduled Reports**

**Daily Metrics Email** (via Laravel Scheduler):
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('metrics:view --period=24')
        ->dailyAt('09:00')
        ->emailOutputTo('admin@villacollege.edu.mv');
}
```

## 🎯 Key Metrics to Watch

### **Performance**
- ✅ **Good**: Avg latency < 2000ms
- ⚠️ **Warning**: Avg latency 2000-5000ms
- ❌ **Critical**: Avg latency > 5000ms

### **Reliability**
- ✅ **Good**: Error rate < 1%
- ⚠️ **Warning**: Error rate 1-5%
- ❌ **Critical**: Error rate > 5%

### **Quality**
- ✅ **Good**: Empty rate < 5%
- ⚠️ **Warning**: Empty rate 5-15%
- ❌ **Critical**: Empty rate > 15%

### **Fallback Rate** (when embeddings enabled)
- ✅ **Good**: Fallback rate < 5%
- ⚠️ **Warning**: Fallback rate 5-20%
- ❌ **Critical**: Fallback rate > 20%

## 🔍 Debugging Common Issues

### **High Latency**
```bash
# Check slow queries
docker compose exec app php artisan metrics:view --period=1

# Look for patterns
docker compose exec app grep "response_time_ms" storage/logs/laravel.log | sort -t: -k2 -n | tail -20
```

### **High Error Rate**
```bash
# Check recent errors
docker compose exec app grep "ERROR" storage/logs/laravel.log | tail -50

# Group by error type
docker compose exec app grep "ERROR" storage/logs/laravel.log | cut -d: -f3 | sort | uniq -c | sort -rn
```

### **Empty Results**
```bash
# Find queries with no results
docker compose exec app php artisan tinker
```
```php
ChatMetric::where('result_count', 0)->latest()->take(20)->pluck('query');
```

These queries might indicate:
- Knowledge base gaps (add more content)
- Poor query phrasing (improve search)
- User confusion (update UI guidance)

## 📊 Database Maintenance

**Metrics Table Size**:
- ~1KB per query
- 1,000 queries/day = ~365 MB/year
- 10,000 queries/day = ~3.65 GB/year

**Retention Policy** (recommended):
```sql
-- Delete metrics older than 90 days
DELETE FROM chat_metrics WHERE created_at < NOW() - INTERVAL 90 DAY;

-- Or archive to data warehouse
INSERT INTO metrics_archive SELECT * FROM chat_metrics WHERE created_at < NOW() - INTERVAL 90 DAY;
DELETE FROM chat_metrics WHERE created_at < NOW() - INTERVAL 90 DAY;
```

**Scheduled Cleanup** (add to Laravel Scheduler):
```php
$schedule->command('db:query', ['DELETE FROM chat_metrics WHERE created_at < NOW() - INTERVAL 90 DAY'])
    ->weekly()
    ->sundays()
    ->at('02:00');
```

---

**Your chatbot is production-ready with enterprise-grade monitoring!** 🎉

**Quick Test**:
1. Go to http://localhost:8080
2. Ask: "What programs do you offer?"
3. Run: `docker compose exec app php artisan metrics:view --period=1`
4. Check: `curl http://localhost:8080/api/health`
