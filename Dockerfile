FROM alpine:3.11
MAINTAINER Christian Kollross

RUN apk add --update php7 php7-pecl-yaml \
	&& mkdir /podcasts /root/config

WORKDIR /root
ENTRYPOINT ["/bin/sh"]
	
COPY *.php /root
