up:
	docker-compose up -d
bash:
	docker exec -it sales_api bash
db:
	docker exec -it sales_api_db mysql -u root -p'123456'
test:
	docker exec sales_api vendor/bin/phpunit
migrate:
	docker exec sales_api php artisan migrate
rollback:
	docker exec sales_api php artisan migrate:rollback
