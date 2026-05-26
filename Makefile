.PHONY: up down build setup migrate shell logs

# Start all containers
up:
	docker compose up -d

# Stop all containers
down:
	docker compose down

# Build images
build:
	docker compose build --no-cache

# First-time setup: copy env, install deps, generate key, migrate
setup:
	cp .env.docker .env
	docker compose up -d
	docker compose exec app composer install --no-interaction
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate --force
	docker compose exec app php artisan storage:link
	docker compose exec app php artisan filament:assets
	@echo ""
	@echo "Setup complete! Visit http://localhost:8080/superadmin"

# Run central DB migrations only
migrate:
	docker compose exec app php artisan migrate

# Open shell in app container
shell:
	docker compose exec app bash

# View logs
logs:
	docker compose logs -f app nginx

# Run npm dev (Vite) inside node container
npm-dev:
	docker compose --profile dev up node

# Build assets for production
npm-build:
	docker compose exec app npm ci && docker compose exec app npm run build

# Create superadmin user
create-admin:
	docker compose exec app php artisan make:filament-user
