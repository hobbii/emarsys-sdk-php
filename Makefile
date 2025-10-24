.PHONY: help build up down shell test format analyse check install clean integration-test

# Default target
.DEFAULT_GOAL := help

## help: Display this help message
help:
	@echo "Available commands:"
	@echo ""
	@grep -E '^## ' $(MAKEFILE_LIST) | sed 's/## /  /' | column -t -s ':'

## build: Build the Docker image
build:
	docker-compose build

## up: Start the Docker container
up:
	docker-compose up -d

## down: Stop and remove the Docker container
down:
	docker-compose down

## shell: Open an interactive shell in the container
shell: up
	docker-compose exec php sh

## install: Install Composer dependencies
install: up
	docker-compose exec php composer install

## test: Run PHPUnit tests
test: up
	docker-compose exec php composer test

## format: Format code with Laravel Pint
format: up
	docker-compose exec php composer format

## analyse: Run PHPStan static analysis
analyse: up
	docker-compose exec php composer analyse

## check: Run all checks (format, analyse, test)
check: up
	docker-compose exec php composer check

## integration-test: Run integration tests (requires .env)
integration-test: up
	docker-compose exec php composer test-integration

## clean: Remove containers, volumes, and vendor directory
clean:
	docker-compose down -v
	rm -rf vendor

## rebuild: Clean and rebuild everything
rebuild: clean build install

