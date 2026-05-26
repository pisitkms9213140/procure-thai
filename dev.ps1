param([string]$Command = "help")

$ErrorActionPreference = "Stop"

function Run-Setup {
    Write-Host "Setting up procure-thai..." -ForegroundColor Cyan
    Copy-Item .env.docker .env -Force
    docker compose up -d
    Write-Host "Waiting for DB to be ready..." -ForegroundColor Yellow
    Start-Sleep -Seconds 10
    docker compose exec app composer install --no-interaction
    docker compose exec app php artisan key:generate
    docker compose exec app php artisan migrate --force
    docker compose exec app php artisan storage:link
    docker compose exec app php artisan filament:assets
    Write-Host ""
    Write-Host "Setup complete!" -ForegroundColor Green
    Write-Host "Superadmin panel -> http://localhost:8080/superadmin" -ForegroundColor Green
}

switch ($Command) {
    "setup"       { Run-Setup }
    "up"          { docker compose up -d }
    "down"        { docker compose down }
    "build"       { docker compose build --no-cache }
    "migrate"     { docker compose exec app php artisan migrate }
    "shell"       { docker compose exec app bash }
    "logs"        { docker compose logs -f app nginx }
    "npm-build"   { docker compose exec app sh -c "npm ci && npm run build" }
    "create-admin"{ docker compose exec app php artisan make:filament-user }
    default {
        Write-Host "Usage: .\dev.ps1 <command>" -ForegroundColor Yellow
        Write-Host ""
        Write-Host "Commands:"
        Write-Host "  setup        First-time setup (copy env, install, migrate)"
        Write-Host "  up           Start containers"
        Write-Host "  down         Stop containers"
        Write-Host "  build        Rebuild Docker images"
        Write-Host "  migrate      Run DB migrations"
        Write-Host "  shell        Open shell in app container"
        Write-Host "  logs         Tail container logs"
        Write-Host "  npm-build    Build frontend assets"
        Write-Host "  create-admin Create Filament superadmin user"
    }
}
