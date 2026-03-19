.PHONY: help build-dev build-prod up-dev up-prod down-dev down-prod logs-dev logs-prod connect-dev connect-prod

ARGS = $(filter-out $@,$(MAKECMDGOALS))

DEV_FILE = .docker/compose-dev.yml
PROD_FILE = .docker/compose-prod.yml

# ======================
# DEV
# ======================

up:
	@echo "=== Demarrage des conteneurs DEV... ==="
	docker compose -f $(DEV_FILE) up -d

down:
	@echo "=== Arret des conteneurs DEV... ==="
	docker compose -f $(DEV_FILE) down

down-v:
	@echo "=== Arret DEV + suppression volumes... ==="
	docker compose -f $(DEV_FILE) down -v

build:
	@echo "=== Build DEV... ==="
	docker compose -f $(DEV_FILE) build --no-cache

logs:
	@echo "=== Logs DEV... ==="
	docker compose -f $(DEV_FILE) logs -f

connect:
	$(eval CONTAINER=$(if $(ARGS),$(firstword $(ARGS)),boilerplate_php))
	@echo "=== Connexion DEV : $(CONTAINER) ==="
	@docker exec -it $(CONTAINER) bash

reload: stop up
	@echo "=== Reload DEV ==="


# ======================
# PROD
# ======================

up-prod:
	@echo "=== Demarrage des conteneurs PROD... ==="
	docker compose -f $(PROD_FILE) up -d

down-prod:
	@echo "=== Arret des conteneurs PROD... ==="
	docker compose -f $(PROD_FILE) down

down-prod-v:
	@echo "=== Arret PROD + suppression volumes... ==="
	docker compose -f $(PROD_FILE) down -v

build-prod:
	@echo "=== Build PROD... ==="
	docker compose -f $(PROD_FILE) build --no-cache

logs-prod:
	@echo "=== Logs PROD... ==="
	docker compose -f $(PROD_FILE) logs -f

connect-prod:
	$(eval CONTAINER=$(if $(ARGS),$(firstword $(ARGS)),boilerplate_php))
	@echo "=== Connexion PROD : $(CONTAINER) ==="
	@docker exec -it $(CONTAINER) bash

reload-prod: down-prod up-prod
	@echo "=== Reload PROD ==="


# ======================
# GLOBAL
# ======================

stopall:
	@echo "=== Arret de tous les containers actifs... ==="
	@if [ -n "$$(docker ps -q)" ]; then \
		docker stop $$(docker ps -q); \
	else \
		echo "Aucun container actif."; \
	fi

prune:
	@echo "=== Nettoyage global Docker... ==="
	docker system prune -a

prune-volumes:
	@echo "=== Suppression des volumes inutilisés... ==="
	docker volume prune

stop: down stopall
# ======================
# HELP
# ======================

help:
	@echo "Commandes disponibles :"
	@echo ""
	@echo "DEV :"
	@echo "  make up-dev           - Démarre l'environnement dev"
	@echo "  make down-dev         - Stop dev"
	@echo "  make down-dev-v       - Stop dev + volumes"
	@echo "  make build-dev        - Build dev"
	@echo "  make logs-dev         - Logs dev"
	@echo "  make connect-dev      - Shell dans container dev"
	@echo ""
	@echo "PROD :"
	@echo "  make up-prod          - Démarre prod"
	@echo "  make down-prod        - Stop prod"
	@echo "  make down-prod-v      - Stop prod + volumes"
	@echo "  make build-prod       - Build prod"
	@echo "  make logs-prod        - Logs prod"
	@echo "  make connect-prod     - Shell dans container prod"
	@echo ""
	@echo "GLOBAL :"
	@echo "  make stopall          - Stop tous les containers"
	@echo "  make prune            - Nettoyage Docker complet"
	@echo "  make prune-volumes    - Nettoyage volumes"
