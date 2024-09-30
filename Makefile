include .env
export

.PHONY: install
install: deploy-cscart run-cscart-installer update-cscart-license install-multisafepay

.PHONY: deploy-cscart
deploy-cscart:
	unzip -o ./source/cscart_v${CSCART_VERSION}.zip -d ./cscart

.PHONY: run-cscart-installer
run-cscart-installer:
	docker-compose exec app curl -o /tmp/wait-for-it.sh https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh
	docker-compose exec app chmod +x /tmp/wait-for-it.sh
	docker-compose exec app /tmp/wait-for-it.sh db:3306 --timeout=30
	docker-compose exec app sed -i 's/localhost/db:3306/' install/config.php
	docker-compose exec app sed -i 's/%DB_NAME%/${MYSQL_DATABASE}/' install/config.php
	docker-compose exec app sed -i 's/%DB_USER%/${MYSQL_USER}/' install/config.php
	docker-compose exec app sed -i 's/%DB_PASS%/${MYSQL_PASSWORD}/' install/config.php
	docker-compose exec app sed -i 's/%HTTP_HOST%/https:\/\/${APP_SUBDOMAIN}.${EXPOSE_HOST}/' install/config.php
	docker-compose exec app /bin/sh -c 'cd install && php index.php'
	docker-compose exec app mysql -h db -u ${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE} -e "UPDATE ${MYSQL_TABLE_PREFIX}settings_objects SET value = 'Y' WHERE name = 'secure_admin' OR name = 'secure_storefront'"
	docker-compose exec app rm /tmp/wait-for-it.sh

.PHONY: update-cscart-license
update-cscart-license:
	docker-compose exec app mysql -h db -u $(MYSQL_USER) -p$(MYSQL_PASSWORD) $(MYSQL_DATABASE) -e \
  "UPDATE ${MYSQL_TABLE_PREFIX}settings_objects SET value = '$(CSCART_LICENSE_KEY)' WHERE name = 'license_number';"

.PHONY: install-multisafepay
install-multisafepay: msp-installer copy-modman modman-deploy
	docker-compose exec app chown -R www-data:www-data /var/www/ > /dev/null 2>&1

.PHONY: msp-installer
msp-installer:
	docker cp ./src/msp_installer.php $$(docker-compose ps -q app):/var/www/html/msp_installer.php
	docker-compose exec app chmod +x /var/www/html/msp_installer.php
	@docker-compose exec app php /var/www/html/msp_installer.php || (echo "msp_installer.php failed" && exit 1)

.PHONY: copy-modman
copy-modman:
	docker-compose exec app mkdir -p /var/www/html/.modman/multisafepay
	docker cp ./modman $$(docker-compose ps -q app):/var/www/html/.modman/multisafepay

.PHONY: modman-deploy
modman-deploy:
	docker-compose exec app modman deploy-all

.PHONY: modman
modman:
	docker-compose exec app modman deploy multisafepay --force --quiet

.PHONY: modman-copy
modman-copy:
	docker-compose exec app modman deploy multisafepay --copy --force --quiet
