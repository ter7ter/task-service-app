#!/bin/sh

# Exit immediately if a command exits with a non-zero status.
set -e

# Change to the application's directory
cd /var/www/html

# 1. Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
fi

# 2. Install Composer Dependencies
echo "Installing Composer dependencies..."
composer install --no-interaction --no-progress --no-ansi

# 3. Generate App Key if it's missing
# The grep command checks if the APP_KEY is empty or just has the placeholder
if ! grep -q "APP_KEY=base64:.*" .env; then
    echo "Generating application key..."
    php artisan key:generate --ansi
fi

# 4. Create databases if they don't exist
echo "Checking/Creating databases..."
php -r "
try {
    \$pdo = new PDO('mysql:host=' . getenv('DB_HOST'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    \$pdo->exec('CREATE DATABASE IF NOT EXISTS \`' . getenv('DB_DATABASE') . '\`');
    \$pdo->exec('CREATE DATABASE IF NOT EXISTS \`laravel_test\`');
    echo \"Databases '\" . getenv('DB_DATABASE') . \"' and 'laravel_test' checked/created successfully.\n\";
} catch (PDOException \$e) {
    die('Could not connect or create database: ' . \$e->getMessage());
}
"

# 5. Run migrations
echo "Running migrations..."
php artisan migrate --force

# 6. Check if seeding is needed and run seeds
USER_COUNT=$(php artisan tinker --execute="echo \\App\\Models\\User::count();")
echo "Found $USER_COUNT users in the database."

if [ "$USER_COUNT" -eq 0 ]; then
    echo "Database is empty, running seeds..."
    php artisan db:seed --force
else
    echo "Database not empty, skipping seeds."
fi

# Fix storage permissions
echo "Fixing storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Hand over control to the main container process (e.g., php-fpm).
echo "Handing over to main process..."
exec "$@"
