# 🚀 Complete Setup & Run Instructions

## Villa College AI Assistant - Laravel Livewire Application

---

## 📋 Prerequisites

Before starting, ensure you have:

✅ **Docker Desktop** (Windows/Mac) or **Docker Engine** (Linux)
  - Download: https://www.docker.com/products/docker-desktop
  - Version: 20.10 or higher

✅ **Docker Compose** (usually included with Docker Desktop)
  - Version: 2.0 or higher

✅ **Git** (optional, for version control)

✅ **Text Editor** (VS Code, Sublime, etc.)

---

## 🎯 Quick Start (5 Minutes)

### Option 1: Automated Setup Script

**For Windows:**
```powershell
# Open PowerShell in the Assignment folder
.\setup.bat
```

**For Linux/Mac:**
```bash
# Open Terminal in the Assignment folder
chmod +x setup.sh
./setup.sh
```

**Then:**
1. Edit `.env` file with your Google OAuth and OpenAI credentials
2. Run: `docker-compose restart`
3. Open browser: http://localhost:8080

---

## 📝 Manual Setup (Step-by-Step)

### Step 1: Prepare Environment File

```bash
# Windows Command Prompt
copy .env.example .env

# Linux/Mac Terminal
cp .env.example .env
```

### Step 2: Configure API Credentials

Open `.env` in a text editor and update:

#### A. Google OAuth Setup

1. Go to **Google Cloud Console**: https://console.cloud.google.com/
2. Create a new project or select existing
3. Navigate to **APIs & Services > Credentials**
4. Click **Create Credentials > OAuth 2.0 Client ID**
5. Configure:
   - Application type: **Web application**
   - Name: **Villa College AI Assistant**
   - Authorized redirect URIs: 
     ```
     http://localhost:8080/auth/google/callback
     ```
6. Copy **Client ID** and **Client Secret**
7. Update `.env`:
   ```env
   GOOGLE_CLIENT_ID=your-actual-client-id-here.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=your-actual-client-secret-here
   GOOGLE_REDIRECT_URI=http://localhost:8080/auth/google/callback
   ```

#### B. OpenAI API Setup

1. Go to **OpenAI Platform**: https://platform.openai.com/
2. Navigate to **API Keys**
3. Click **Create new secret key**
4. Copy the key
5. Update `.env`:
   ```env
   OPENAI_API_KEY=sk-your-actual-openai-api-key-here
   ```

### Step 3: Build Docker Containers

```bash
docker-compose up -d --build
```

**What this does:**
- Builds PHP 8.2 container with Laravel
- Starts Nginx web server on port 8080
- Starts MySQL 8.0 database on port 3306
- Creates network for containers to communicate

**Wait for:** About 2-3 minutes for all containers to build and start

### Step 4: Verify Containers Running

```bash
docker-compose ps
```

**Expected output:**
```
NAME              STATUS          PORTS
laravel_app       Up              9000/tcp
laravel_nginx     Up              0.0.0.0:8080->80/tcp
laravel_db        Up              0.0.0.0:3306->3306/tcp
```

### Step 5: Install PHP Dependencies

```bash
docker-compose exec app composer install --no-interaction --optimize-autoloader
```

**What this does:**
- Installs Laravel framework
- Installs Livewire
- Installs Laravel Socialite
- Installs OpenAI PHP client
- Installs all other dependencies

**Wait for:** About 1-2 minutes

### Step 6: Install Node.js Dependencies

```bash
docker-compose exec app npm install
```

**What this does:**
- Installs Tailwind CSS
- Installs Vite build tool
- Installs frontend dependencies

**Wait for:** About 1 minute

### Step 7: Generate Application Key

```bash
docker-compose exec app php artisan key:generate
```

**What this does:**
- Creates encryption key for Laravel
- Updates `.env` with APP_KEY

### Step 8: Run Database Migrations

```bash
docker-compose exec app php artisan migrate
```

**What this does:**
- Creates `users` table
- Creates `conversations` table
- Creates `knowledge_base` table
- Creates `sessions` table
- Creates other Laravel tables

**Expected output:**
```
Migration table created successfully.
Migrating: 0001_01_01_000000_create_users_table
Migrated:  0001_01_01_000000_create_users_table
...
```

### Step 9: Build Frontend Assets

```bash
docker-compose exec app npm run build
```

**What this does:**
- Compiles Tailwind CSS
- Bundles JavaScript
- Optimizes assets for production

**Wait for:** About 30 seconds

### Step 10: Set Permissions (Linux/Mac only)

```bash
docker-compose exec app chown -R www-data:www-data /var/www/storage
docker-compose exec app chown -R www-data:www-data /var/www/bootstrap/cache
```

---

## ✅ Verification

### 1. Check All Services

```bash
docker-compose ps
```

All should show **"Up"** status.

### 2. Check Application Health

Open browser: http://localhost:8080

You should see the **Login Screen** with:
- Villa College AI Assistant header
- Google sign-in button
- Domain restriction notice

### 3. Check Logs (if issues)

```bash
# All logs
docker-compose logs -f

# Just app logs
docker-compose logs -f app

# Just nginx logs
docker-compose logs -f nginx
```

---

## 🎮 Using the Application

### 1. Access Login Page

Navigate to: **http://localhost:8080**

### 2. Sign In with Google

1. Click **"Sign in with Google"** button
2. Choose a Google account with email ending in:
   - `@villacollege.edu.mv` OR
   - `@students.villacollege.edu.mv`
3. Grant permissions

**Note:** Other email domains will be rejected with an error message.

### 3. Start Chatting

After successful login:
- You'll be redirected to the chat interface
- Type your question in the input box
- Press **Enter** or click **Send**
- AI will respond (currently placeholder, needs RAG implementation)

