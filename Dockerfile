FROM composer:1.9.3 as composer

COPY composer.* /app/
RUN set -xe && composer install --no-dev --no-scripts --no-suggest --no-interaction --prefer-dist --optimize-autoloader
COPY . /app
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative


FROM php:7.3-cli
MAINTAINER Christian Kollross

RUN mkdir /podcasts /app /app/config \
	&& apt-get update && apt-get install -y libyaml-dev \
	&& pecl install yaml

WORKDIR /app

COPY . /app
COPY --from=composer /app/vendor /app/vendor
COPY *.php /app
COPY infra/php/php.ini /usr/local/etc/php/
COPY infra/php/init.sh /usr/local/bin/init.sh

RUN chmod +x /usr/local/bin/init.sh

CMD ["/usr/local/bin/init.sh"]
