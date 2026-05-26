# Makefile

include .env

DOCKER_PHP_EXECUTABLE := docker exec -ti $(DOCKER_CONTAINER_PREFIX_NAME)_php_apache

create:
	docker compose up -d --build --remove-orphans

start:
	docker compose up -d --remove-orphans

stop:
	docker compose down --remove-orphans

destroy:
	docker compose down --volumes --remove-orphans

recreate: destroy create

composer/install:
	$(DOCKER_PHP_EXECUTABLE) composer install

composer/update:
	$(DOCKER_PHP_EXECUTABLE) composer update

rector:
	$(DOCKER_PHP_EXECUTABLE) vendor/bin/rector process src --config=rector.php

php_cs_fixer:
	$(DOCKER_PHP_EXECUTABLE) vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php

logs:
	docker logs $(DOCKER_CONTAINER_PREFIX_NAME)_php_apache

shell:
	$(DOCKER_PHP_EXECUTABLE) /bin/sh

test:
	$(DOCKER_PHP_EXECUTABLE) vendor/bin/phpunit
