<p align="center">Teses & Súmulas</p>

##How to prepare local?

1. create db tes
2. import tes tables from production
3. clone repo
4. ``` mv laravel_tes [name] ```
5. ``` composer install ```
6. ``` npm install ```
7. ``` npm run dev ```
8. ``` cp .env.example .env ```

```
APP_NAME=[name]
APP_KEY=
APP_DEBUG=true
APP_URL=https://[name].test
DB_DATABASE=tes
DB_USERNAME=root
DB_PASSWORD=
```

9. ``` php artisan key:generate ```
10. ``` php artisan session:table ```
11. ``` php artisan migrate ```
12. ``` valet secure [name] ```
