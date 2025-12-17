# Production Deployment Guide

## 🚀 Quick Deployment

### **Prerequisites**
- Ubuntu/Debian server with Docker & Docker Compose installed
- Domain name pointing to your server (e.g., chat.villacollege.edu.mv)
- SSL certificate (Let's Encrypt recommended)
- Google OAuth credentials configured
- OpenAI API key with credits ($10+ recommended)

---

## 📋 Deployment Steps

### **1. Server Setup**

```bash
# Install Docker & Docker Compose
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo apt install docker-compose-plugin -y

# Verify installation
docker --version
docker compose version
```

### **2. Clone Repository**

```bash
# Create application directory
sudo mkdir -p /var/www/villa-ai
cd /var/www/villa-ai

# Clone from GitHub
git clone https://github.com/udhai483/Villa-College-AI-Assistant.git .

# Set permissions
sudo chown -R $USER:$USER /var/www/villa-ai
chmod -R 755 storage bootstrap/cache
```

### **3. Environment Configuration**

```bash
# Copy production template
cp .env.production .env

# Edit configuration
nano .env
```

**Required changes in `.env`**:
```bash
APP_URL=https://chat.villacollege.edu.mv
APP_KEY=                              # Generate below
DB_PASSWORD=                          # Strong password (20+ chars)
GOOGLE_CLIENT_ID=                     # From Google Console
GOOGLE_CLIENT_SECRET=                 # From Google Console
OPENAI_API_KEY=                       # From OpenAI platform
```

**Generate app key**:
```bash
docker compose run --rm app php artisan key:generate
```

### **4. Google OAuth Setup**

1. Go to: https://console.cloud.google.com/apis/credentials
2. Create OAuth 2.0 credentials
3. **Authorized redirect URIs**:
   - `https://chat.villacollege.edu.mv/auth/google/callback`
4. **Restrict to domain** (optional but recommended):
   - villacollege.edu.mv
5. Copy Client ID & Secret to `.env`

### **5. SSL Certificate (Let's Encrypt)**

```bash
# Install Certbot
sudo apt install certbot -y

# Generate certificate
sudo certbot certonly --standalone -d chat.villacollege.edu.mv

# Certificates will be at:
# /etc/letsencrypt/live/chat.villacollege.edu.mv/fullchain.pem
# /etc/letsencrypt/live/chat.villacollege.edu.mv/privkey.pem
```

### **6. Configure Nginx (Production)**

Edit `docker/nginx/default.conf`:

```nginx
server {
    listen 80;
    server_name chat.villacollege.edu.mv;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name chat.villacollege.edu.mv;
    root /var/www/html/public;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/chat.villacollege.edu.mv/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/chat.villacollege.edu.mv/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    
    # Rate Limiting
    limit_req_zone $binary_remote_addr zone=chat:10m rate=30r/m;
    limit_req zone=chat burst=10 nodelay;
    
    index index.php;
    charset utf-8;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Update `docker-compose.yml` to mount SSL certificates:

```yaml
services:
  nginx:
    volumes:
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
      - /etc/letsencrypt:/etc/letsencrypt:ro
```

### **7. Build & Deploy**

```bash
# Build and start containers
docker compose up -d --build

# Run migrations
docker compose exec app php artisan migrate --force

# Optimize for production
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Verify containers are running
docker compose ps
```

### **8. Populate Knowledge Base**

```bash
# Scrape Villa College website
docker compose exec app php artisan scrape:villacollege

# Add manual knowledge entries
docker compose exec app php artisan knowledge:add-manual

# Import PDFs (if available)
docker compose exec app php artisan knowledge:import-pdf storage/app/pdfs/

# Generate embeddings (requires OpenAI credits)
docker compose exec app php artisan embeddings:generate
```

### **9. Verify Deployment**

```bash
# Check health endpoint
curl https://chat.villacollege.edu.mv/api/health

# Check logs
docker compose logs -f app
docker compose logs -f nginx

# Test login
# Visit: https://chat.villacollege.edu.mv
# Login with @villacollege.edu.mv email
```

---

## 🔒 Security Hardening

### **Firewall Configuration**

```bash
# Allow only necessary ports
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP (redirect)
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable

# Block direct database access
sudo ufw deny 3306/tcp
```

### **Database Security**

```bash
# Access MySQL container
docker compose exec mysql mysql -u root -p

# Create limited user (instead of root)
CREATE USER 'villa_ai'@'%' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON villa_college_ai.* TO 'villa_ai'@'%';
FLUSH PRIVILEGES;

# Update .env with new credentials
DB_USERNAME=villa_ai
DB_PASSWORD=YOUR_STRONG_PASSWORD
```

### **SSL Auto-Renewal**

```bash
# Test renewal
sudo certbot renew --dry-run

# Add cron job for auto-renewal
sudo crontab -e

# Add this line:
0 0 1 * * certbot renew --quiet && docker compose restart nginx
```

### **Regular Backups**

```bash
# Create backup script
sudo nano /usr/local/bin/backup-villa-ai.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR=/var/backups/villa-ai
mkdir -p $BACKUP_DIR

# Backup database
docker compose exec -T mysql mysqldump -u root -p$DB_PASSWORD villa_college_ai > $BACKUP_DIR/db_$DATE.sql

# Backup storage (PDFs, logs)
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz storage/

# Keep only last 30 days
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/backup-villa-ai.sh

# Schedule daily backups (3 AM)
sudo crontab -e
# Add: 0 3 * * * /usr/local/bin/backup-villa-ai.sh
```

---

## 📊 Monitoring

### **Health Checks**

```bash
# Add to crontab for monitoring
*/5 * * * * curl -f https://chat.villacollege.edu.mv/api/health || echo "Health check failed" | mail -s "Villa AI Down" admin@villacollege.edu.mv
```

### **Log Rotation**

```bash
# Configure log rotation
sudo nano /etc/logrotate.d/villa-ai
```

```
/var/www/villa-ai/storage/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        docker compose exec app php artisan cache:clear > /dev/null
    endscript
}
```

### **Performance Metrics**

```bash
# View daily metrics
docker compose exec app php artisan metrics:view --period=24

