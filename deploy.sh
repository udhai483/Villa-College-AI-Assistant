#!/bin/bash
#
# Villa College AI Assistant - Production Deployment Script
# Run this on your Ubuntu/Debian production server
#
# Usage: bash deploy.sh
#

set -e  # Exit on error

echo "🚀 Villa College AI Assistant - Production Deployment"
echo "======================================================"
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/villa-ai"
REPO_URL="https://github.com/udhai483/Villa-College-AI-Assistant.git"
DOMAIN="chat.villacollege.edu.mv"

# Check if running as root or with sudo
if [ "$EUID" -eq 0 ]; then 
    echo -e "${RED}⚠️  Do not run this script as root. Run as regular user with sudo access.${NC}"
    exit 1
fi

echo -e "${YELLOW}📋 Pre-deployment Checklist:${NC}"
echo "  1. Ubuntu/Debian server ready"
echo "  2. DNS record pointing to this server ($DOMAIN)"
echo "  3. Ports 22, 80, 443 accessible"
echo "  4. Google OAuth credentials ready"
echo "  5. OpenAI API key ready (optional)"
echo ""
read -p "Ready to proceed? (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
    echo "Deployment cancelled."
    exit 0
fi

# Step 1: Install Docker
echo ""
echo -e "${GREEN}[1/8] Installing Docker...${NC}"
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker $USER
    rm get-docker.sh
    echo "✅ Docker installed"
else
    echo "✅ Docker already installed"
fi

# Step 2: Install Docker Compose
echo ""
echo -e "${GREEN}[2/8] Installing Docker Compose...${NC}"
if ! docker compose version &> /dev/null; then
    sudo apt update
    sudo apt install docker-compose-plugin -y
    echo "✅ Docker Compose installed"
else
    echo "✅ Docker Compose already installed"
fi

# Step 3: Install Certbot
echo ""
echo -e "${GREEN}[3/8] Installing Certbot...${NC}"
if ! command -v certbot &> /dev/null; then
    sudo apt update
    sudo apt install certbot -y
    echo "✅ Certbot installed"
else
    echo "✅ Certbot already installed"
fi

# Step 4: Clone Repository
echo ""
echo -e "${GREEN}[4/8] Cloning repository...${NC}"
if [ -d "$APP_DIR" ]; then
    echo -e "${YELLOW}⚠️  Directory $APP_DIR already exists${NC}"
    read -p "Delete and re-clone? (yes/no): " reclone
    if [ "$reclone" = "yes" ]; then
        sudo rm -rf $APP_DIR
        sudo mkdir -p $APP_DIR
        sudo chown -R $USER:$USER $APP_DIR
        git clone $REPO_URL $APP_DIR
        echo "✅ Repository cloned"
    else
        cd $APP_DIR
        git pull origin main
        echo "✅ Repository updated"
    fi
else
    sudo mkdir -p $APP_DIR
    sudo chown -R $USER:$USER $APP_DIR
    git clone $REPO_URL $APP_DIR
    echo "✅ Repository cloned"
fi

cd $APP_DIR

# Step 5: Configure Environment
echo ""
echo -e "${GREEN}[5/8] Configuring environment...${NC}"
if [ ! -f .env ]; then
    cp .env.production .env
    echo "✅ .env created from template"
    echo ""
    echo -e "${YELLOW}⚠️  IMPORTANT: Edit .env now with your credentials${NC}"
    echo "Required values:"
    echo "  - APP_URL=https://$DOMAIN"
    echo "  - DB_PASSWORD=<strong-password>"
    echo "  - GOOGLE_CLIENT_ID=<your-client-id>"
    echo "  - GOOGLE_CLIENT_SECRET=<your-client-secret>"
    echo "  - OPENAI_API_KEY=<your-api-key> (optional)"
    echo ""
    read -p "Press Enter to edit .env now..."
    nano .env
else
    echo "✅ .env already exists"
    read -p "Edit .env? (yes/no): " edit_env
    if [ "$edit_env" = "yes" ]; then
        nano .env
    fi
fi

