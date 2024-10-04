include .env
export

# Escape "@" in the admin email to avoid issues with sed
CSCART_ESCAPED_ADMIN_EMAIL = $(subst @,\@,${CSCART_ADMIN_EMAIL})

# Escape "/" and "&" as they have a special meaning in sed and can be part of the password
CSCART_ESCAPED_ADMIN_PASSWORD = $(shell echo "${CSCART_ADMIN_PASSWORD}" | sed 's/[\/&]/\\&/g')

# Generate the secret key using openssl and base64, and escape special characters for later usage with sed
CSCART_SECRET_KEY := $(shell openssl rand -base64 32 | sed -e 's/[+/=]/\\&/g')

.PHONY: install
install: validate-email deploy-cscart run-cscart-installer install-multisafepay

.PHONY: validate-email
validate-email:
	@# NOTE: Character "@" suppresses the output of the command or line
	@if ! echo "${CSCART_ADMIN_EMAIL}" | grep -E '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$$' > /dev/null; then \
		echo "\nInvalid email address: ${CSCART_ADMIN_EMAIL}.\n\nExiting the script.\n"; \
		exit 1; \
	fi

.PHONY: check-folder-empty
check-folder-empty:
	@echo "Please enter your superuser credentials to allow deletion of necessary files and folders in the './cscart' directory:"
	@# Delete all files in cscart directory (except the root .gitkeep and .modman folder and its content).
	@# This deletion avoid conflicts with other previous installations, including different CS-Cart versions
	@sudo -k find ./cscart -mindepth 1 ! -path './cscart/.gitkeep' ! -path './cscart/.modman/multisafepay' -delete
	@# Check if the folder contains other than .gitkeep file and -modman folder (including its content)
	@if [ $$(find ./cscart -mindepth 1 -maxdepth 1 ! -path './cscart/.modman' ! -path './cscart/.modman/*' | wc -l) -ne 1 ]; then \
	echo "\nNot all files in the './cscart' folder were removed.\n\nPlease manually delete its contents, except for the root '.gitkeep' file and the '.modman' folder.\n"; \
	exit 1; \
	fi

.PHONY: deploy-cscart
deploy-cscart: check-folder-empty
	@# Decompress the CS-Cart archive to the cscart directory
	unzip -o ./source/cscart_v${CSCART_VERSION}.zip -d ./cscart

.PHONY: run-cscart-installer
run-cscart-installer:
	docker-compose exec app curl -o /tmp/wait-for-it.sh https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh
	docker-compose exec app chmod +x /tmp/wait-for-it.sh
	docker-compose exec app /tmp/wait-for-it.sh db:3306 --timeout=30
	docker-compose exec app sed -i 's/CART-1111-1111-1111-1111/${CSCART_LICENSE_KEY}/' install/config.php
	@# In the first segment of the sed command, the "@" in the email doesn’t need escaping since it’s a literal string, not a variable
	docker-compose exec app sed -i 's/admin@example.com/${CSCART_ESCAPED_ADMIN_EMAIL}/' install/config.php
	docker-compose exec app sed -i 's/admin/${CSCART_ESCAPED_ADMIN_PASSWORD}/' install/config.php
	docker-compose exec app sed -i 's/YOURVERYSECRETCEY/${CSCART_SECRET_KEY}/' install/config.php
	@# Delete in the config file non existing languages: "bg" and "sl"
	docker-compose exec app sed -i "s/'bg', 'no', 'sl'/'no'/" install/config.php
	docker-compose exec app sed -i 's/localhost/db:3306/' install/config.php
	docker-compose exec app sed -i 's/%DB_NAME%/${MYSQL_DATABASE}/' install/config.php
	docker-compose exec app sed -i 's/%DB_USER%/${MYSQL_USER}/' install/config.php
	docker-compose exec app sed -i 's/%DB_PASS%/${MYSQL_PASSWORD}/' install/config.php
	docker-compose exec app sed -i 's/%HTTP_HOST%/https:\/\/${APP_SUBDOMAIN}.${EXPOSE_HOST}/' install/config.php
	docker-compose exec app /bin/sh -c 'cd install && php index.php'
	docker-compose exec app mysql -h db -u ${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE} -e "UPDATE ${MYSQL_TABLE_PREFIX}settings_objects SET value = 'Y' WHERE name = 'secure_admin' OR name = 'secure_storefront'"
	docker-compose exec app rm /tmp/wait-for-it.sh

.PHONY: install-multisafepay
install-multisafepay: msp-installer modman-deploy
	@# - ignore the error if the command fails
	@# || true ensures that the command returns a successful exit code due
	@# to the errors that chown throws when pass through .git folder
	@-docker-compose exec app chown -R www-data:www-data /var/www/ > /dev/null 2>&1 || true

.PHONY: msp-installer
msp-installer:
	docker cp ./src/msp_installer.php $$(docker-compose ps -q app):/var/www/html/msp_installer.php
	docker-compose exec app chmod +x /var/www/html/msp_installer.php
	@docker-compose exec app php /var/www/html/msp_installer.php || (echo "msp_installer.php failed" && exit 1)

.PHONY: modman-deploy
modman-deploy:
	docker-compose exec app modman deploy-all

.PHONY: modman
modman:
	docker-compose exec app modman deploy multisafepay --force --quiet

.PHONY: modman-copy
modman-copy:
	docker-compose exec app modman deploy multisafepay --copy --force --quiet
