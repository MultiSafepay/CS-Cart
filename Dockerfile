FROM php:7.3-apache

RUN apt-get update && apt-get upgrade -y
ARG DEBIAN_FRONTEND=noninteractive
ENV TZ=Europe/Amsterdam
RUN apt-get install -y software-properties-common git curl zip mariadb-client libicu-dev libxml2-dev libxslt-dev libfreetype6-dev libjpeg-dev libpng-dev libzip-dev libcurl3-dev

# set up mailhog connection
RUN debconf-set-selections << "postfix postfix/main_mailer_type string 'Internet Site'"
RUN apt-get install --assume-yes postfix
RUN sed -i -E "s/( *relayhost *= *).*/\1[mail]:1025/g" /etc/postfix/main.cf

RUN pecl install xdebug-3.1.0 && docker-php-ext-enable xdebug
RUN { \
        echo 'xdebug.mode=debug'; \
        echo 'xdebug.start_with_request=trigger'; \
        echo 'xdebug.client_host=host.docker.internal'; \
        echo 'xdebug.idekey=PHPSTORM'; \
	} | tee -a "/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
RUN rm -rf /var/www/html

#install CS Cart dependencies
RUN docker-php-ext-install mbstring zip xml soap pdo_mysql mysqli curl sockets exif
RUN apt-get update && apt-get install -y \
    imagemagick libmagickwand-dev --no-install-recommends \
    && pecl install imagick \
    && docker-php-ext-enable imagick
RUN curl https://raw.githubusercontent.com/colinmollenhour/modman/master/modman --silent --output /usr/local/bin/modman && chmod +x /usr/local/bin/modman

RUN { \
		echo '<FilesMatch \.php$>'; \
		echo '\tSetHandler application/x-httpd-php'; \
		echo '</FilesMatch>'; \
		echo; \
		echo 'DirectoryIndex disabled'; \
		echo 'DirectoryIndex index.php index.html'; \
		echo; \
		echo '<Directory /var/www/html/>'; \
		echo '\tOptions +Indexes +FollowSymLinks'; \
		echo '\tAllowOverride All'; \
		echo '\tRequire all granted'; \
		echo '\tOrder allow,deny'; \
		echo '\tAllow from all'; \
		echo '\tSetEnvIf X-Forwarded-Proto https HTTPS=on'; \
		echo '\tSetEnvIf X-Forwarded-Host ^(.+) HTTP_X_FORWARDED_HOST=$1'; \
		echo '\tRequestHeader set Host %{HTTP_X_FORWARDED_HOST}e env=HTTP_X_FORWARDED_HOST'; \
		echo '</Directory>'; \
	} | tee "/etc/apache2/conf-available/docker-php.conf" \
	&& a2enconf docker-php && a2enmod headers && a2enmod rewrite

RUN chown -R www-data:www-data /var/www/

USER www-data
