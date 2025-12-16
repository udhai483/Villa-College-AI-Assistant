#!/bin/bash

echo "================================================"
echo "Villa College AI Assistant - Quick Start Script"
echo "================================================"
echo ""

echo "[1/7] Copying environment file..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo ".env file created. Please configure Google OAuth and OpenAI API keys!"
    echo ""
else
    echo ".env file already exists, skipping..."
    echo ""
fi

echo "[2/7] Building Docker containers..."
docker-compose up -d --build
echo ""

echo "Waiting for containers to be ready..."
sleep 10
echo ""

echo "[3/7] Installing Composer dependencies..."
docker-compose exec app composer install --no-interaction
echo ""

echo "[4/7] Installing NPM dependencies..."
docker-compose exec app npm install
echo ""

echo "[5/7] Generating application key..."
docker-compose exec app php artisan key:generate
echo ""

echo "[6/7] Running database migrations..."
docker-compose exec app php artisan migrate --force
echo ""

echo "[7/7] Building frontend assets..."
docker-compose exec app npm run build
echo ""

echo "================================================"
echo "Setup Complete!"
echo "================================================"
echo ""
echo "Application is running at: http://localhost:8080"
echo ""
echo "IMPORTANT: Before using the application:"
echo "1. Configure Google OAuth credentials in .env file"
echo "2. Configure OpenAI API key in .env file"
echo "3. Restart containers: docker-compose restart"
echo ""
echo "Useful commands:"
echo "- Stop: docker-compose down"
echo "- Logs: docker-compose logs -f"
echo "- Shell: docker-compose exec app bash"
echo "================================================"
