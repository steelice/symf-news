# symf-news - тестовый проект на симфони для демонстрации кода

### Замечания к реализации

- В контроллерах я придерживался стандарта, чтобы никакие локали там не хранились, поэтому всё берется из переводов. 
В шаблонах же наоборот — я не стал тратить время и ресурсы на них, а прописал все локали прямо в шаблонах. 
- Тесты и визуальный редактор делать пока не стал т.к. их нет по ТЗ, но если необходимо — смогу быстро реализовать

### Установка

```
git clone https://github.com/steelice/symf-news.git
cd symf-news
composer install
yarn install
yarn dev
```

Скопировать .env в .env.local и настроить в нём подключение к БД

```
php bin/console doctrine:schema:update --force
symfony server:start
```

Настроить браузер на корень symf-news/public
