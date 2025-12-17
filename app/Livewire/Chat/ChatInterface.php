<?php

namespace App\Livewire\Chat;

use App\Models\Conversation;
use App\Models\KnowledgeBase;
use App\Models\ChatMetric;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use OpenAI\Laravel\Facades\OpenAI;

class ChatInterface extends Component
{
    public $messages = [];
    public $userInput = '';
    public $isLoading = false;
    public $currentConversationId = null;
    public $conversations = [];
    public $showSidebar = true;
    
    protected $listeners = ['message-sent' => '$refresh'];

    public function mount()
    {
        $this->loadConversations();
        $this->startNewConversation();
    }

    public function loadConversations()
    {
        $this->conversations = auth()->user()->conversations()
            ->select('id', 'user_message', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function($conv) {
                return [
                    'id' => $conv->id,
                    'preview' => Str::limit($conv->user_message, 40),
                    'time' => $conv->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }

    public function loadConversation($conversationId)
    {
        $conversation = Conversation::where('user_id', auth()->id())
            ->where('id', $conversationId)
            ->first();
            
        if ($conversation) {
            $this->currentConversationId = $conversationId;
            $this->messages = [
                [
                    'type' => 'user',
                    'content' => $conversation->user_message,
                    'time' => $conversation->created_at->format('h:i A'),
                ],
                [
                    'type' => 'ai',
                    'content' => $conversation->ai_response,
                    'time' => $conversation->created_at->format('h:i A'),
                    'sources' => json_decode($conversation->sources ?? '[]', true),
                ]
            ];
        }
    }

    public function startNewConversation()
    {
        $this->currentConversationId = null;
        $this->messages = [];
        $this->userInput = '';
    }

    public function toggleSidebar()
    {
        $this->showSidebar = !$this->showSidebar;
    }

    public function sendMessage()
    {
        if (empty(trim($this->userInput))) {
            return;
        }
        
        // Rate limiting: 20 messages per minute per user
        $key = 'chat-limit:' . auth()->id();
        
        if (RateLimiter::tooManyAttempts($key, 20)) {
            $this->dispatch('rate-limit-exceeded');
            $this->messages[] = [
                'type' => 'ai',
                'content' => 'You are sending messages too quickly. Please wait a moment before trying again.',
                'time' => now()->format('h:i A'),
                'sources' => [],
            ];
            $this->isLoading = false;
            return;
        }
        
        RateLimiter::hit($key, 60); // 60 seconds decay

        $userMessage = $this->userInput;
        $this->userInput = '';
        $this->isLoading = true;

        // Add user message to chat
        $this->messages[] = [
            'type' => 'user',
            'content' => $userMessage,
            'time' => now()->format('h:i A'),
        ];

        // Get AI response with sources
        $result = $this->getAIResponse($userMessage);
        $aiResponse = $result['response'];
        $sources = $result['sources'];

        // Add AI response to chat
        $this->messages[] = [
            'type' => 'ai',
            'content' => $aiResponse,
            'time' => now()->format('h:i A'),
            'sources' => $sources,
        ];

        // Save to database
        $conversation = Conversation::create([
            'user_id' => auth()->id(),
            'user_message' => $userMessage,
            'ai_response' => $aiResponse,
            'sources' => json_encode($sources),
        ]);
        
        $this->currentConversationId = $conversation->id;
        $this->loadConversations();

        $this->isLoading = false;
        
        // Scroll to bottom
        $this->dispatch('message-sent');
    }

    private function getAIResponse($message)
    {
        $startTime = microtime(true);
        $searchMethod = 'keyword';
        $hadFallback = false;
        $error = null;
        $resultCount = 0;
        
        try {
            // Try semantic search first (if embeddings exist)
            $hasEmbeddings = KnowledgeBase::whereNotNull('embedding')->exists();
            
            if ($hasEmbeddings) {
                try {
                    $searchMethod = 'semantic';
                    $result = $this->getSemanticResponse($message);
                    $resultCount = count($result['sources']);
                    
                    // Log metrics
                    $this->logMetrics($message, $startTime, $searchMethod, $resultCount, $hadFallback, $error);
                    
                    return $result;
                } catch (\Exception $e) {
                    // Fall back to keyword search on error
                    $hadFallback = true;
                    $error = 'Semantic search failed: ' . $e->getMessage();
                    Log::warning('Semantic search failed, falling back to keyword search', [
                        'query' => $message,
                        'error' => $e->getMessage(),
                        'user_id' => auth()->id(),
                    ]);
                }
            }
            
            // Fallback to keyword search
            $searchMethod = $hasEmbeddings ? 'keyword' : 'keyword'; // If we fell back, it's still keyword
            $result = $this->getKeywordResponse($message);
            $resultCount = count($result['sources']);
            
            // Check for empty results
            if ($resultCount === 0) {
                Log::info('No results found for query', [
                    'query' => $message,
                    'search_method' => $searchMethod,
                    'user_id' => auth()->id(),
                ]);
            }
            
            // Log metrics
            $this->logMetrics($message, $startTime, $searchMethod, $resultCount, $hadFallback, $error);
            
            return $result;
            
        } catch (\Exception $e) {
            $searchMethod = 'failed';
            $error = $e->getMessage();
            
            Log::error('Complete search failure', [
                'query' => $message,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            
            // Log failed metrics
            $this->logMetrics($message, $startTime, $searchMethod, 0, $hadFallback, $error);
            
            return [
                'response' => "I apologize, but I encountered an error while processing your question. Please try again or contact support if the problem persists.",
                'sources' => []
            ];
        }
    }
    
    private function logMetrics($query, $startTime, $searchMethod, $resultCount, $hadFallback, $error)
    {
        try {
            $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);
            
            ChatMetric::create([
                'user_id' => auth()->id(),
                'conversation_id' => $this->currentConversationId,
                'query' => $query,
                'response_time_ms' => $responseTimeMs,
                'search_method' => $searchMethod,
                'result_count' => $resultCount,
                'error' => $error,
                'had_fallback' => $hadFallback,
            ]);
        } catch (\Exception $e) {
            // Don't fail the request if metrics logging fails
            Log::error('Failed to log metrics', ['error' => $e->getMessage()]);
        }
    }
    
    private function getSemanticResponse($message)
    {
        try {
            // Generate embedding for user query
            $response = OpenAI::embeddings()->create([
                'model' => 'text-embedding-ada-002',
                'input' => $message,
            ]);
            
            $queryEmbedding = $response->embeddings[0]->embedding;
            
            // Find most similar chunks using cosine similarity
            $relevantChunks = $this->searchBySimilarity($queryEmbedding, 5);
            
            if ($relevantChunks->isEmpty()) {
                Log::info('Semantic search returned no results', [
                    'query' => $message,
                    'user_id' => auth()->id(),
                ]);
                
                return [
                    'response' => "I apologize, but I couldn't find relevant information about that in our Villa College knowledge base. Could you please rephrase your question?",
                    'sources' => []
                ];
            }
            
            // Build context from chunks
            $context = $relevantChunks->map(fn($chunk) => $chunk->content)->implode("\n\n");
            
            // Use GPT to generate natural response
            $gptResponse = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a helpful assistant for Villa College in the Maldives. Use the provided context to answer questions accurately and naturally. If the context doesn't contain enough information, say so. Always be concise but informative. Format responses in a clear, readable way."
                    ],
                    [
                        'role' => 'user',
                        'content' => "Context:\n{$context}\n\nQuestion: {$message}"
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);
            
            $answer = $gptResponse->choices[0]->message->content;
            $sources = $relevantChunks->map(fn($chunk) => $chunk->source_url)->unique()->values()->toArray();
            
            return [
                'response' => $answer,
                'sources' => $sources
            ];
            
        } catch (\Exception $e) {
            Log::error('GPT response generation failed', [
                'query' => $message,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            throw $e; // Re-throw to trigger fallback
        }
    }
    
    private function getKeywordResponse($message)
    {
        // Extract keywords from user message
        $keywords = $this->extractKeywords($message);
        
        // Search knowledge base for relevant content
        $relevantChunks = $this->searchKnowledgeBase($keywords);
        
        if ($relevantChunks->isEmpty()) {
            return [
                'response' => "I apologize, but I couldn't find specific information about that in our Villa College knowledge base. Could you please rephrase your question or ask about our programs, admissions, campus facilities, or student life?",
                'sources' => []
            ];
        }
        
        // Build response from relevant chunks with sources
        $response = $this->buildResponse($message, $relevantChunks);
        $sources = $relevantChunks->map(fn($chunk) => $chunk->source_url)->unique()->values()->toArray();
        
        return [
            'response' => $response,
            'sources' => $sources
        ];
    }
    
    private function searchBySimilarity($queryEmbedding, $limit = 5)
    {
        // Get all chunks with embeddings
        $chunks = KnowledgeBase::whereNotNull('embedding')->get();
        
        // Calculate cosine similarity for each chunk
        $scored = $chunks->map(function($chunk) use ($queryEmbedding) {
            $chunkEmbedding = is_string($chunk->embedding) 
                ? json_decode($chunk->embedding, true) 
                : $chunk->embedding;
            
            $similarity = $this->cosineSimilarity($queryEmbedding, $chunkEmbedding);
            $chunk->similarity_score = $similarity;
            return $chunk;
        });
        
        // Sort by similarity (highest first) and return top chunks
        return $scored->sortByDesc('similarity_score')->take($limit)->values();
    }
    
    private function cosineSimilarity($vec1, $vec2)
    {
        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;
        
        for ($i = 0; $i < count($vec1); $i++) {
            $dotProduct += $vec1[$i] * $vec2[$i];
            $magnitude1 += $vec1[$i] * $vec1[$i];
            $magnitude2 += $vec2[$i] * $vec2[$i];
        }
        
        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);
        
        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }
        
        return $dotProduct / ($magnitude1 * $magnitude2);
    }
    
    private function extractKeywords($message)
    {
        // Convert to lowercase and remove punctuation
        $cleaned = strtolower($message);
        $cleaned = preg_replace('/[^\w\s]/', '', $cleaned);
        
        // Common stop words to filter out
        $stopWords = ['what', 'how', 'when', 'where', 'who', 'why', 'is', 'are', 'the', 'a', 'an', 'in', 'on', 'at', 'to', 'for', 'of', 'about', 'me', 'tell', 'can', 'you', 'please', 'i', 'want', 'know', 'do', 'does'];
        
        // Split into words and filter
        $words = explode(' ', $cleaned);
        $keywords = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });
        
        return [
            'keywords' => array_values($keywords),
            'phrases' => $this->extractPhrases($words, $stopWords),
            'original' => $cleaned
        ];
    }
    
    private function extractPhrases($words, $stopWords)
    {
        $phrases = [];
        $filtered = array_filter($words, fn($w) => strlen($w) > 2 && !in_array($w, $stopWords));
        $filtered = array_values($filtered);
        
        // Extract 2-word phrases
        for ($i = 0; $i < count($filtered) - 1; $i++) {
            $phrases[] = $filtered[$i] . ' ' . $filtered[$i + 1];
        }
        
        // Extract 3-word phrases for more context
        for ($i = 0; $i < count($filtered) - 2; $i++) {
            $phrases[] = $filtered[$i] . ' ' . $filtered[$i + 1] . ' ' . $filtered[$i + 2];
        }
        
        return array_unique($phrases);
    }
    
    private function searchKnowledgeBase($extractedData)
    {
        if (empty($extractedData['keywords']) && empty($extractedData['phrases'])) {
            return collect([]);
        }
        
        // Get all potential matches
        $query = KnowledgeBase::query();
        
        foreach ($extractedData['keywords'] as $keyword) {
            $query->orWhere('content', 'LIKE', "%{$keyword}%");
        }
        
        $chunks = $query->get();
        
        // Score each chunk for relevance
        $scoredChunks = $chunks->map(function($chunk) use ($extractedData) {
            $score = $this->calculateRelevanceScore($chunk, $extractedData);
            $chunk->relevance_score = $score;
            return $chunk;
        });
        
        // Sort by relevance score (highest first) and return top 5
        return $scoredChunks
            ->sortByDesc('relevance_score')
            ->take(5)
            ->values();
    }
    
    private function calculateRelevanceScore($chunk, $extractedData)
    {
        $content = strtolower($chunk->content);
        $score = 0;
        
        // Phrase matching (highest priority) - worth 10 points each
        foreach ($extractedData['phrases'] as $phrase) {
            $phraseCount = substr_count($content, $phrase);
            $score += $phraseCount * 10;
        }
        
        // Keyword frequency - worth 3 points each
        foreach ($extractedData['keywords'] as $keyword) {
            $keywordCount = substr_count($content, $keyword);
            $score += $keywordCount * 3;
        }
        
        // Keyword position bonus - if keyword appears in first 200 chars (likely title/intro)
        $firstPart = substr($content, 0, 200);
        foreach ($extractedData['keywords'] as $keyword) {
            if (strpos($firstPart, $keyword) !== false) {
                $score += 5; // Position bonus
            }
        }
        
        // Exact phrase in first 200 characters gets massive bonus
        foreach ($extractedData['phrases'] as $phrase) {
            if (strpos($firstPart, $phrase) !== false) {
                $score += 15;
            }
        }
        
        // Multiple keyword matches bonus (indicates comprehensive content)
        $uniqueMatches = 0;
        foreach ($extractedData['keywords'] as $keyword) {
            if (strpos($content, $keyword) !== false) {
                $uniqueMatches++;
            }
        }
        if ($uniqueMatches >= 3) {
            $score += 8;
        }
        
        return $score;
    }
    
    private function buildResponse($question, $chunks)
    {
        if ($chunks->isEmpty()) {
            return "I couldn't find relevant information about that.";
        }
        
        // Combine relevant content
        $context = $chunks->pluck('content')->implode("\n\n");
        
        // If context is too short, provide direct answer
        if (strlen($context) < 100) {
            return $context;
        }
        
        // Build a structured response with most relevant content first
        $response = "Based on Villa College information:\n\n";
        
        // Add each chunk as a point (already sorted by relevance)
        foreach ($chunks as $index => $chunk) {
            $snippet = Str::limit($chunk->content, 300);
            $response .= $snippet;
            
            // Add spacing between chunks
            if ($index < $chunks->count() - 1) {
                $response .= "\n\n";
            }
        }
        
        $response .= "\n\nIf you need more specific information, please feel free to ask!";
        
        return $response;
    }

    public function render()
    {
        return view('livewire.chat.chat-interface')->layout('components.layouts.app');
    }
}
