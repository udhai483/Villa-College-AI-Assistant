# Villa College AI Assistant - Complete Project Structure

```
Assignment/
│
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── ScrapeVillaCollege.php         # Web scraping Artisan command
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Auth/
│   │   │       └── GoogleController.php       # Google OAuth controller
│   │   │
│   │   └── Middleware/
│   │       └── CheckDomainRestriction.php     # Domain validation middleware
│   │
│   ├── Livewire/
│   │   ├── Auth/
│   │   │   └── Login.php                      # Login Livewire component
│   │   │
│   │   └── Chat/
│   │       └── ChatInterface.php              # Chat Livewire component
│   │
│   └── Models/
│       ├── User.php                           # User model with domain check
│       ├── Conversation.php                   # Chat history model
│       └── KnowledgeBase.php                  # RAG knowledge base model
│
├── bootstrap/
│   └── app.php                                # Application bootstrap
│
├── config/
│   ├── app.php                                # App configuration
│   ├── auth.php                               # Authentication config
│   ├── database.php                           # Database configuration
│   ├── livewire.php                           # Livewire configuration
│   ├── openai.php                             # OpenAI API configuration
│   ├── services.php                           # Third-party services (Google OAuth)
│   └── session.php                            # Session configuration
│
├── database/
│   └── migrations/
│       ├── 0001_01_01_000000_create_users_table.php
│       ├── 0001_01_01_000001_create_cache_table.php
│       ├── 0001_01_01_000002_create_jobs_table.php
│       ├── 2024_01_01_000003_create_conversations_table.php
│       └── 2024_01_01_000004_create_knowledge_base_table.php
│
├── docker/
│   ├── nginx/
│   │   └── default.conf                       # Nginx server configuration
│   │
│   └── php/
│       └── Dockerfile                         # PHP 8.2 container definition
│
├── public/
│   ├── .htaccess                              # Apache configuration
│   ├── favicon.svg                            # Site favicon
│   ├── index.php                              # Application entry point
│   └── robots.txt                             # SEO robots file
│
├── resources/
│   ├── css/
│   │   └── app.css                            # Tailwind CSS main file
│   │
│   ├── js/
│   │   ├── app.js                             # Main JavaScript file
│   │   └── bootstrap.js                       # JavaScript bootstrap (Axios)
│   │
│   └── views/
│       ├── components/
│       │   └── layouts/
│       │       ├── app.blade.php              # Authenticated layout
│       │       └── guest.blade.php            # Guest layout
│       │
│       └── livewire/
│           ├── auth/
│           │   └── login.blade.php            # Login UI template
│           │
│           └── chat/
│               └── chat-interface.blade.php   # Chat UI template
│
├── routes/
│   ├── artisan.php                            # Artisan commands
│   ├── console.php                            # Console routes
│   └── web.php                                # Web routes
│
├── storage/
│   ├── app/
│   │   └── .gitignore
│   │
│   ├── framework/
│   │   ├── cache/
│   │   │   └── .gitignore
│   │   ├── sessions/
│   │   │   └── .gitignore
│   │   └── views/
│   │       └── .gitignore
│   │
│   └── logs/
│       └── .gitignore
│
├── .env.example                               # Environment variables template
├── .gitignore                                 # Git ignore rules
├── artisan                                    # Laravel Artisan CLI
├── composer.json                              # PHP dependencies
├── docker-compose.yml                         # Docker orchestration
├── HOW-TO-RUN.md                             # Complete setup instructions
├── package.json                               # Node.js dependencies
├── postcss.config.js                          # PostCSS configuration
├── QUICKSTART.md                              # Quick start guide
├── README.md                                  # Project documentation
├── setup.bat                                  # Windows setup script
├── setup.sh                                   # Linux/Mac setup script
├── tailwind.config.js                         # Tailwind CSS configuration
├── UI-DESIGN.md                              # UI design documentation
└── vite.config.js                            # Vite build configuration

```

