# README
## How it works
- Import command
```shell
bin/console data:import
```
- Broadcast api | server - server | async | see [fruits-vegetables.postman_collection.json](Postman/fruits-vegetables.postman_collection.json)
```shell
curl --location 'https://localhost/api/v0/broadcast-listener/plant' \
--header 'Content-Type: application/json' \
--header 'Authorization: Basic YnJvYWRjYXN0ZXI6YWRtaW4xMjM=' \
--data '{
    "id": 1,
    "name": "Carrot",
    "type": "vegetable",
    "quantity": 10922,
    "unit": "g"
}'
```
- Search api | fruit - vegetable separate endpoint | see [fruits-vegetables.postman_collection.json](Postman/fruits-vegetables.postman_collection.json)
```shell
curl --location 'https://localhost/api/v0/fruits?query=a&minQty=10&maxQty=100&unit=kg'
```
- Add new api | crud | fruit - vegetable separate endpoint | see [fruits-vegetables.postman_collection.json](Postman/fruits-vegetables.postman_collection.json)
```shell
# 409 Conflict
curl --location 'https://localhost/api/v0/fruit' \
--header 'Content-Type: application/json' \
--header 'Authorization: Basic YWRtaW46YWRtaW4xMjM=' \
--data '{
    "goldenId": 2,
    "name": "Grapes",
    "quantity": 2,
    "unit": "kg"
}'

# 201 Created
curl --location 'https://localhost/api/v0/fruit' \
--header 'Content-Type: application/json' \
--header 'Authorization: Basic YWRtaW46YWRtaW4xMjM=' \
--data '{
    "goldenId": 100,
    "name": "Grapes",
    "quantity": 2,
    "unit": "kg"
}'
```
- Update api | crud | fruit - vegetable separate endpoint | see [fruits-vegetables.postman_collection.json](Postman/fruits-vegetables.postman_collection.json)
```shell
# 404 Not found
curl --location --request PATCH 'https://localhost/api/v0/fruit/100' \
--header 'Content-Type: application/json' \
--header 'Authorization: Basic YWRtaW46YWRtaW4xMjM=' \
--data '{
    "goldenId": 2,
    "name": "Apples",
    "quantity": 20,
    "unit": "kg"
}'

# 422 Unprocessable Entity (Invalid type)
curl --location --request PATCH 'https://localhost/api/v0/fruit/1' \
--header 'Content-Type: application/json' \
--header 'Authorization: Basic YWRtaW46YWRtaW4xMjM=' \
--data '{
    "goldenId": 2,
    "name": "Apples",
    "quantity": 20,
    "unit": "kg"
}'

# 202 Accepted
curl --location --request PATCH 'https://localhost/api/v0/fruit/2' \
--header 'Content-Type: application/json' \
--header 'Authorization: Basic YWRtaW46YWRtaW4xMjM=' \
--data '{
    "goldenId": 2,
    "name": "Apples",
    "quantity": 20,
    "unit": "kg"
}'
```
- Delete api | crud | fruit - vegetable separate endpoint | see [fruits-vegetables.postman_collection.json](Postman/fruits-vegetables.postman_collection.json)
```shell
# 404 
curl --location --request DELETE 'https://localhost/api/v0/fruit/100' \
--header 'Authorization: Basic YWRtaW46YWRtaW4xMjM=' \
--data ''

# 204 No content
curl --location --request DELETE 'https://localhost/api/v0/fruit/2' \
--header 'Authorization: Basic YWRtaW46YWRtaW4xMjM=' \
--data ''
```

## Code quality
- docker exec fruits-vegetables-php-1 vendor/bin/phpcs --standard=phpcs.xml.dist
- docker exec fruits-vegetables-php-1 vendor/bin/php-cs-fixer fix -v --dry-run
- docker exec fruits-vegetables-php-1 php -d memory_limit=-1 vendor/bin/phpstan analyse
- docker exec fruits-vegetables-php-1 vendor/bin/psalm
- docker exec fruits-vegetables-php-1 vendor/bin/phpunit --testsuite=UnitTests

## Docker
[Symfony Docker](SYMFONY_DOCKER.md)

## Requirements
[fruits-and-vegetables-challenge](FRUITS_VEGETABLES.md)
