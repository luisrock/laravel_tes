<p align="center">Teses & Súmulas</p>

1. create db tes
2. import tes tables

```
RENAME TABLE `tes`.`fon_civ_sumulas` TO `tes`.`fonaje_civ_sumulas`;
RENAME TABLE `tes`.`fon_cri_sumulas` TO `tes`.`fonaje_cri_sumulas`;
RENAME TABLE `tes`.`fon_faz_sumulas` TO `tes`.`fonaje_faz_sumulas`;
```

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
