# ==========================================================
# Emarsys PHP SDK Makefile
# ==========================================================

# --- Config ---
DOCKER_EXEC_CMD = docker-compose exec php

# Default target
.DEFAULT_GOAL := help

.PHONY: help
help: ## Display this help message
	@printf "\033[33mUsage:\033[0m\n  make [target] [arg=\"val\"...]\n\n\033[33mTargets:\033[0m\n"
	@grep -hE '^[a-zA-Z0-9_-]+:.*## .*$$' $(MAKEFILE_LIST) | sort | \
	  awk 'BEGIN {FS = ": ## "}; {printf "  \033[32m%-15s\033[0m %s\n", $$1, $$2}'

.PHONY: build
build: ## Build the Docker image
	docker-compose build

.PHONY: up
up: ## Start the Docker container
	docker-compose up -d

.PHONY: down
down: ## Stop and remove the Docker container
	docker-compose down

.PHONY: shell
shell: ## Open an interactive shell in the container
	$(MAKE) up
	$(DOCKER_EXEC_CMD) sh

.PHONY: sh
sh: ## Alias for shell command
	$(MAKE) shell

.PHONY: install
install: ## Install Composer dependencies
	$(MAKE) up
	$(DOCKER_EXEC_CMD) git config --global --add safe.directory /app || true
	$(DOCKER_EXEC_CMD) composer install

.PHONY: test
test: ## Run PHPUnit tests
	$(MAKE) up
	$(DOCKER_EXEC_CMD) composer test

.PHONY: format
format: ## Format code with Laravel Pint
	$(MAKE) up
	$(DOCKER_EXEC_CMD) composer format

.PHONY: analyse
analyse: ## Run PHPStan static analysis
	$(MAKE) up
	$(DOCKER_EXEC_CMD) composer analyse

.PHONY: check
check: ## Run all checks (format, analyse, test)
	$(MAKE) up
	$(DOCKER_EXEC_CMD) git config --global --add safe.directory /app || true
	$(DOCKER_EXEC_CMD) composer check

.PHONY: integration-test
integration-test: ## Run integration tests (requires .env). Usage: make integration-test [test=test-name]
	$(MAKE) up
	$(DOCKER_EXEC_CMD) composer test-integration $(test)

.PHONY: clean
clean: ## Remove containers, volumes, and vendor directory
	docker-compose down -v
	rm -rf vendor

.PHONY: rebuild
rebuild: ## Clean and rebuild everything
	$(MAKE) clean build install
