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

#>>>docker
start:
	@docker-compose start

stop:
	@docker-compose stop

rebuild:
	@docker-compose up --build -d --no-deps

build:
	@docker-compose up --build
#<<<docker

#>>>docker-container
clear:
	@$(CONSOLE) cache:clear
	@composer cc
	@$(VENDOR)psalm --clear-cache

console:
	@$(CONSOLE) $(COMMAND)

bash:
	@$(DCE) bash
#<<<docker-container

#>>>rabbit
consumer-import:
	@$(CONSOLE) rabbitmq:consumer import_send

consumers-stop:
	@$(DCE) sh -c " ps -F | grep rabbitmq | awk '{ print $$ 2 }' | xargs kill -9"
#<<<rabbit

#>>>checkers
validate: validate-schema validate-composer check-security

check-code: cs-fixer cpd mnd stan psalm

validate-schema:
	$(call FUNC_WITH_PRINT, schema:validate, $(CONSOLE) doctrine:schema:validate)

validate-composer:
	$(call FUNC_WITH_PRINT, composer:validate, composer validate)

check-security:
	$(call FUNC_WITH_PRINT, check security, ./local-php-security-checker)

cs-fixer:
	$(call FUNC_WITH_PRINT, php-cs-fixer, $(VENDOR)php-cs-fixer fix --allow-risky=yes --verbose)

cpd:
	$(call FUNC_WITH_PRINT, phpcpd, $(VENDOR)phpcpd $(EXCLUDES) .)

mnd:
	$(call FUNC_WITH_PRINT, phpmnd, $(VENDOR)phpmnd run . $(EXCLUDES) --ignore-numbers=-1,0,1)

stan:
	$(call FUNC_WITH_PRINT, stan, $(VENDOR)phpstan analyze -c phpstan.neon)

psalm:
	$(call FUNC_WITH_PRINT, psalm, $(VENDOR)psalm)

tests-run:
	$(call FUNC_WITH_PRINT, tests, $(DCE) bin/phpunit)
#<<<checkers

#>>>git
reindex:
	@git diff --name-only --cached | xargs git add
#<<<git