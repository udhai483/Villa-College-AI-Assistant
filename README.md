# Villa College AI Assistant

A modern, intelligent chatbot application for Villa College built with Laravel, Livewire, and Tailwind CSS. This application uses Retrieval-Augmented Generation (RAG) to provide accurate, context-aware responses based on Villa College's website data.

## Features

- 🔐 **Secure Google OAuth Authentication** with domain restriction (@villacollege.edu.mv and @students.villacollege.edu.mv)
- 💬 **Modern Chat Interface** with real-time messaging
- 🤖 **AI-Powered Responses** using RAG technology
- 📱 **Fully Responsive Design** optimized for all devices
- 💾 **Conversation History** persisted in database
- 🎨 **Beautiful UI** with Tailwind CSS
- 🐳 **Docker Support** for easy deployment

## Tech Stack

- **Backend**: Laravel 11
- **Frontend**: Laravel Livewire 3 + Tailwind CSS 3
- **Database**: MySQL 8.0
- **AI Integration**: OpenAI PHP Client
- **Authentication**: Laravel Socialite (Google OAuth)
- **Containerization**: Docker & Docker Compose

## Prerequisites

Before you begin, ensure you have installed:
- Docker Desktop (Windows/Mac) or Docker Engine (Linux)
- Docker Compose
- Git

## Installation & Setup

### 1. Clone the Repository

```bash
git clone <repository-url>
cd Assignment
```

### 2. Configure Environment Variables

Copy the example environment file:

```bash
copy .env.example .env
```

Edit `.env` file and configure:

**Google OAuth Settings:**
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable Google+ API
4. Create OAuth 2.0 credentials
5. Add authorized redirect URI: `http://localhost:8080/auth/google/callback`
6. Copy Client ID and Client Secret to `.env`:

```env
GOOGLE_CLIENT_ID=your-google-client-id-here
GOOGLE_CLIENT_SECRET=your-google-client-secret-here
GOOGLE_REDIRECT_URI=http://localhost:8080/auth/google/callback
```

**OpenAI API Settings:**
1. Go to [OpenAI Platform](https://platform.openai.com/)
2. Create an API key
3. Add to `.env`:

```env
OPENAI_API_KEY=your-openai-api-key-here
```

### 3. Build and Start Docker Containers

```bash
docker-compose up -d --build
```

This will:
- Build the PHP application container
- Start Nginx web server
- Start MySQL database
- Set up networking between containers

### 4. Install Dependencies

```bash
# Install PHP dependencies
docker-compose exec app composer install

# Install Node.js dependencies
docker-compose exec app npm install
```

### 5. Generate Application Key

```bash
docker-compose exec app php artisan key:generate
```

### 6. Run Database Migrations

```bash
docker-compose exec app php artisan migrate
```

### 7. Build Frontend Assets

```bash
docker-compose exec app npm run build
```

### 8. Set Permissions (Linux/Mac only)

```bash
docker-compose exec app chown -R www-data:www-data /var/www/storage
docker-compose exec app chown -R www-data:www-data /var/www/bootstrap/cache
```

## Running the Application

### Start the Application

```bash
docker-compose up -d
```

The application will be available at: **http://localhost:8080**

### Stop the Application

```bash
docker-compose down
```

### View Logs

```bash
# All containers
docker-compose logs -f

# Specific container
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f db
```

## Development Workflow

### Watch for Frontend Changes

For development with hot reloading:

```bash
docker-compose exec app npm run dev
```

### Access Container Shell

```bash
# PHP/Laravel container
docker-compose exec app bash

# MySQL database
docker-compose exec db mysql -u laravel_user -plaravel_password laravel_ai
```

### Run Artisan Commands

```bash
docker-compose exec app php artisan <command>
```

### Clear Caches

```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

## Scraping Villa College Data (RAG Setup)

To populate the knowledge base with Villa College data, you'll need to create and run a scraping command:

```bash
docker-compose exec app php artisan scrape:villacollege
```

*Note: The scraping command implementation is a placeholder and needs to be completed with actual scraping logic.*

## Project Structure

```
Assignment/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Auth/
│   │   │       └── GoogleController.php
│   │   └── Middleware/
│   │       └── CheckDomainRestriction.php
│   ├── Livewire/
│   │   ├── Auth/
│   │   │   └── Login.php
│   │   └── Chat/
│   │       └── ChatInterface.php
│   └── Models/
│       ├── User.php
│       ├── Conversation.php
│       └── KnowledgeBase.php
├── config/
│   ├── auth.php
│   ├── database.php
│   ├── livewire.php
│   ├── openai.php
│   └── services.php
├── database/
│   └── migrations/
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── Dockerfile
├── public/
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   └── bootstrap.js
│   └── views/
│       ├── components/
│       │   └── layouts/
│       │       ├── app.blade.php
│       │       └── guest.blade.php
│       └── livewire/
│           ├── auth/
│           │   └── login.blade.php
│           └── chat/
│               └── chat-interface.blade.php
├── routes/
│   └── web.php
├── docker-compose.yml
├── tailwind.config.js
└── vite.config.js
```

## Key Features Implementation

### 1. Authentication
- Google OAuth integration with Laravel Socialite
- Server-side domain restriction middleware
- Secure session management

### 2. Chat Interface
- Real-time messaging with Livewire
- Conversation history persistence
- Mobile-responsive design
- User avatar display
- Message timestamps

### 3. AI Integration
- RAG pattern for grounded responses
- Vector similarity search (to be implemented)
- OpenAI API integration

### 4. Security
- Domain whitelisting (@villacollege.edu.mv, @students.villacollege.edu.mv)
- CSRF protection
- Session-based authentication
- Secure OAuth flow

## Troubleshooting

### Docker Issues

**Containers won't start:**
```bash
docker-compose down -v
docker-compose up -d --build
```

**Permission denied errors:**
```bash
docker-compose exec app chown -R www-data:www-data /var/www
```

### Database Issues

**Connection refused:**
- Ensure the database container is running: `docker-compose ps`
- Check database credentials in `.env`
- Verify DB_HOST is set to `db` (container name)

**Migrations fail:**
```bash
docker-compose exec app php artisan migrate:fresh
```

### Frontend Issues

**Assets not loading:**
```bash
docker-compose exec app npm run build
docker-compose exec app php artisan config:clear
```

### Authentication Issues

**Google OAuth fails:**
- Verify Google Client ID and Secret in `.env`
- Check redirect URI matches Google Console settings
- Ensure authorized domains are configured in Google Console

## Next Steps for Production

1. **Complete RAG Implementation**
   - Implement web scraping command
   - Add vector embedding generation
   - Create semantic search functionality

2. **Optimize Performance**
   - Add Laravel Octane with OpenSwoole
   - Implement caching strategies
   - Optimize database queries

3. **PWA Features**
   - Add service worker
   - Create manifest.json
   - Enable offline functionality

4. **Testing**
   - Write unit tests
   - Add feature tests
   - Implement E2E testing

5. **Deployment**
   - Set up CI/CD pipeline
   - Configure production environment
   - Add monitoring and logging

## License

This project is proprietary software for Villa College.

## Support

For support, please contact the development team.
