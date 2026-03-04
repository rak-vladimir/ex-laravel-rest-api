.PHONY: help up down down-volumes restart build rebuild logs ps shell bash artisan composer npm migrate seed test fresh install composer-install clean

# ────────────────────────────────────────────────
# Main settings
# ────────────────────────────────────────────────
COMPOSE := docker compose -f _docker/docker-compose.yml \
		--env-file .env \
		--env-file _docker/.env

SERVICE_APP := app

UID ?= $(shell id -u)
GID ?= $(shell id -g)

export UID
export GID

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Start containers (make up ARGS="--force-recreate")
	$(COMPOSE) up -d $(ARGS)

down: ## Stop and remove containers (volumes are NOT removed!)
	$(COMPOSE) down

down-volumes: ## Stop and remove ALL volumes (careful!)
	$(COMPOSE) down -v

restart: down up ## Restart everything

build: ## Rebuild images (without cache — make build ARGS="--no-cache")
	$(COMPOSE) build $(ARGS)

rebuild: build down up ## Full rebuild + restart

logs: ## Show logs (make logs ARGS="-f --tail=100")
	$(COMPOSE) logs $(ARGS)

ps: ## Show list of containers
	$(COMPOSE) ps

shell: ## Open shell in the application container (sh)
	$(COMPOSE) exec $(SERVICE_APP) sh

bash: ## Open shell in the application container (bash if available)
	$(COMPOSE) exec $(SERVICE_APP) bash -l

artisan: ## Run any artisan command: make artisan migrate
	$(COMPOSE) exec $(SERVICE_APP) php artisan $(filter-out $@,$(MAKECMDGOALS))
	@$(eval MAKECMDGOALS := $(filter-out $@,$(MAKECMDGOALS)))

composer: ## Run composer: make composer install
	$(COMPOSE) exec $(SERVICE_APP) composer $(filter-out $@,$(MAKECMDGOALS))
	@$(eval MAKECMDGOALS := $(filter-out $@,$(MAKECMDGOALS)))

npm: ## Run npm: make npm install
	$(COMPOSE) exec $(SERVICE_APP) npm $(filter-out $@,$(MAKECMDGOALS))
	@$(eval MAKECMDGOALS := $(filter-out $@,$(MAKECMDGOALS)))

migrate: ## Run migrations
	$(COMPOSE) exec $(SERVICE_APP) php artisan migrate --force

seed: ## Run seeders
	$(COMPOSE) exec $(SERVICE_APP) php artisan db:seed --force

test: ## Run tests
	$(COMPOSE) exec $(SERVICE_APP) ./vendor/bin/phpunit $(ARGS)

fresh: down-volumes build up migrate seed ## Full reset + migrations + seeders (careful!)

install: build up composer-install migrate ## Convenient target for first run
	@echo "Installation completed. Access: http://localhost"

composer-install: ## Run composer install
	$(COMPOSE) exec $(SERVICE_APP) composer install --prefer-dist --no-interaction

clean: ## Complete environment cleanup
	$(COMPOSE) down -v --remove-orphans

%:
	@:
