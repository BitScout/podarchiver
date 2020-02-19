.PHONY: help install-default-config run

help: ## Show help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'

install-default-config: ## Create a first simple config file
	cp config.yaml.dist config/config.yaml

build-container: ## Build container for development test
	docker build -t ckollross/podarchiver:dev .

run: ## Start downloading episodes using the container
	docker exec podarchiver bin/console app:download

php-fix: ## Fix PHP code style
	bin/php-cs-fixer fix .