# Step 6: Generate SSL Certificate
echo ""
echo -e "${GREEN}[6/8] Generating SSL certificate...${NC}"
if [ ! -d "/etc/letsencrypt/live/$DOMAIN" ]; then
    echo "Generating certificate for $DOMAIN..."
    echo "Make sure DNS is pointing to this server!"
    read -p "Continue? (yes/no): " ssl_confirm
    if [ "$ssl_confirm" = "yes" ]; then
        sudo certbot certonly --standalone -d $DOMAIN --agree-tos --non-interactive --register-unsafely-without-email || {
            echo -e "${RED}❌ SSL certificate generation failed${NC}"
            echo "Common issues:"
            echo "  - DNS not pointing to this server"
            echo "  - Port 80 not accessible"
            echo "  - Domain name incorrect"
            exit 1
        }
        echo "✅ SSL certificate generated"
    else
        echo -e "${YELLOW}⚠️  Skipping SSL setup - app will run on HTTP only${NC}"
    fi
else
    echo "✅ SSL certificate already exists"
fi

# Step 7: Deploy Application
echo ""
echo -e "${GREEN}[7/8] Deploying application...${NC}"

# Generate app key
echo "Generating application key..."
docker compose run --rm app php artisan key:generate --force

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R $USER:$USER storage bootstrap/cache

# Start containers
echo "Starting Docker containers..."
docker compose up -d --build

# Wait for services
echo "Waiting for services to start..."
sleep 10

# Run migrations
echo "Running database migrations..."
docker compose exec -T app php artisan migrate --force

# Optimize for production
echo "Optimizing for production..."
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache

echo "✅ Application deployed"

# Step 8: Populate Knowledge Base
echo ""
echo -e "${GREEN}[8/8] Populating knowledge base...${NC}"
read -p "Scrape Villa College website now? (yes/no): " scrape
if [ "$scrape" = "yes" ]; then
    docker compose exec -T app php artisan scrape:villacollege
    docker compose exec -T app php artisan knowledge:add-manual
    echo "✅ Knowledge base populated"
else
    echo "⏭️  Skipped - you can run later with:"
    echo "   docker compose exec app php artisan scrape:villacollege"
    echo "   docker compose exec app php artisan knowledge:add-manual"
fi

# Firewall Setup
echo ""
echo -e "${GREEN}🔒 Configuring firewall...${NC}"
if command -v ufw &> /dev/null; then
    sudo ufw --force enable
    sudo ufw allow 22/tcp   # SSH
    sudo ufw allow 80/tcp   # HTTP
    sudo ufw allow 443/tcp  # HTTPS
    sudo ufw deny 3306/tcp  # Block MySQL
    sudo ufw status
    echo "✅ Firewall configured"
else
    echo "⚠️  UFW not installed - install manually: sudo apt install ufw"
fi

# Final Status
echo ""
echo "======================================================"
echo -e "${GREEN}✅ Deployment Complete!${NC}"
echo "======================================================"
echo ""
echo "📊 Application Status:"
docker compose ps
echo ""
echo "🌐 Access your application:"
if [ -d "/etc/letsencrypt/live/$DOMAIN" ]; then
    echo "   https://$DOMAIN"
else
    echo "   http://$DOMAIN:8080 (HTTP only - SSL not configured)"
fi
echo ""
echo "🔍 Health Check:"
echo "   curl http://localhost:8080/api/health"
echo ""
echo "📈 View Metrics:"
echo "   docker compose exec app php artisan metrics:view"
echo ""
echo "📋 View Logs:"
echo "   docker compose logs -f app"
echo ""
echo "⚠️  Next Steps:"
echo "   1. Test login with @villacollege.edu.mv email"
echo "   2. Ask test questions in chat"
echo "   3. Check health endpoint"
echo "   4. Monitor logs for errors"
if [ ! -f .env ] || ! grep -q "OPENAI_API_KEY=sk-" .env; then
    echo "   5. Add OpenAI API key for semantic search (optional)"
fi
echo ""
echo "📖 Documentation:"
echo "   - PRODUCTION-DEPLOYMENT.md"
echo "   - MONITORING-GUIDE.md"
echo "   - SECURITY-STATUS.md"
echo ""
echo -e "${GREEN}🎉 Your AI Assistant is ready!${NC}"
