Тестовое задание с использование спцеификации OpenAPI:

# чтобы запустить проект нужно:

# 1) Поднять контейнеры:
docker compose -f .\docker-compose.yml up --build
# 2) Перейти в баш:
docker compose exec php-fpm bash
# внутри контейнера:
composer install
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
php artisan migrate


После всех действий отправлять запросы через postman на http://localhost:82/api
# Swagger находится по маршруту http://localhost:82/docs
![img.png](img.png)
