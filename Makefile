setup: # Setup Dependencies
	npm install -g localtunnel
	npm install -g concurrently
	wp package install wp-cli/dist-archive-command
.PHONY: setup

serve: # Serve Wordpress & Local Tunnel
	@export $(shell sed 's/^/export /' .env); \
	cd ../../../ && concurrently --names "wordpress,localtunnel,logs" --prefix-colors "blue,green,yellow" \
 		"APP_ENV=dev php -S localhost:8000" \
 		"lt --port 8000 --subdomain mondu-trade-account-woocommerce-ainsleydev" \
 		"tail -n 0 -f ./wp-content/debug.log"
.PHONY: serve

zip: # Zips the contents of the plugin under /dist
	wp dist-archive ./ dist/mondu-trade-woocommerce.zip
.PHONY: zip

clear-log: # Clear the contents of debug.log
	> ../../debug.log
.PHONY: clear-log

pack: # Packs repo into txt file (AI)
	repopack --ignore "**/*.log,tmp/,vendor/dist/" --output repopack.txt
.PHONY: pack

todo: # Show TODO items per file
	$(Q) grep \
		--exclude=Makefile.util \
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
