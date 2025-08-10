#!/usr/bin/make
# Makefile readme (en): <https://www.gnu.org/software/make/manual/html_node/index.html#SEC_Contents>

SHELL = /bin/bash
DC_RUN_ARGS = -f docker-compose.production.yml
HOST_UID=$(shell id -u)
HOST_GID=$(shell id -g)

.PHONY : help up down shell\:laravel stop-all ps update build restart down-up images\:list images\:clean logs\:laravel logs containers\:health command\:laravel
.DEFAULT_GOAL : help

# This will output the help for each task. thanks to https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
help: ## Show this help
	@printf "\033[33m%s:\033[0m\n" 'Available commands'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z0-9_-]+:.*?## / {printf "  \033[32m%-18s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

env: ## Init env file
	cp .env.example .env && cp laravel/.env.example laravel/.env

up: ## Up containers
	docker compose ${DC_RUN_ARGS} up -d --remove-orphans

logs: ## Tail all containers logs
	docker compose ${DC_RUN_ARGS} logs -f

logs\:laravel: ## laravel container logs
	docker compose ${DC_RUN_ARGS} logs laravel

down: ## Stop containers
	docker compose ${DC_RUN_ARGS} down

down\:with-volumes: ## Stop containers and remove volumes
	docker compose ${DC_RUN_ARGS} down -v

shell\:laravel: ## Start shell into laravel container
	docker compose ${DC_RUN_ARGS} exec laravel bash

command\:laravel: ## Run a command in the app container
	docker compose ${DC_RUN_ARGS} exec laravel sh -c "$(command)"

stop-all: ## Stop all containers
	docker stop $(shell docker ps -a -q)

ps: ## Containers status
	docker compose ${DC_RUN_ARGS} ps

build: ## Build images
	docker compose ${DC_RUN_ARGS} build

update: ## Update containers
	docker compose ${DC_RUN_ARGS} up -d --no-deps --build --remove-orphans

restart: ## Restart all containers
	docker compose ${DC_RUN_ARGS} restart

down-up: down up ## Down all containers, then up

images\:list: ## Sort Docker images by size
	docker images --format "{{.ID}}\t{{.Size}}\t{{.Repository}}" | sort -k 2 -h

images\:clean: ## Remove all dangling images and images not referenced by any container
	docker image prune -a

containers\:health: ## Check all containers health
	docker compose ${DC_RUN_ARGS} ps --format "table {{.Name}}\t{{.Service}}\t{{.Status}}"

ide_helpers: ## Generate IDE helper
	docker compose ${DC_RUN_ARGS} exec laravel php artisan ide-helper:generate
	docker compose ${DC_RUN_ARGS} exec laravel php artisan ide-helper:meta
	docker compose ${DC_RUN_ARGS} exec laravel php artisan ide-helper:models --nowrite
