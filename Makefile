.PHONY: all

# CONFIG ---------------------------------------------------------------------------------------------------------------
ifneq (,$(findstring xterm,${TERM}))
    BLACK   := $(shell tput -Txterm setaf 0)
    RED     := $(shell tput -Txterm setaf 1)
    GREEN   := $(shell tput -Txterm setaf 2)
    YELLOW  := $(shell tput -Txterm setaf 3)
    BLUE    := $(shell tput -Txterm setaf 4)
    MAGENTA := $(shell tput -Txterm setaf 5)
    CYAN    := $(shell tput -Txterm setaf 6)
    WHITE   := $(shell tput -Txterm setaf 7)
    RESET   := $(shell tput -Txterm sgr0)
else
    BLACK   := ""
    RED     := ""
    GREEN   := ""
    YELLOW  := ""
    BLUE    := ""
    MAGENTA := ""
    CYAN    := ""
    WHITE   := ""
    RESET   := ""
endif

COMMAND_COLOR := $(GREEN)
HELP_COLOR := $(BLUE)

IMAGE_NAME=graphicresources/itpg-api-coding-task
IMAGE_TAG_BASE=base
IMAGE_TAG_DEV=development

# DEFAULT COMMANDS -----------------------------------------------------------------------------------------------------
all: help

help: ## Listar comandos disponibles en este Makefile
	@echo "╔══════════════════════════════════════════════════════════════════════════════╗"
	@echo "║                           ${CYAN}.:${RESET} AVAILABLE COMMANDS ${CYAN}:.${RESET}                           ║"
	@echo "╚══════════════════════════════════════════════════════════════════════════════╝"
	@echo ""
	@grep -E '^[a-zA-Z_0-9%-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "${COMMAND_COLOR}%-40s${RESET} ${HELP_COLOR}%s${RESET}\n", $$1, $$2}'
	@echo ""


# BUILD COMMANDS -------------------------------------------------------------------------------------------------------
build: build-container composer-install ## Construye las dependencias del proyecto

build-container: ## Construye el contenedor de la aplicación
	docker build --no-cache --target development -t $(IMAGE_NAME):$(IMAGE_TAG_DEV) .

composer-install: ## Instala las dependencias via composer
	docker run --rm -v ${PWD}/app:/app -w /app $(IMAGE_NAME):$(IMAGE_TAG_DEV) composer install --verbose

composer-update: ## Actualiza las dependencias via composer
	docker run --rm -v ${PWD}/app:/app -w /app $(IMAGE_NAME):$(IMAGE_TAG_DEV) composer update --verbose

composer-require: ## Añade nuevas dependencias de producción
	docker run --rm -ti -v ${PWD}/app:/app -w /app $(IMAGE_NAME):$(IMAGE_TAG_DEV) composer require --verbose

composer-require-dev: ## Añade nuevas dependencias de desarrollo
	docker run --rm -ti -v ${PWD}/app:/app -w /app $(IMAGE_NAME):$(IMAGE_TAG_DEV) composer require --dev --verbose

dot-env: ## Copia el archivo .env.dist a .env
	@if [ ! -f app/.env ]; then \
		cp app/.env.dist app/.env; \
		echo "Archivo .env creado con éxito"; \
	else \
		echo "Archivo .env ya existe"; \
	fi

test: ## Ejecuta los tests
	docker compose exec php vendor/bin/phpunit --colors=always test

test-unit: ## Ejecuta los tests unitarios
	docker compose exec php vendor/bin/phpunit --colors=always test --group unit

test-acceptance: ## Ejecuta los tests de aceptación
	docker compose exec php vendor/bin/phpunit --colors=always test --group acceptance

test-integration: ## Ejecuta los tests de integración
	docker compose exec php vendor/bin/phpunit --colors=always test --group integration

test-group-%: ## Ejecuta los tests de un grupo
	docker compose exec php vendor/bin/phpunit --colors=always test --group $*

test-coverage: ## Ejecuta los tests de cobertura
	docker compose exec php vendor/bin/phpunit --colors=always test --coverage-html test/coverage