#!/bin/bash
# ============================================================
# ProcureThai — VPS Deployment Script
# Run this on the VPS: bash deploy-vps.sh
# ============================================================
set -e

APP_DIR="/var/www/procure-thai"
PHP="php8.3"
REPO="https://github.com/pisitkms9213140/procure-thai.git"

echo "======================================================"
echo " ProcureThai VPS Deploy"
echo "======================================================"

# ── 1. Clone or pull ─────────────────────────────────────
if [ -d "$APP_DIR/.git" ]; then
    echo "[1] Pulling latest code..."
    cd "$APP_DIR"
    git pull origin main
else
    echo "[1] Cloning repo..."
    git clone "$REPO" "$APP_DIR"
    cd "$APP_DIR"
fi

# ── 2. Install PHP dependencies ──────────────────────────
echo "[2] Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# ── 3. Build frontend assets ─────────────────────────────
echo "[3] Building Vite assets..."
npm ci --silent
npm run build

# ── 4. Set up .env if not exists ─────────────────────────
if [ ! -f "$APP_DIR/.env" ]; then
    echo "[4] Creating .env from example..."
    cp "$APP_DIR/.env.example" "$APP_DIR/.env"
    echo ""
    echo "  *** IMPORTANT: Edit /var/www/procure-thai/.env before continuing ***"
    echo "  Set: APP_URL, CENTRAL_DOMAIN, DB_* credentials, APP_KEY"
    echo ""
    read -p "  Press ENTER after editing .env to continue..."
fi

# ── 5. Generate app key if missing ───────────────────────
if ! grep -q "APP_KEY=base64" "$APP_DIR/.env"; then
    echo "[5] Generating app key..."
    $PHP artisan key:generate
fi

# ── 6. Run migrations ────────────────────────────────────
echo "[6] Running central migrations..."
$PHP artisan migrate --force

# ── 7. Storage symlink ───────────────────────────────────
echo "[7] Creating storage symlink..."
$PHP artisan storage:link --force

# ── 8. Fix permissions ───────────────────────────────────
echo "[8] Setting permissions..."
chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# ── 9. Clear & cache config ──────────────────────────────
echo "[9] Optimizing..."
$PHP artisan optimize:clear
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache

echo ""
echo "======================================================"
echo " Deploy complete!"
echo " Next: configure Nginx (see nginx-procurethai.conf)"
echo "======================================================"
