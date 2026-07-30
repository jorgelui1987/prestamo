#!/bin/sh

echo "=========================================="
echo "Starting Laravel application..."
echo "=========================================="

# =============================================
# 1. Crear .env COMPLETO para tallerluitech.fun
# =============================================
echo "Creating .env..."

cat > /var/www/html/.env << 'ENVEOF'
APP_NAME="Sistema de Prestamos Pro"
APP_ENV=production
APP_KEY=base64:xnEF34C3x4IXvThZT77bxxGeLmtdsOmEYJmjPNYN3E8=
APP_DEBUG=false
APP_TIMEZONE=America/Lima
APP_URL=https://tallerluitech.fun
APP_LOCALE=es

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=prestamos-poyecto-2zr8c2
DB_PORT=3306
DB_DATABASE=chile
DB_USERNAME=chile
DB_PASSWORD=Castro161219@

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=.tallerluitech.fun

CACHE_STORE=file
QUEUE_CONNECTION=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@tallerluitech.fun
MAIL_FROM_NAME="Sistema de Prestamos Pro"
ENVEOF

echo ".env created"

# =============================================
# 2. Directorios de storage
# =============================================
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# =============================================
# 3. Storage link
# =============================================
echo "Creating storage link..."
rm -f /var/www/html/public/storage
php /var/www/html/artisan storage:link --force --no-interaction 2>&1 || true

# =============================================
# 4. Migraciones
# =============================================
echo "Running migrations..."
php /var/www/html/artisan migrate --force --no-interaction 2>&1
MIGRATE_EXIT=$?
if [ $MIGRATE_EXIT -eq 0 ]; then
    echo "Migrations completed"
else
    echo "WARNING: Migrations failed with code $MIGRATE_EXIT"
fi

# =============================================
# 4b. Fallback: crear tabla planes si no existe
# =============================================
echo "Checking if planes table exists..."
php /var/www/html/artisan tinker --no-interaction --execute="
\$exists = Illuminate\Support\Facades\Schema::hasTable('planes');
if (!\$exists) {
    echo \"Planes table not found. Creating directly...\n\";
    Illuminate\Support\Facades\Schema::create('planes', function (\Illuminate\Database\Schema\Blueprint \$table) {
        \$table->id();
        \$table->string('nombre');
        \$table->text('descripcion')->nullable();
        \$table->decimal('precio', 10, 2)->default(0.00);
        \$table->integer('limite_usuarios')->default(5);
        \$table->integer('limite_clientes')->default(50);
        \$table->integer('limite_prestamos')->default(100);
        \$table->timestamps();
    });
    echo \"Planes table created successfully via fallback!\n\";
} else {
    echo \"Planes table already exists.\n\";
}
" 2>&1 || echo "Fallback check failed (non-critical)"

# =============================================
# 5. Limpiar cachés
# =============================================
echo "Clearing caches..."
php /var/www/html/artisan config:clear --no-interaction 2>&1 || true
php /var/www/html/artisan route:clear --no-interaction 2>&1 || true
php /var/www/html/artisan view:clear --no-interaction 2>&1 || true

echo "=========================================="
echo "Starting server..."
echo "=========================================="

exec php /var/www/html/artisan serve --host=0.0.0.0 --port=80