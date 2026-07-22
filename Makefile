# Convenience wrappers around docker compose. Run `make help` to list targets.
DC := docker compose
RUN := $(DC) run --rm app

.DEFAULT_GOAL := help

.PHONY: help build install test coverage analyse format ci shell

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

build: ## Build the Docker image
	$(DC) build

install: ## Install composer dependencies
	$(RUN) composer install

test: ## Run the test suite (Pest)
	$(RUN) composer test

coverage: ## Run tests with a 90% coverage gate
	$(RUN) composer test:coverage

analyse: ## Run PHPStan (level max)
	$(RUN) composer analyse

format: ## Fix code style (Pint)
	$(RUN) composer format

ci: ## Run the full CI gate (pint --test + phpstan + pest)
	$(RUN) composer ci

shell: ## Open a shell inside the container
	$(RUN) sh
