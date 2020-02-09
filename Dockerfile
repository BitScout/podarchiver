FROM php:7.3-cli
MAINTAINER Christian Kollross

RUN mkdir /podcasts /root/config \
	&& apt-get update && apt-get install -y libyaml-dev \
	&& pecl install yaml

WORKDIR /root

COPY *.php /root
COPY php/php.ini /usr/local/etc/php/

CMD tail -f /dev/null
