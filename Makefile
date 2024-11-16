setup: # Setup
	npm install -g localtunnel
	npm install -g concurrently
.PHONY: setup

serve: # Serve Wordpress & Local Tunnel
	cd ../../../ && concurrently --names "wordpress,localtunnel,logs" --prefix-colors "blue,green,yellow" \
 		"APP_ENV=dev php -S localhost:8000" \
 		"lt --port 8000 --subdomain mondu-resinbound-ainsleydev" \
 		"tail -f  ./wp-content/debug.log"
.PHONY: serve

todo: # Show to-do items per file
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
