#!/bin/bash

# Cache configurations and routes for performance
echo "Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "Running migrations..."
php artisan migrate --force
