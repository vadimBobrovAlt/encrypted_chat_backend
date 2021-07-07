# Encrypted chat backend

### Практическая работа Backend чать


#### Установка зависимостей
```
composer install
```

#### Настройка подключения к бд 
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3366
DB_DATABASE=app_chat
DB_USERNAME=root
DB_PASSWORD=root
```

#### В приложение используется модуль отправки SMS для его активации необходимо зарегистироваться на сайте  https://new.smsgorod.ru и польучить токен доступа после чего задать его в .env
```
SMS_API_KEY=12345
```

#### Настройка проекта
```
php artisan migrate
php artisan key:generate
php artisan optimize
```

#### Запуск
```
php artisan serve
```


