# Libermedia New

Laravel 12 osnova za novu Liber Media trgovinu, kopirana iz projekta `herrera-new`.

Lokalno: http://libermedia-new.test

Projekt koristi zasebni `.env`, `APP_KEY` i SQLite bazu. Lokalni OpenCart izvor spojen je putem zasebne MySQL veze `libermedia_source`, sa session postavkom `TRANSACTION READ ONLY`.

## Lokalni uvoz

- `php artisan migrate`
- `php artisan libermedia:import --dry-run`
- `php artisan libermedia:import`
- Kopirati originalnu OpenCart mapu `image` u `public/image` bez generiranih predmemorija.
- `php artisan storage:link`
- `php artisan libermedia:import-assets`

Privatne postavke izvora nalaze se samo u `.env`. Baza, slike, izvještaji i sigurnosne kopije nisu u Gitu. Instalacija bez lokalnih podataka zahtijeva `composer install`, `npm install`, `npm run build` i novi `APP_KEY`.

Stanje prijenosa i poznate iznimke: [docs/libermedia-migration.md](docs/libermedia-migration.md).

Naslijeđene Herrera naredbe, integracije i deployment skripte zahtijevaju prilagodbu prije korištenja za Libermediju. Redizajn još nije rađen.