### 4. View Conversation History

- All conversations are automatically saved
- History loads when you revisit the page
- Each message shows timestamp

### 5. Logout

Click the logout icon (arrow) in the top-right corner.

---

## 🔧 Development Commands

### Running in Development Mode

For hot module replacement (auto-refresh on code changes):

```bash
docker-compose exec app npm run dev
```

Keep this running in a terminal while developing.

### Accessing Container Shell

```bash
docker-compose exec app bash
```

Now you're inside the container and can run any command.

### Running Artisan Commands

```bash
# Examples
docker-compose exec app php artisan route:list
docker-compose exec app php artisan migrate:status
docker-compose exec app php artisan tinker
```

### Clearing Caches

```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Populating Knowledge Base

```bash
docker-compose exec app php artisan scrape:villacollege
```

This will scrape Villa College website and populate the database.

### Database Access

```bash
docker-compose exec db mysql -u laravel_user -plaravel_password laravel_ai
```

Or use any MySQL client:
- Host: `localhost`
- Port: `3306`
- Database: `laravel_ai`
- Username: `laravel_user`
- Password: `laravel_password`

---

## 🛑 Stopping the Application

### Stop Containers (Preserves Data)

```bash
docker-compose down
```

### Stop and Remove Volumes (Fresh Start)

```bash
docker-compose down -v
```

**Warning:** This deletes all database data!

### Restart Containers

```bash
docker-compose restart
```

---

## 🐛 Troubleshooting

### Problem: "Port 8080 already in use"

**Solution:**

Edit `docker-compose.yml`:
```yaml
nginx:
  ports:
    - "8081:80"  # Change to any available port
```

Then use: http://localhost:8081

### Problem: "Connection refused" to database

**Solution:**

Wait 10 seconds for MySQL to fully start:
```bash
docker-compose logs -f db
```

Look for: `mysqld: ready for connections`

### Problem: "No such file or directory: vendor/autoload.php"

**Solution:**

```bash
docker-compose exec app composer install
```

### Problem: "Permission denied" errors

**Solution (Linux/Mac):**

```bash
docker-compose exec app chown -R www-data:www-data /var/www
```

**Solution (Windows):**

Usually not needed on Windows.

### Problem: "Vite manifest not found"

**Solution:**

```bash
docker-compose exec app npm run build
```

### Problem: Google OAuth "redirect_uri_mismatch"

**Solution:**

1. Check Google Cloud Console
2. Ensure redirect URI is exactly: `http://localhost:8080/auth/google/callback`
3. No trailing slash
4. Correct port number

### Problem: "Class not found" errors

**Solution:**

```bash
docker-compose exec app composer dump-autoload
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
```

### Problem: Fresh install needed

**Solution:**

```bash
# Complete reset
docker-compose down -v
docker-compose up -d --build

# Reinstall
docker-compose exec app composer install
docker-compose exec app npm install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app npm run build
```

---

## 📊 Project Status

### ✅ Completed Features

- ✅ Docker configuration
- ✅ Laravel 11 setup
- ✅ Livewire 3 integration
- ✅ Tailwind CSS 3 styling
- ✅ Google OAuth authentication
- ✅ Domain restriction (@villacollege.edu.mv, @students.villacollege.edu.mv)
- ✅ Modern login UI
- ✅ Professional chat UI
- ✅ Conversation persistence
- ✅ User session management
- ✅ Database migrations
- ✅ Responsive design

### 🚧 To Be Implemented

- ⚠️ **RAG Implementation** (Retrieval-Augmented Generation)
  - Vector embeddings generation
  - Semantic similarity search
  - OpenAI API integration for responses
  
- ⚠️ **Web Scraping Enhancement**
  - Complete Villa College website scraping
  - Content chunking optimization
  - Embedding generation

- ⚠️ **Laravel Octane** (Optional - P1)
  - Performance optimization
  - OpenSwoole integration

- ⚠️ **PWA Features** (Optional - P2)
  - Service worker
  - Offline support
  - App manifest

---

## 📁 Important Files

| File | Purpose |
|------|---------|
| `.env` | Environment configuration |
| `docker-compose.yml` | Docker orchestration |
| `routes/web.php` | Application routes |
| `app/Livewire/Auth/Login.php` | Login component |
| `app/Livewire/Chat/ChatInterface.php` | Chat component |
| `app/Http/Controllers/Auth/GoogleController.php` | OAuth logic |
| `app/Models/User.php` | User model |
| `app/Models/Conversation.php` | Chat history model |
| `resources/views/livewire/auth/login.blade.php` | Login UI |
| `resources/views/livewire/chat/chat-interface.blade.php` | Chat UI |
| `tailwind.config.js` | Tailwind configuration |

---

## 📞 Support & Resources

- **Laravel Docs**: https://laravel.com/docs/11.x
- **Livewire Docs**: https://livewire.laravel.com/docs
- **Tailwind Docs**: https://tailwindcss.com/docs
- **Docker Docs**: https://docs.docker.com/
- **OpenAI API**: https://platform.openai.com/docs

---

## 🎓 Testing Credentials

For testing, use Google accounts with these domains:
- `@villacollege.edu.mv`
- `@students.villacollege.edu.mv`

Other domains will be **rejected** by the system.

---

## 📈 Next Steps

1. ✅ **Verify Installation** - Ensure everything runs
2. 🔑 **Add API Keys** - Configure Google OAuth and OpenAI
3. 🧪 **Test Login** - Try authentication
4. 💬 **Test Chat** - Send some messages
5. 🤖 **Implement RAG** - Add AI intelligence
6. 🚀 **Deploy** - Move to production

---

**Congratulations!** Your Villa College AI Assistant is ready to run. 🎉

For questions or issues, refer to the troubleshooting section or check the logs.
