up:
	clear
	make stop
	docker-compose up -d
	#Sleep for 3 seconds to allow the containers to start
	echo "Containers are starting..."
	sleep 3
stop:
	docker-compose stop
	docker system prune -f
ps:
	docker-compose ps
full-restart:
	clear
	make stop
	docker system prune -f
	docker-compose up -d --build
shell:
	clear
	docker-compose exec $(arg) bash
build:
	docker-compose build