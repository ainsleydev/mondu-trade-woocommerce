# Constants
PORT := 8000
REPO_OWNER := ainsleydev
REPO_NAME := mondu-trade-woocommerce
GITHUB_API := https://api.github.com/repos/$(REPO_OWNER)/$(REPO_NAME)
DOCKER_COMPOSE_FILE := docker-compose.yml
ZIP_FILE_NAME := mondu-digital-trade-account.zip

# Functions
setup: # Setup Dependencies
	npm install -g localtunnel
	npm install -g concurrently
	wp package install wp-cli/dist-archive-command
.PHONY: setup

serve: # Serve Wordpress & Local Tunnel
	@export $(shell sed 's/^/export /' .env); \
	cd ../../../ && concurrently --names "wordpress,localtunnel,logs" --prefix-colors "blue,green,yellow" \
 		"MONDU_TRADE_ENV=dev php -S localhost:$(PORT)" \
 		"lt --port $(PORT) --subdomain mondu-digital-trade-account-woocommerce-ainsleydev" \
 		"tail -n 0 -f ./wp-content/debug.log"
.PHONY: serve

zip: # Zips the contents of the plugin under /dist
	rm dist/$(ZIP_FILE_NAME) || true
	wp dist-archive ./ dist/$(ZIP_FILE_NAME) --allow-root
.PHONY: zip

version: # Extracts the version from the php file.
	@grep -i "^[[:space:]]*\* Version:[[:space:]]*" ./mondu-digital-trade-account.php | sed -E 's/\*[[:space:]]*Version:[[:space:]]*([0-9]+\.[0-9]+\.[0-9]+).*/\1/' | tr -d '[:space:]' && echo ""
.PHONY: version

version-remote: # Gets the remote version from GitHub.
	@curl -s $(GITHUB_API)/releases/latest | jq -r '.tag_name'
.PHONY: version-remote

version-bump: # Asks for release type and bumps version
	sh ./bin/bump-wp-version.sh
.PHONY: version-bump

release: # Creates a new tag
	@local_version=$(shell $(MAKE) -s version) && \
	remote_version=$(shell $(MAKE) -s version-remote) && \
	if [ "$$local_version" = "$$remote_version" ]; then \
		echo "Local version ($$local_version) and remote version ($$remote_version) are the same. Exiting."; \
		exit 0; \
	else \
		echo "Local version ($$local_version) and remote version ($$remote_version) differ. Proceeding with the release..."; \
		sh ./bin/tag.sh $$local_version "Mondu Trade Release: $$local_version"; \
	fi
.PHONY: release

lint: # Runs Linter
	@composer lint
.PHONY: lint

users-seed: # Seeds three users to the db (accepted, pending & declined)
	sh bin/seed.sh seed
.PHONY: users-seed

users-delete: # Deletes the seeded users
	sh bin/seed.sh delete
.PHONY: users-delete

lint-fix: # Runs Linter with auto-fix
	@composer lint-fix
.PHONY: lint-fix

docker-build: # Rebuild Docker images
	docker-compose -f $(DOCKER_COMPOSE_FILE) build
.PHONY: docker-build

docker-up: # Start Docker containers
	docker-compose -f $(DOCKER_COMPOSE_FILE) up --build
.PHONY: docker-up

docker-down: # Stop Docker containers
	docker-compose -f $(DOCKER_COMPOSE_FILE) down
.PHONY: docker-down

docker-clean: # Remove stopped containers, networks, and volumes
	docker-compose -f $(DOCKER_COMPOSE_FILE) down --volumes --remove-orphans
.PHONY: docker-clean

clear-log: # Clear the contents of debug.log
	> ../../debug.log
.PHONY: clear-log

pack: # Packs repo into txt file (AI)
	repopack --ignore "**/*.log,tmp/,vendor/dist/" --output repopack.txt
.PHONY: pack

todo: # Show TODO items per file
	$(Q) @grep \
		--exclude=Makefile \
		--exclude=Makefile.util \
		--exclude=repopack.txt \
		--exclude-dir=vendor \
		--exclude-dir=.vercel \
		--exclude-dir=.gen \
		--exclude-dir=.idea \
		--exclude-dir=public \
		--exclude-dir=node_modules \
		--exclude-dir=archetypes \
		--exclude-dir=.git \
		--text \
		--color \
		-nRo \
		-E '\S*[^\.]TODO.*' \
		.
.PHONY: todo

help: # Display this help
	$(Q) awk 'BEGIN {FS = ":.*#"; printf "Usage: make \033[36m<target>\033[0m\n"} /^[a-zA-Z_-]+:.*?#/ { printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)
.PHONY: help
