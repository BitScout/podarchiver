#!/bin/sh
set -e

bin/console cache:warm

php-fpm --nodaemonize
