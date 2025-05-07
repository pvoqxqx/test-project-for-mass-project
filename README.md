# Установка и настройка проекта

Для установки и настройки проекта выполните следующие шаги:

### 1. Зависимости. 

- composer install

### 2. Скопируйте файл .env.example в .env:

- cp .env.example .env

### 3. Настройка Docker

Построение и запуск контейнеров Docker:

docker-compose build
docker-compose up -d

### 4. Установка Passport
Запустите команду для установки Passport (убедитесь, что ключи созданы в /storage):

php artisan passport:install

### 5. Миграции и сиды

php artisan migrate
php artisan db:seed

### 6. Генерация Swagger документации

php artisan l5-swagger:generate

### 7. Включение очередей

php artisan queue:work

### 8. Доступ к сервисам

Swagger API документация: http://127.0.0.1:8000/api/documentation

Log Viewer: http://127.0.0.1:8000/log-viewer

### 9. Настройка почтового сервиса

Для уведомлений зайдите в Mailtrap и добавьте конфигурацию в .env, используя данные из вашего аккаунта.

Перейдите по ссылке: mailtrap.io

Пример конфигурации для .env:

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=d2f0f931123020
MAIL_PASSWORD=1d8be6713edfd8
MAIL_FROM_ADDRESS=denis.klevtsovs@gmail.com
MAIL_FROM_NAME="Bid System"