# Weekly metrics
docker compose exec app php artisan metrics:view --period=168
```

---

## 🔄 Updates & Maintenance

### **Deploy Updates**

```bash
cd /var/www/villa-ai

# Pull latest changes
git pull origin main

# Rebuild if needed
docker compose up -d --build

# Run migrations
docker compose exec app php artisan migrate --force

# Clear caches
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear

# Re-optimize
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Restart services
docker compose restart
```

### **Zero-Downtime Deployment** (Advanced)

```bash
# Use Docker health checks + load balancer
# Or Blue-Green deployment with 2 environments
# Configure Nginx upstream with multiple backends
```

---

## ✅ Production Checklist

**Before Going Live**:

- [x] `.env` configured with production values
- [x] `APP_DEBUG=false` (CRITICAL!)
- [x] Strong `DB_PASSWORD` (20+ characters)
- [x] SSL certificate installed & working
- [x] Google OAuth redirect URI configured
- [x] OpenAI API key added with credits
- [x] Domain restriction enabled (@villacollege.edu.mv)
- [x] Rate limiting active (20 msg/min)
- [x] Firewall configured (only 22, 80, 443)
- [x] Database backups scheduled
- [x] Log rotation configured
- [x] Health monitoring active
- [x] Knowledge base populated (scrape + manual + PDFs)
- [x] Embeddings generated (if using semantic search)
- [x] Test login with @villacollege.edu.mv email
- [x] Test chat functionality
- [x] Verify source URLs working
- [x] Check logs for errors
- [x] Monitor metrics for first 24 hours

**Performance Tuning** (Optional):
- [ ] Install Redis for cache/session
- [ ] Enable PHP OpCache
- [ ] Configure CDN for static assets
- [ ] Database query optimization
- [ ] Enable Gzip compression
- [ ] Browser caching headers

---

## 🆘 Troubleshooting

### **"Access Denied" on Login**
- Verify email ends with `@villacollege.edu.mv`
- Check Google OAuth authorized domains
- Check Laravel logs: `docker compose logs app`

### **Slow Response Times**
```bash
# Check metrics
docker compose exec app php artisan metrics:view --period=1

# Check database queries
docker compose exec mysql mysql -u root -p
SHOW PROCESSLIST;

# Enable query logging (temporarily)
SET GLOBAL general_log = 'ON';
```

### **SSL Certificate Errors**
```bash
# Verify certificate
openssl s_client -connect chat.villacollege.edu.mv:443

# Renew certificate
sudo certbot renew
docker compose restart nginx
```

### **Container Not Starting**
```bash
# Check logs
docker compose logs

# Rebuild
docker compose down
docker compose up -d --build --force-recreate
```

---

## 📞 Support

**Issues**: https://github.com/udhai483/Villa-College-AI-Assistant/issues

**Logs**: `storage/logs/laravel.log`

**Health Check**: `https://chat.villacollege.edu.mv/api/health`

**Metrics**: `docker compose exec app php artisan metrics:view`

---

**Your production deployment is ready! 🎉**

Access at: **https://chat.villacollege.edu.mv**
