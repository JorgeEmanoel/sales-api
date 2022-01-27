up:
	docker-compose up -d
install: up
	docker exec sales_api composer install
bash: up
	docker exec -it sales_api bash
db: up
	docker exec -it sales_api_db mysql -u root -p'123456'
test: install
	docker exec sales_api vendor/bin/phpunit
migrate: install
	docker exec sales_api php artisan migrate
rollback: install
	docker exec sales_api php artisan migrate:rollback
