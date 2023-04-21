.PHONY: all test

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
	docker build --no-cache --target development -t $(IMAGE_NAME):$(IMAGE_TAG_DEV) ./docker

composer-install: ## Instala las dependencias via composer
	docker run --rm -v ${PWD}/app:/app -w /app $(IMAGE_NAME):$(IMAGE_TAG_DEV) composer install --verbose

composer-update: ## Actualiza las dependencias via composer
	docker run --rm -v ${PWD}/app:/app -w /app $(IMAGE_NAME):$(IMAGE_TAG_DEV) composer update --verbose

composer-require: ## Añade nuevas dependencias de producción
	docker run --rm -ti -v ${PWD}/app:/app -w /app $(IMAGE_NAME):$(IMAGE_TAG_DEV) composer require --verbose

composer-require-dev: ## Añade nuevas dependencias de desarrollo
	docker run --rm -ti -v ${PWD}/app:/app -w /app $(IMAGE_NAME):$(IMAGE_TAG_DEV) composer require --dev --verbose

# TESTING COMMANDS ----------------------------------------------------------------------------------------------------
test: ## PHPUnit test
	docker compose exec slim php ./vendor/bin/phpunit --no-coverage --color=always

test-coverage: ## PHPUnit test and coverage
	docker run --rm -v ${PWD}:/app -w /app $(IMAGE_NAME):$(IMAGE_TAG_DEV) rm -rf test/report || echo "No existe la carpeta de reportes previamente"
	docker compose exec -e "XDEBUG_MODE=coverage" slim php ./vendor/bin/phpunit --color=always

test-mutant: ## Infection Mutant Testing
	docker run --rm -e "XDEBUG_MODE=coverage" -v ${PWD}/app:/app -w /app $(IMAGE_NAME):$(IMAGE_TAG_DEV) php ./vendor/bin/infection

# OTHER COMMANDS & UTILS -----------------------------------------------------------------------------------------------
up: ## Levanta los servicios
	docker compose up -d

down: ## Tira los servicios
	docker compose down