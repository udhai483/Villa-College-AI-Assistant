# 🚀 QUICK START - Villa College AI Assistant

## One-Command Setup

### Windows (PowerShell or CMD)
```cmd
setup.bat
```

### Linux/Mac (Terminal)
```bash
chmod +x setup.sh && ./setup.sh
```

---

## Manual Setup (If Scripts Don't Work)

### 1️⃣ Copy Environment File
```bash
copy .env.example .env
```

### 2️⃣ Get Google OAuth Credentials
1. Visit: https://console.cloud.google.com/
2. Create project
3. Enable Google+ API
4. Create OAuth 2.0 Client ID
5. Add redirect: `http://localhost:8080/auth/google/callback`
6. Copy Client ID and Secret

### 3️⃣ Get OpenAI API Key
1. Visit: https://platform.openai.com/
2. Create API key
3. Copy the key

### 4️⃣ Edit .env File
```env
GOOGLE_CLIENT_ID=paste-your-client-id-here
GOOGLE_CLIENT_SECRET=paste-your-secret-here
OPENAI_API_KEY=paste-your-api-key-here
```

### 5️⃣ Start Docker
```bash
docker-compose up -d --build
```

### 6️⃣ Install Dependencies
```bash
docker-compose exec app composer install
docker-compose exec app npm install
```

### 7️⃣ Setup Laravel
```bash
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app npm run build
```

### 8️⃣ Open Browser
```
http://localhost:8080
```

---

## ✅ Verification

Run this to check if everything is working:
```bash
docker-compose ps
```

Should show 3 containers running:
- ✅ laravel_app
- ✅ laravel_nginx
- ✅ laravel_db

---

## 🎯 Usage

1. **Login**: Click "Sign in with Google"
2. **Use Villa Email**: Must end with @villacollege.edu.mv or @students.villacollege.edu.mv
3. **Chat**: Type your question and press Enter
4. **Logout**: Click logout icon in header

---

## 📱 Screenshots Reference

### Login Screen
```
┌─────────────────────────────┐
│    [🎨 Blue Logo Icon]      │
│ Villa College AI Assistant  │
│                             │
│  ┌───────────────────────┐  │
│  │   Welcome Back!       │  │
│  │  [G] Sign with Google │  │
│  │   ℹ️ Domain Notice     │  │
│  └───────────────────────┘  │
└─────────────────────────────┘
```

### Chat Screen
```
┌─────────────────────────────────────┐
│ [Logo] Villa AI    [User] [Logout] │
├─────────────────────────────────────┤
│                                     │
│ [AI] Hello, how can I help?         │
│                                     │
│           [User] What courses? 👤   │
│                                     │
│ [AI] We offer various courses...    │
│                                     │
├─────────────────────────────────────┤
│ [Ask anything...        ] [Send]    │
└─────────────────────────────────────┘
```

---

## 🛠️ Common Commands

### Start
```bash
docker-compose up -d
```

### Stop
```bash
docker-compose down
```

### View Logs
```bash
docker-compose logs -f
```

### Clear Cache
```bash
docker-compose exec app php artisan cache:clear
```

### Run Scraper
```bash
docker-compose exec app php artisan scrape:villacollege
```

---

## 🐛 Troubleshooting

### Port 8080 in use?
Change in docker-compose.yml:
```yaml
nginx:
  ports:
    - "8081:80"
```

### Database issues?
Wait 10 seconds for MySQL to start

### Permission errors?
```bash
docker-compose exec app chown -R www-data:www-data /var/www
```

---

## 📚 Full Documentation

- **Setup Guide**: HOW-TO-RUN.md
- **Quick Reference**: QUICKSTART.md
- **UI Design**: UI-DESIGN.md
- **Project Structure**: PROJECT-STRUCTURE.md
- **Summary**: PROJECT-SUMMARY.md

---

## ✨ Features

✅ Google OAuth Login
✅ Domain Restriction
✅ Modern Chat UI
✅ Conversation History
✅ Mobile Responsive
✅ Docker Containerized

---

**Need Help?** Check HOW-TO-RUN.md for detailed instructions!
