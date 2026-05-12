#!/usr/bin/env bash
set -e

# Create Laravel project in src/ if it doesn't exist
if [ ! -f "src/composer.json" ]; then
  echo "Creating Laravel project..."
  docker run --rm \
    -v "$(pwd)/src:/app" \
    composer:latest \
    create-project laravel/laravel . --prefer-dist
fi

# Configure Laravel .env to point at the Docker DB service
ENV_FILE="src/.env"
sed -i "s/DB_HOST=127.0.0.1/DB_HOST=db/" "$ENV_FILE"
sed -i "s/DB_DATABASE=laravel/DB_DATABASE=laravel/" "$ENV_FILE"
sed -i "s/DB_USERNAME=root/DB_USERNAME=laravel/" "$ENV_FILE"
sed -i "s/DB_PASSWORD=/DB_PASSWORD=secret/" "$ENV_FILE"

echo "Starting containers..."
docker compose up -d --build

echo ""
echo "Waiting for DB to be ready..."
sleep 10

echo "Running migrations..."
docker compose exec app php artisan migrate --force

echo ""
echo "Done! App running at http://localhost:8000"
