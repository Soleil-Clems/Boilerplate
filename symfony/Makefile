.PHONY: help build start stop
ARGS = $(filter-out $@,$(MAKECMDGOALS))
up:
	@echo "=== Demarrage du conteneur... ==="
	docker compose -f .docker/compose.yml up

down:
	 @echo "=== Arret du conteneur... ==="
	 docker compose -f .docker/compose.yml down  && clear

down-v:
	 @echo "=== Arret du conteneur et les volumes... ==="
	 docker compose -f .docker/compose.yml down -v && clear
build:
	  @echo "=== Construction de l'image... ==="
	  docker compose -f .docker/compose.yml build --no-cache

upscale:
	@echo "=== Mise à l'échelle du conteneur... ==="
	docker compose -f .docker/compose.yml up --scale boilerplate_php=3 -d
stopall:
		@echo "=== Arret de tous les containers actifs... ==="
		@if [ -n "$$(docker ps -q)" ]; then \
			docker stop $$(docker ps -q); \
		else \
			echo "Aucun container actif."; \
		fi

stop: down stopall

connect:
	@# Si ARGS est vide, on utilise boilerplate_php, sinon on prend le premier argument
	$(eval CONTAINER=$(if $(ARGS),$(firstword $(ARGS)),boilerplate_php))
	@echo "=== Connexion au container : $(CONTAINER) ==="
	@docker exec -it $(CONTAINER) bash

reload: down up
		@echo "=== Arret du container et redemarage... ==="

logs:
	#$(eval CONTAINER=$(if $(ARGS),$(firstword $(ARGS)),boilerplate_php))
	@echo "=== Affichage des logs... ==="
	docker compose -f .docker/compose.yml logs -f

prune:
	@echo "=== Suppression des conteneurs, réseaux, images et volumes inutilisés... ==="
	docker system prune -a

prune-volumes:
	@echo "=== Suppression des volumes inutilisés... ==="
	docker volume prune

help:
	@echo "Utilisation :"
	@echo "  make build    - Construit l'image"
	@echo "  make start    - Démarre les services"
	@echo "  make stop     - Arrête les services"
	@echo "  make help     - Affiche ce message"%
