<div class="h-screen flex bg-gray-50">
    <!-- Sidebar -->
    <div class="{{ $showSidebar ? 'w-64' : 'w-0' }} bg-white border-r border-gray-200 transition-all duration-300 overflow-hidden">
        <div class="h-full flex flex-col">
            <!-- Sidebar Header -->
            <div class="p-4 border-b border-gray-200">
                <button wire:click="startNewConversation" 
                        class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition flex items-center justify-center space-x-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="font-medium">New Chat</span>
                </button>
            </div>
            
            <!-- Conversation History -->
            <div class="flex-1 overflow-y-auto p-3 space-y-2">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide px-2 mb-2">Recent Conversations</h3>
                @forelse($conversations as $conv)
                    <button wire:click="loadConversation({{ $conv['id'] }})"
                            class="w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100 transition {{ $currentConversationId == $conv['id'] ? 'bg-primary-50 border border-primary-200' : 'border border-transparent' }}">
                        <p class="text-sm text-gray-800 truncate">{{ $conv['preview'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $conv['time'] }}</p>
                    </button>
                @empty
                    <p class="text-xs text-gray-500 text-center py-4">No conversations yet</p>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <!-- Sidebar Toggle -->
                    <button wire:click="toggleSidebar" class="p-2 hover:bg-gray-100 rounded-lg transition">
                        <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    
                    <div class="h-10 w-10 bg-primary-600 rounded-lg flex items-center justify-center">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Villa College AI Assistant</h1>
                        <p class="text-xs text-gray-500">Powered by RAG Technology</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Clear Chat Button -->
                    @if(!empty($messages))
                        <button wire:click="startNewConversation" 
                                class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition flex items-center space-x-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Clear</span>
                        </button>
                    @endif
                    
                    <div class="hidden sm:flex items-center space-x-2 px-3 py-2 bg-gray-100 rounded-lg">
                        <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="h-7 w-7 rounded-full">
                        <div class="text-right">
                            <p class="text-xs font-medium text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-700 transition">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Chat Messages Area -->
    <div class="flex-1 overflow-y-auto px-4 py-6" id="chat-container">
        <div class="max-w-4xl mx-auto space-y-4">
            @if(empty($messages))
                <!-- Welcome Message -->
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center h-16 w-16 bg-primary-100 rounded-full mb-4">
                        <svg class="h-8 w-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Welcome to Villa College AI Assistant!</h3>
                    <p class="text-gray-600 max-w-md mx-auto">
                        Ask me anything about Villa College. I'm here to help you with information about courses, admissions, faculty, and more.
                    </p>
                    <div class="mt-6 flex flex-wrap justify-center gap-2">
                        <button wire:click="$set('userInput', 'What courses does Villa College offer?')" 
                                class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
                            What courses does Villa College offer?
                        </button>
                        <button wire:click="$set('userInput', 'How do I apply for admission?')" 
                                class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
                            How do I apply for admission?
                        </button>
                        <button wire:click="$set('userInput', 'Tell me about the faculty')" 
                                class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
                            Tell me about the faculty
                        </button>
                    </div>
                </div>
            @else
                <!-- Messages -->
                @foreach($messages as $message)
                    <div class="flex {{ $message['type'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="flex items-start space-x-2 max-w-2xl {{ $message['type'] === 'user' ? 'flex-row-reverse space-x-reverse' : '' }}">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                @if($message['type'] === 'user')
                                    <img src="{{ auth()->user()->avatar }}" alt="User" class="h-8 w-8 rounded-full">
                                @else
                                    <div class="h-8 w-8 bg-primary-600 rounded-full flex items-center justify-center">
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Message Content -->
                            <div class="flex flex-col {{ $message['type'] === 'user' ? 'items-end' : 'items-start' }}">
                                <div class="px-4 py-3 rounded-2xl {{ $message['type'] === 'user' ? 'bg-primary-600 text-white' : 'bg-white text-gray-800 shadow-sm border border-gray-200' }}">
                                    <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $message['content'] }}</p>
                                    
                                    <!-- Source URLs for AI messages -->
                                    @if($message['type'] === 'ai' && isset($message['sources']) && !empty($message['sources']))
                                        <div class="mt-3 pt-3 border-t border-gray-200">
                                            <p class="text-xs font-semibold text-gray-500 mb-2">Sources:</p>
                                            <div class="space-y-1">
                                                @foreach($message['sources'] as $source)
                                                    <a href="{{ $source }}" target="_blank" 
                                                       class="flex items-center space-x-1 text-xs text-primary-600 hover:text-primary-700 hover:underline">
                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                        </svg>
                                                        <span class="truncate">{{ Str::limit($source, 50) }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-500 mt-1 px-2">{{ $message['time'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Loading Indicator -->
                @if($isLoading)
                    <div class="flex justify-start">
                        <div class="flex items-start space-x-2 max-w-2xl">
                            <div class="h-8 w-8 bg-primary-600 rounded-full flex items-center justify-center">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <div class="px-4 py-3 bg-white rounded-2xl shadow-sm border border-gray-200">
                                <div class="flex space-x-2">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Input Area -->
    <div class="bg-white border-t border-gray-200 px-4 py-4">
        <div class="max-w-4xl mx-auto">
            <form wire:submit.prevent="sendMessage" class="flex items-end space-x-3">
                <div class="flex-1">
                    <textarea 
                        wire:model="userInput"
                        rows="1"
                        placeholder="Ask me anything about Villa College..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none transition"
                        style="min-height: 48px; max-height: 120px;"
                        @keydown.enter.prevent="if(!$event.shiftKey) { $wire.sendMessage(); }"
                    ></textarea>
                </div>
                <button 
                    type="submit"
                    :disabled="isLoading || !userInput.trim()"
                    class="px-5 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2 shadow-sm hover:shadow-md">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <span class="font-medium">Send</span>
                </button>
            </form>
            <p class="text-xs text-gray-500 text-center mt-2">
                Press Enter to send, Shift+Enter for new line
            </p>
        </div>
    </div>
    </div>
    <!-- End Main Content -->

    @script
    <script>
        $wire.on('message-sent', () => {
            setTimeout(() => {
                const container = document.getElementById('chat-container');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }, 100);
        });
    </script>
    @endscript
</div>
