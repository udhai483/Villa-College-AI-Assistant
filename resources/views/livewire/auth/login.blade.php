<div x-data="{ darkMode: @js($darkMode) }" 
     x-init="$watch('darkMode', value => $el.classList.toggle('dark', value))"
     @dark-mode-toggled.window="darkMode = $event.detail.darkMode"
     :class="{ 'dark': darkMode }"
     class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 via-white to-primary-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 px-4 sm:px-6 lg:px-8 relative overflow-hidden transition-colors duration-300">
    
    <!-- Geometric Background Pattern -->
    <div class="absolute inset-0 opacity-5 dark:opacity-10">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <circle cx="20" cy="20" r="1" fill="currentColor" class="text-primary-600 dark:text-primary-400"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)" />
        </svg>
    </div>

    <!-- Dark Mode Toggle -->
    <button wire:click="toggleDarkMode" 
            class="absolute top-4 right-4 p-3 rounded-full bg-white dark:bg-gray-800 shadow-lg hover:shadow-xl transition-all duration-300 group z-10">
        <svg x-show="!darkMode" class="w-5 h-5 text-gray-600 group-hover:text-yellow-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
        <svg x-show="darkMode" class="w-5 h-5 text-yellow-400 group-hover:text-yellow-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
    </button>

    <div class="max-w-4xl w-full space-y-12 relative z-10">
        <!-- Logo and Header -->
        <div class="text-center space-y-6">
            <div class="mx-auto h-20 w-20 bg-gradient-to-br from-primary-600 to-primary-700 dark:from-primary-500 dark:to-primary-600 rounded-2xl flex items-center justify-center shadow-2xl transform hover:scale-105 transition-transform duration-300">
                <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
            </div>
            <div class="space-y-3">
                <h2 class="text-4xl font-extrabold text-gray-900 dark:text-white flex items-center justify-center gap-3 flex-wrap">
                    <span>Villa College AI Assistant</span>
                    <!-- Live Status Indicator -->
                    <span class="flex items-center gap-2 text-sm font-normal text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-3 py-1 rounded-full">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        System Online
                    </span>
                </h2>
                <p class="text-base text-gray-600 dark:text-gray-400">
                    Your intelligent chatbot powered by RAG technology
                </p>
                
                <!-- What's New Badge -->
                <div class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 px-4 py-2 rounded-full">
                    <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold text-purple-800 dark:text-purple-300 bg-purple-200 dark:bg-purple-800 rounded-full">NEW</span>
                    <span class="text-sm text-purple-900 dark:text-purple-200 font-medium">Enhanced with Semantic Search & GPT-4</span>
                </div>
            </div>
        </div>

        <!-- Login Card - Centered -->
        <div class="max-w-lg mx-auto">
            <div class="card bg-white dark:bg-gray-800 shadow-2xl border border-gray-200 dark:border-gray-700 transition-colors duration-300">
                <div class="px-8 py-10">
                        <!-- Error Message -->
                @if (session('error'))
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    {{ session('error') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                        <!-- Welcome Text -->
                        <div class="text-center mb-8">
                            <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Welcome Back!</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">Sign in with your Villa College account</p>
                        </div>

                        <!-- Google Sign In Button with Gradient -->
                        <div class="space-y-4">
                            <a href="{{ route('auth.google') }}" 
                               id="google-signin-btn"
                               class="w-full flex items-center justify-center gap-3 px-6 py-4 border border-transparent rounded-xl shadow-lg bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 dark:from-primary-500 dark:to-primary-600 transition-all duration-300 group transform hover:scale-105">
                                <svg class="w-6 h-6 bg-white rounded p-0.5" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                <span class="text-white font-semibold text-lg">Sign in with Google</span>
                                <svg class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>

                            <!-- Skeleton Loader (hidden by default) -->
                            <div id="skeleton-loader" class="hidden">
                                <div class="animate-pulse space-y-4">
                                    <div class="h-14 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                                    <div class="space-y-2">
                                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Domain Restriction Notice -->
                            <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-800">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-blue-400 dark:text-blue-300 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700 dark:text-blue-300 font-medium">
                                            @if(config('app.env') === 'production')
                                                Authorized Domains Only
                                            @else
                                                Development Mode - Any Email Allowed
                                            @endif
                                        </p>
                                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                            @if(config('app.env') === 'production')
                                                Only <span class="font-semibold">@villacollege.edu.mv</span> and 
                                                <span class="font-semibold">@students.villacollege.edu.mv</span> emails are allowed.
                                            @else
                                                Testing mode active - domain restriction disabled
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Value Propositions - Below login card in three-column grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 flex flex-col h-full">
                <div class="flex items-start gap-4 flex-grow">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Instant Course Info</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Get immediate answers about programs, admissions, and schedules</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 flex flex-col h-full">
                <div class="flex items-start gap-4 flex-grow">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">24/7 Student Support</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Available anytime to help with your questions and concerns</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 flex flex-col h-full">
                <div class="flex items-start gap-4 flex-grow">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Research Assistance</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Smart AI-powered search across our knowledge base</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-xs text-gray-500 dark:text-gray-400">
            <p>© 2025 Villa College. All rights reserved.</p>
            <p class="mt-1">Powered by AI & RAG Technology</p>
        </div>
    </div>

    <!-- Skeleton Loading Script -->
    <script>
        document.getElementById('google-signin-btn')?.addEventListener('click', function(e) {
            const loader = document.getElementById('skeleton-loader');
            const btn = document.getElementById('google-signin-btn');
            if (loader && btn) {
                btn.classList.add('hidden');
                loader.classList.remove('hidden');
            }
        });
    </script>
</div>