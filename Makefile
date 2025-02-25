# COLORS
RESET = \033[0m
BLUE = \033[36m
GREEN = \033[0;32m
RED = \033[0;31m

help:
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make ${BLUE}[target]${RESET}\n"}/^[a-zA-Z0-9_-]+:.*?##/ \
	{ printf "  ${RED}%-10s${RESET}%s\n", $$1, $$2 } /^##@/ \
	{ printf "\n${BLUE}%s${RESET}\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

style: ## run phpcs & phpstan
	@$(MAKE) phpcs
	@$(MAKE) phpstan


phpcbf: ## run phpcbf
	@docker run --rm --platform linux/amd64 -v $(shell pwd):/app php:8.3-cli /bin/sh -c "cd /app && php vendor/bin/phpcbf --standard=psr12 src/"

phpunit: ## run phpstan
	@docker run --rm --platform linux/amd64 -v $(shell pwd):/app jitesoft/phpunit:8.3 /bin/sh -c "cd /app && php vendor/bin/phpunit --coverage-text --coverage-clover=coverage.clover"

phpcs: ## run phpcs
	@docker run --rm --platform linux/amd64 -v $(shell pwd):/app php:8.3-cli /bin/sh -c "cd /app && php vendor/bin/phpcs --standard=psr12 src/"

composer-install: ## run composer install
	@docker run --rm --platform linux/amd64 -v $(shell pwd):/app composer:lts /bin/sh -c "composer install --ignore-platform-reqs"
