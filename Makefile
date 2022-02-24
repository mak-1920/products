DCE = docker-compose exec -T php
CONSOLE = $(DCE) bin/console
COMMAND = $(filter-out $@,$(MAKECMDGOALS))
VENDOR = vendor/bin/
EXCLUDES = --exclude vendor/ --exclude var/ --exclude tests/ --exclude tmp/ --exclude migrations/
define FUNC_WITH_PRINT
	@printf ">>>$1 start\n"
	@$2
	@printf "<<<$1 finish\n\n"
endef

start:
	@docker-compose start

stop:
	@docker-compose stop

rebuild:
	@docker-compose -d --build --no-deps

build:
	@docker-compose -d --build

consumer-import:
	@$(CONSOLE) rabbitmq:consumer import_send

validate: validate-schema validate-composer

validate-schema:
	$(call FUNC_WITH_PRINT, schema:validate, $(CONSOLE) doctrine:schema:validate)

validate-composer:
	$(call FUNC_WITH_PRINT, composer:validate, composer validate)

check-code: cs-fixer cpd mnd stan

cs-fixer:
	$(call FUNC_WITH_PRINT, php-cs-fixer, $(VENDOR)php-cs-fixer fix --allow-risky=yes --verbose)

cpd:
	$(call FUNC_WITH_PRINT, phpcpd, $(VENDOR)phpcpd $(EXCLUDES) .)

mnd:
	$(call FUNC_WITH_PRINT, phpmnd, $(VENDOR)phpmnd run . $(EXCLUDES) --ignore-numbers=-1,0,1)

stan:
	$(call FUNC_WITH_PRINT, stan, $(VENDOR)phpstan analyze -c phpstan.neon)

tests-run:
	$(call FUNC_WITH_PRINT, tests, $(DCE) bin/phpunit)

clear:
	@$(CONSOLE) cache:clear
	@composer cc

console:
	@$(CONSOLE) $(COMMAND)

bash:
	@$(DCE) bash