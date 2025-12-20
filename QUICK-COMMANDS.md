# Quick Submission Commands

This file contains the exact commands needed for your submission demonstration.

## Initial Setup (One-Time)

```powershell
# 1. Create .env file from example
Copy-Item .env.example .env

# 2. Edit .env and add your API keys
# - GOOGLE_CLIENT_ID
# - GOOGLE_CLIENT_SECRET  
# - OPENAI_API_KEY

# 3. Start the application (SINGLE COMMAND)
docker-compose up -d
```

## Seeding Knowledge Base

```powershell
# Run the seeding process (as specified in assignment)
docker-compose exec app php artisan vc:seed-knowledge
```

## Accessing the Application

**URL**: http://localhost:8080

**Port**: 8080

**Login Requirement**: @villacollege.edu.mv or @students.villacollege.edu.mv email

## For Demonstration

### Show the application running
```powershell
# Check containers are running
docker-compose ps

# Access the application
# Open browser to: http://localhost:8080
```

### Show the seeding process
```powershell
# Seed knowledge base (combines scraping + embeddings)
docker-compose exec app php artisan vc:seed-knowledge
```

### Show logs (if needed)
```powershell
# View application logs
docker-compose logs -f app
```

### Stop the application
```powershell
# Stop all containers
docker-compose down
```

## GitHub Repository Setup

```powershell
# Initialize Git (if needed)
git init

# Add all files
git add .

# Commit
git commit -m "Initial commit: Villa College AI Assistant"

# Add remote (replace YOUR_USERNAME)
git remote add origin https://github.com/YOUR_USERNAME/villa-college-ai-assistant.git

# Push to GitHub
git push -u origin main
```

## Add Collaborator on GitHub

1. Go to: https://github.com/YOUR_USERNAME/villa-college-ai-assistant
2. Click: **Settings**
3. Click: **Collaborators**
4. Click: **Add people**
5. Enter: **ahmed.ashham@villacollege.edu.mv**
6. Click: **Add ahmed.ashham@villacollege.edu.mv to this repository**

## Verification Commands

```powershell
# Verify Docker is running
docker --version
docker-compose --version

# Verify containers are up
docker-compose ps

# Verify database connection
docker-compose exec app php artisan migrate:status

# Verify knowledge base has data
docker-compose exec app php artisan tinker
# Then type: \App\Models\KnowledgeBase::count()
# Should return a number > 0

# Verify embeddings exist
# In tinker: \App\Models\KnowledgeBase::whereNotNull('embedding')->count()
```

## Common Issues & Solutions

### Issue: Port 8080 already in use
```powershell
# Find what's using port 8080
netstat -ano | findstr :8080

# Kill the process (replace PID)
taskkill /PID <PID> /F

# Or change port in docker-compose.yml
# Change "8080:80" to "8081:80"
```

### Issue: Docker containers won't start
```powershell
# Remove everything and restart
docker-compose down -v
docker-compose up -d --build
```

### Issue: Database connection fails
```powershell
# Check if database container is running
docker-compose ps

# Check database logs
docker-compose logs db

# Recreate containers
docker-compose down
docker-compose up -d
```

### Issue: .env not found
```powershell
# Create from example
Copy-Item .env.example .env

# Edit and add your API keys
notepad .env
```

## Testing Everything Works

```powershell
# 1. Clean start
docker-compose down -v

# 2. Start fresh
docker-compose up -d

# 3. Wait 30 seconds for containers to be ready

# 4. Seed knowledge base
docker-compose exec app php artisan vc:seed-knowledge

# 5. Open browser
start http://localhost:8080

# 6. Login with @villacollege.edu.mv email

# 7. Ask the AI: "What programs does Villa College offer?"

# 8. Verify you get a response with sources
```

---

**Everything working? You're ready to submit! ✅**
