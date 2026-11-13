# Shortcuts for the local environment. Run `make` or `make help` to list them.

.DEFAULT_GOAL := help
.PHONY: help up down logs check site-create

help: ## List the commands
	@grep -E '^[a-z-]+:.*## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*## "} {printf "  make %-12s %s\n", $$1, $$2}'

up: ## Start the local environment on http://localhost:8080 (add PROFILE=mysql, mariadb or postgres for a database)
	docker compose $(if $(PROFILE),--profile $(PROFILE)) up --detach --build --wait

down: ## Stop the local environment (all profiles)
	docker compose --profile "*" down

logs: ## Follow the logs
	docker compose logs --follow

check: ## Run every check (code style, static analysis, tests, coverage) in the PHP container
	docker compose run --rm --no-deps php composer check

site-create: ## Create a site: make site-create NAME=demo (not available yet)
	docker compose run --rm --no-deps php bin/sightpress site:create $(NAME)
