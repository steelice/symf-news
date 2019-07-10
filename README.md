# symf-news - тестовый проект на симфони для демонстрации кода

Установка

```
git clone https://github.com/steelice/symf-news.git
cd symf-news
```

Скопировать .env в .env.local и настроить в нём подключение к БД

```
composer install
yarn install
yarn dev
php bin/console doctrine:schema:update --force
```

Настроить браузер на корень symf-news/public
