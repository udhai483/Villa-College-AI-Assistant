<?php

namespace App\Livewire\Chat;

use App\Models\Conversation;
use Livewire\Component;
use Livewire\Attributes\On;

class ChatInterface extends Component
{
    public $messages = [];
    public $userInput = '';
    public $isLoading = false;

    public function mount()
    {
        $this->loadConversationHistory();
    }

    public function loadConversationHistory()
    {
        $conversations = auth()->user()->conversations()->take(50)->get();
        
        $this->messages = [];
        foreach ($conversations as $conversation) {
            $this->messages[] = [
                'type' => 'user',
                'content' => $conversation->user_message,
                'time' => $conversation->created_at->format('h:i A'),
            ];
            $this->messages[] = [
                'type' => 'ai',
                'content' => $conversation->ai_response,
                'time' => $conversation->created_at->format('h:i A'),
            ];
        }
        
        $this->messages = array_reverse($this->messages);
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

        // Simulate AI response (Replace with actual RAG implementation)
        $aiResponse = $this->getAIResponse($userMessage);

        // Add AI response to chat
        $this->messages[] = [
            'type' => 'ai',
            'content' => $aiResponse,
            'time' => now()->format('h:i A'),
        ];

        // Save to database
        Conversation::create([
            'user_id' => auth()->id(),
            'user_message' => $userMessage,
            'ai_response' => $aiResponse,
        ]);

        $this->isLoading = false;
        
        // Scroll to bottom
        $this->dispatch('message-sent');
    }

    private function getAIResponse($message)
    {
        // TODO: Implement actual RAG logic here
        // 1. Search knowledge base for relevant context
        // 2. Create prompt with context
        // 3. Call OpenAI API
        // 4. Return response
        
        // Placeholder response
        return "Thank you for your question about Villa College. This is a placeholder response. The actual AI response will be implemented using RAG (Retrieval-Augmented Generation) with the scraped Villa College website data. The system will search the knowledge base for relevant information and generate a contextual response based only on the available data.";
    }

    public function render()
    {
        return view('livewire.chat.chat-interface')->layout('components.layouts.app');
    }
}
