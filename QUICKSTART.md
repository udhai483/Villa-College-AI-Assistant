# Quick Start Guide - Villa College AI Assistant

## 🚀 How to Run the Project

### Option 1: Automated Setup (Recommended)

**Windows:**
```cmd
setup.bat
```

**Linux/Mac:**
```bash
chmod +x setup.sh
./setup.sh
```

### Option 2: Manual Setup

**Step 1: Copy Environment File**
```bash
copy .env.example .env     # Windows
cp .env.example .env       # Linux/Mac
```

**Step 2: Configure API Keys**

Edit `.env` file and add:

```env
# Google OAuth (Get from https://console.cloud.google.com/)
GOOGLE_CLIENT_ID=your-client-id-here
GOOGLE_CLIENT_SECRET=your-client-secret-here
GOOGLE_REDIRECT_URI=http://localhost:8080/auth/google/callback

# OpenAI API (Get from https://platform.openai.com/)
OPENAI_API_KEY=sk-your-api-key-here
```

**Step 3: Build and Start Containers**
```bash
docker-compose up -d --build
```

**Step 4: Install Dependencies**
```bash
docker-compose exec app composer install
docker-compose exec app npm install
```

**Step 5: Generate App Key**
```bash
docker-compose exec app php artisan key:generate
```

**Step 6: Run Migrations**
```bash
docker-compose exec app php artisan migrate
```

**Step 7: Build Assets**
```bash
docker-compose exec app npm run build
```

**Step 8: Open Browser**
```
http://localhost:8080
```

## 📝 Common Commands

### Start/Stop Application
```bash
# Start
docker-compose up -d

# Stop
docker-compose down

# Restart
docker-compose restart
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

### Access Container
```bash
docker-compose exec app bash
```

### Clear Caches
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear
```

### Run Scraper (Populate Knowledge Base)
```bash
docker-compose exec app php artisan scrape:villacollege
```

### Development Mode (Hot Reload)
```bash
docker-compose exec app npm run dev
```

## 🔧 Troubleshooting

### "Port 8080 already in use"
Change port in `docker-compose.yml`:
```yaml
nginx:
  ports:
    - "8081:80"  # Change 8080 to 8081
```

### "Database connection refused"
Wait 10 seconds for MySQL to fully start, then retry

### "Permission denied" errors
```bash
docker-compose exec app chown -R www-data:www-data /var/www/storage
docker-compose exec app chown -R www-data:www-data /var/www/bootstrap/cache
```

### Fresh Install
```bash
docker-compose down -v
docker-compose up -d --build
# Then repeat steps 4-7
```

## ✅ Verification Checklist

- [ ] Docker containers running: `docker-compose ps`
- [ ] Google OAuth configured in `.env`
- [ ] OpenAI API key configured in `.env`
- [ ] Database migrations completed
- [ ] Frontend assets built
- [ ] Application accessible at http://localhost:8080
- [ ] Can see login page with Google sign-in button

## 🎯 Next Steps

1. **Test Login**: Try logging in with @villacollege.edu.mv or @students.villacollege.edu.mv email
2. **Populate Knowledge Base**: Run `docker-compose exec app php artisan scrape:villacollege`
3. **Test Chat**: Send messages and receive AI responses
4. **Customize**: Modify UI colors, add features, implement full RAG

## 📱 Accessing on Mobile

On your local network:
1. Find your computer's IP address
2. Update `APP_URL` in `.env` to `http://YOUR_IP:8080`
3. Access from mobile: `http://YOUR_IP:8080`

## 🛡️ Security Notes

- Only @villacollege.edu.mv and @students.villacollege.edu.mv emails can login
- Session-based authentication
- CSRF protection enabled
- Domain restriction enforced server-side

## 📚 File Structure Reference

```
├── app/
│   ├── Livewire/Auth/Login.php          # Login component
│   ├── Livewire/Chat/ChatInterface.php  # Chat component
│   ├── Models/User.php                  # User model
│   └── Http/Controllers/Auth/           # OAuth controller
├── resources/views/livewire/
│   ├── auth/login.blade.php             # Login UI
│   └── chat/chat-interface.blade.php    # Chat UI
├── routes/web.php                       # Application routes
├── docker-compose.yml                   # Docker configuration
└── .env                                 # Environment config
```
