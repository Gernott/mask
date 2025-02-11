.PHONY: help
help: ## Displays this list of targets with descriptions
	@echo "The following commands are available:\n"
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: docs
docs: ## Generate projects docs (from "Documentation" directory)
	mkdir -p Documentation-GENERATED-temp

	docker run --rm --pull always -v "$(shell pwd)":/project -t ghcr.io/typo3-documentation/render-guides:latest --config=Documentation

.PHONY: test-docs
test-docs: ## Test the documentation rendering
	mkdir -p Documentation-GENERATED-temp

	docker run --rm --pull always -v "$(shell pwd)":/project -t ghcr.io/typo3-documentation/render-guides:latest --config=Documentation --no-progress --fail-on-log

.PHONY: test
test: test-cs test-unit

.PHONY: test-cs
test-cs:
	Build/Scripts/runTests.sh -s cgl -n
	Build/Scripts/runTests.sh -s cglHeader -n
	XDEBUG_MODE="off" .Build/bin/phpstan analyse -c Build/phpstan/phpstan.neon --no-progress --no-interaction --memory-limit 4G

.PHONE: test-unit
test-unit:
	composer install --prefer-dist --no-progress
	Build/Scripts/runTests.sh -s unit -p 8.4
