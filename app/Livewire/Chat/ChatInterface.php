<?php

namespace App\Livewire\Chat;

use App\Models\Conversation;
use App\Models\KnowledgeBase;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Str;

class ChatInterface extends Component
{
    public $messages = [];
    public $userInput = '';
    public $isLoading = false;
    public $currentConversationId = null;
    public $conversations = [];
    public $showSidebar = true;

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
        
        return array_values($keywords);
    }
    
    private function searchKnowledgeBase($keywords)
    {
        if (empty($keywords)) {
            return collect([]);
        }
        
        // Build query to search for keywords in content
        $query = KnowledgeBase::query();
        
        foreach ($keywords as $keyword) {
            $query->orWhere('content', 'LIKE', "%{$keyword}%");
        }
        
        // Get top 3 most relevant chunks
        return $query->limit(3)->get();
    }
    
    private function buildResponse($question, $chunks)
    {
        // Combine relevant content
        $context = $chunks->pluck('content')->implode("\n\n");
        
        // If context is too short, provide direct answer
        if (strlen($context) < 100) {
            return $context;
        }
        
        // Build a structured response
        $response = "Based on Villa College information:\n\n";
        
        // Add each chunk as a point
        foreach ($chunks as $index => $chunk) {
            $snippet = Str::limit($chunk->content, 250);
            $response .= $snippet . "\n\n";
        }
        
        $response .= "If you need more specific information, please feel free to ask!";
        
        return $response;
    }

    public function render()
    {
        return view('livewire.chat.chat-interface')->layout('components.layouts.app');
    }
}
