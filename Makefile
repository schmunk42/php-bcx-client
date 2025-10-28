.PHONY: help build up down shell composer test cs-fix cs-check phpstan clean install example oauth-flow token-refresh

.DEFAULT_GOAL := help

help: ##@other Show this help
	@echo ""
	@echo "Available targets:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?##@[a-zA-Z_-]+ .*$$' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?##@"}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}' | \
		sed 's/@[a-zA-Z_-]* //'
	@echo ""

build: ##@docker Build Docker image
	docker compose build

up: ##@docker Start containers
	docker compose up -d

down: ##@docker Stop containers
	docker compose down

shell: ##@docker Open shell in PHP container
	docker compose run --rm php sh

composer: ##@docker Run composer install
	docker compose run --rm php composer install

install: build composer ##@setup Build image and install dependencies

test: ##@development Run PHPUnit tests
	docker compose run --rm php composer test

cs-fix: ##@development Fix code style
	docker compose run --rm php composer cs-fix

cs-check: ##@development Check code style
	docker compose run --rm php composer cs-check

phpstan: ##@development Run static analysis
	docker compose run --rm php composer phpstan

example: ##@examples Run basic usage example
	docker compose run --rm php php examples/basic-usage.php

oauth-flow: ##@examples Run OAuth flow example (get access token)
	docker compose run --rm php php examples/oauth-flow.php

token-refresh: ##@examples Run token refresh example
	docker compose run --rm php php examples/token-refresh.php

clean: ##@docker Remove containers and volumes
	docker compose down -v
	rm -rf vendor