## 📊 File Count Summary

### Backend (PHP/Laravel)
- **Models**: 3 files (User, Conversation, KnowledgeBase)
- **Controllers**: 1 file (GoogleController)
- **Middleware**: 1 file (CheckDomainRestriction)
- **Livewire Components**: 2 files (Login, ChatInterface)
- **Console Commands**: 1 file (ScrapeVillaCollege)
- **Migrations**: 5 files
- **Config Files**: 7 files

### Frontend (Blade/CSS/JS)
- **Blade Templates**: 4 files
- **CSS**: 1 file (Tailwind)
- **JavaScript**: 2 files

### Infrastructure
- **Docker**: 2 files (Dockerfile, docker-compose.yml)
- **Build Config**: 3 files (vite, tailwind, postcss)
- **Scripts**: 2 files (setup.bat, setup.sh)

### Documentation
- **Docs**: 5 files (README, QUICKSTART, HOW-TO-RUN, UI-DESIGN, PROJECT-STRUCTURE)

## 🎯 Key Directories Explained

### `/app`
Core application logic - Models, Controllers, Livewire components, Commands

### `/config`
All configuration files for Laravel, services, and packages

### `/database`
Database migrations for schema creation

### `/docker`
Docker configuration for containerization

### `/public`
Publicly accessible files (entry point, assets)

### `/resources`
Frontend assets (views, CSS, JavaScript)

### `/routes`
Application routing definitions

### `/storage`
File storage, logs, and framework files

## 🔗 Important Relationships

```
User Model
  ├─> has many Conversations
  └─> has method isAuthorizedDomain()

GoogleController
  ├─> uses Laravel Socialite
  ├─> validates domain
  └─> creates/updates User

CheckDomainRestriction Middleware
  └─> validates User domain on each request

Login Component (Livewire)
  └─> renders login.blade.php

ChatInterface Component (Livewire)
  ├─> loads Conversations
  ├─> manages chat state
  └─> renders chat-interface.blade.php

KnowledgeBase Model
  ├─> stores scraped content
  └─> will store embeddings for RAG
```

## 📦 Dependencies

### PHP (via Composer)
- laravel/framework: ^11.0
- livewire/livewire: ^3.4
- laravel/socialite: ^5.12
- openai-php/laravel: ^0.8.1
- guzzlehttp/guzzle: ^7.8

### Node.js (via NPM)
- tailwindcss: ^3.4.0
- vite: ^5.0
- laravel-vite-plugin: ^1.0
- @tailwindcss/forms: ^0.5.7
- autoprefixer: ^10.4.16

## 🐳 Docker Services

### app
- PHP 8.2-FPM
- Composer
- Node.js & NPM
- Laravel application

### nginx
- Nginx web server
- Port 8080 → 80
- Serves public directory

### db
- MySQL 8.0
- Port 3306
- Database: laravel_ai
- Persistent volume

## 🗄️ Database Tables

1. **users** - User accounts
2. **conversations** - Chat history
3. **knowledge_base** - RAG data
4. **sessions** - User sessions
5. **cache** - Application cache
6. **jobs** - Queue jobs
7. **password_reset_tokens** - Password resets
8. **migrations** - Migration history

## 🎨 Frontend Assets

### CSS
- Tailwind utility classes
- Custom components (btn-primary, card, input-field)
- Responsive design
- Inter font family

### JavaScript
- Axios for HTTP requests
- Livewire for reactivity
- Auto-scroll chat functionality

## 🔒 Security Features

1. **CSRF Protection** - Laravel default
2. **Domain Whitelisting** - Custom middleware
3. **OAuth 2.0** - Google authentication
4. **Session Management** - Database-backed
5. **Server-Side Validation** - Email domain check

## 📱 Responsive Breakpoints

- **Mobile**: < 640px
- **Tablet**: 640px - 1024px
- **Desktop**: > 1024px

All UI components are fully responsive across these breakpoints.
