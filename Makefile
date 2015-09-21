.PHONY: composer cs test

composer:
	composer validate
	composer install --prefer-dist

cs: composer
	vendor/bin/php-cs-fixer fix --config-file=.php_cs --verbose --diff

test: composer
	vendor/bin/phpunit --configuration phpunit.xml
