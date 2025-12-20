# For Instructor: Project Setup Guide

Dear Instructor,

This guide will help you quickly set up and test the Villa College AI Assistant project.

## Project Overview

This is a Laravel-based AI chatbot that uses Retrieval-Augmented Generation (RAG) to answer questions about Villa College. It features:

- Google OAuth authentication with domain restriction
- AI-powered responses using OpenAI's GPT-4o-mini
- Vector embeddings for semantic search
- Web scraping of Villa College website
- Real-time chat interface with Livewire

## Prerequisites

- Docker Desktop installed and running
- Git installed
- Google OAuth credentials (for testing login)
- OpenAI API key (for testing AI features)

## Quick Setup (3 Steps)

### 1. Clone the Repository

```bash
git clone [REPOSITORY_URL]
cd villa-college-ai-assistant
```

### 2. Configure Environment

Create `.env` file from `.env.example`:

```bash
cp .env.example .env
```

Edit `.env` and add:
- Your Google OAuth credentials (`GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`)
- Your OpenAI API key (`OPENAI_API_KEY`)

**Note**: For quick testing, I can provide test credentials separately if needed.

### 3. Start the Application

**Single command to start everything:**

```bash
docker-compose up -d
```

This will:
- Build and start all containers (Nginx, PHP, MySQL)
- Install all dependencies
- Run database migrations
- Build frontend assets
- Make the application ready to use

**Access the application at:** http://localhost:8080

## Seeding the Knowledge Base

To populate the knowledge base with Villa College data:

```bash
docker-compose exec app php artisan vc:seed-knowledge
```

This command:
- Scrapes villacollege.edu.mv website
- Extracts and cleans content
- Generates AI embeddings for semantic search
- Stores everything in the database

**Expected time**: 2-5 minutes depending on internet speed and OpenAI API rate limits.

## Testing the Application

### 1. Authentication Test

1. Visit http://localhost:8080
2. Click "Sign in with Google"
3. Try logging in with:
   - ✅ **Valid**: An email ending with `@villacollege.edu.mv` or `@students.villacollege.edu.mv`
   - ❌ **Invalid**: Any other email domain (should be rejected)

### 2. Chat Functionality Test

Once logged in:
1. Type a question like: "What programs does Villa College offer?"
2. Verify you receive an AI-generated response
3. Check that source URLs are displayed below the response
4. Verify the response is relevant to Villa College

### 3. RAG System Test

Ask specific questions to test the RAG implementation:
- "What are the admission requirements?"
- "Tell me about the campus facilities"
- "What is the vision of Villa College?"

The system should provide accurate, context-aware responses with source citations.

## Project Structure Highlights

```
villa-college-ai-assistant/
├── app/
│   ├── Console/Commands/
│   │   ├── SeedKnowledge.php          # Main seeding command (vc:seed-knowledge)
│   │   ├── ScrapeVillaCollege.php     # Web scraper
│   │   └── GenerateEmbeddings.php     # Embedding generator
│   ├── Http/
│   │   ├── Controllers/Auth/
│   │   │   └── GoogleController.php   # OAuth + domain validation
│   │   └── Middleware/
│   │       └── CheckDomainRestriction.php  # Domain security middleware
│   ├── Livewire/Chat/
│   │   └── ChatInterface.php          # Main chat component with RAG logic
│   └── Models/
│       ├── KnowledgeBase.php          # Stores scraped data + embeddings
│       └── Conversation.php           # Chat history
├── docker-compose.yml                  # Single-command deployment
├── README.md                          # Complete documentation
└── .env.example                       # Configuration template
```

## Key Technical Features to Review

### 1. RAG Implementation (app/Livewire/Chat/ChatInterface.php)

The RAG system works in these steps:
1. User sends a question
2. System generates embedding for the question using OpenAI
3. Performs cosine similarity search against knowledge base embeddings
4. Retrieves top 5 most relevant chunks
5. Builds context from retrieved chunks
6. Sends context + question to GPT-4o-mini
7. Returns AI-generated response with source URLs

**Key method**: `askQuestion()` in ChatInterface.php

### 2. Domain Security (Multi-Layer)

Three layers of security:
1. **Model-level**: `User::isAuthorizedDomain()` checks email domain
2. **Middleware**: `CheckDomainRestriction` validates on every request
3. **OAuth Controller**: Pre-authentication validation before user creation

**Files to review**:
- `app/Models/User.php` (line ~45)
- `app/Http/Middleware/CheckDomainRestriction.php`
- `app/Http/Controllers/Auth/GoogleController.php` (line ~30)

### 3. Web Scraping (app/Console/Commands/ScrapeVillaCollege.php)

- Scrapes actual Villa College website pages
- Intelligent content extraction and cleaning
- Handles navigation, links, and page discovery
- Stores structured data in knowledge_base table

## Useful Commands

```bash
# View application logs
docker-compose logs -f app

# Access container shell
docker-compose exec app bash

# Run any artisan command
docker-compose exec app php artisan <command>

# View database
docker-compose exec db mysql -u laravel_user -plaravel_password laravel_ai

# Stop application
docker-compose down

# Restart with fresh database
docker-compose down -v
docker-compose up -d
```

## Troubleshooting

### Application won't start
```bash
docker-compose down -v
docker-compose up -d --build
```

### Database errors
Check that Docker Desktop is running and has enough resources (4GB+ RAM recommended).

### OpenAI API errors
- Verify API key is valid
- Check that you have available credits
- Ensure you have access to gpt-4o-mini and text-embedding-ada-002 models

### Domain restriction not working
The domain validation is server-side and cannot be bypassed. Test with actual @villacollege.edu.mv email.

## Evaluation Points

When evaluating, please check:

1. **✅ Setup**: Single command (`docker-compose up -d`) starts everything
2. **✅ Seeding**: Command `php artisan vc:seed-knowledge` works
3. **✅ Access**: Application accessible at http://localhost:8080
4. **✅ Security**: Domain restriction properly enforced
5. **✅ RAG**: AI provides relevant, grounded responses with sources
6. **✅ Documentation**: README is clear and complete
7. **✅ Self-Assessment**: Thorough explanation of architectural decisions

## Expected Behavior

- **Login**: Only @villacollege.edu.mv and @students.villacollege.edu.mv emails allowed
- **Chat**: Real-time responses with ~2-5 second latency
- **Accuracy**: Responses should be relevant to Villa College with source citations
- **UI**: Clean, modern interface that's mobile-responsive

## Contact

If you encounter any issues during setup, common problems are addressed in:
- `README.md` (Troubleshooting section)
- `GITHUB-SUBMISSION.md` (Setup issues)
- `QUICK-COMMANDS.md` (Common commands)

## Notes

- The application uses Docker for consistent deployment across environments
- All sensitive data (API keys) are in `.env` which is not committed to Git
- The `.env.example` file contains placeholders showing what configuration is needed
- Knowledge base seeding may take several minutes on first run

---

Thank you for reviewing this project!

**Student**: [Your Name]  
**Course**: [Course Name]  
**Date**: December 2025
