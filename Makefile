include .env
export

.PHONY: install
install: install-cscart install-multisafepay

.PHONY: install-cscart
install-cscart: download-cscart run-cscart-installer install-multisafepay

.PHONY: run-cscart-installer
run-cscart-installer:
	docker-compose exec app sed -i 's/localhost/db:3306/' install/config.php
	docker-compose exec app sed -i 's/%DB_NAME%/${MYSQL_DATABASE}/' install/config.php
	docker-compose exec app sed -i 's/%DB_USER%/${MYSQL_USER}/' install/config.php
	docker-compose exec app sed -i 's/%DB_PASS%/${MYSQL_PASSWORD}/' install/config.php
	docker-compose exec app sed -i 's/%HTTP_HOST%/https:\/\/${APP_SUBDOMAIN}.${EXPOSE_HOST}/' install/config.php
	docker-compose exec app /bin/sh -c 'cd install && php index.php'
	docker-compose exec app mysql -hdb -u${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE} -e "UPDATE cscart_settings_objects SET value = 'Y' WHERE name = 'secure_admin' OR name = 'secure_storefront'"

.PHONY: download-cscart
download-cscart:
	docker-compose exec app curl -L "https://www.cs-cart.com/index.php?dispatch=pages.get_trial&page_id=297&edition=ultimate" -o cscart.zip -s
	docker-compose exec app unzip -o -q cscart.zip

.PHONY: install-multisafepay
install-multisafepay:
	docker-compose exec app php msp_installer.php

.PHONY: modman
modman:
	docker-compose exec app modman deploy multisafepay --force --quiet

.PHONY: modman-copy
modman-copy:
	docker-compose exec app modman deploy multisafepay --copy --force --quiet

