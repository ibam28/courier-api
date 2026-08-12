# Courier API — Laravel 11

REST API + admin UI for courier master data. Built with Laravel 11 + SQLite + Tailwind v4.

🌐 **Documentation languages:**
- 🇬🇧 [English](./docs/en/README.md) — default
- 🇮🇩 [Bahasa Indonesia](./docs/id/README.md)

---

## Repository structure

```
courier-api/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/CourierController.php     ← REST CRUD (/api/couriers)
│   │   └── CourierPageController.php     ← Blade page (/couriers)
│   └── Models/Courier.php
├── database/
│   ├── migrations/2026_08_12_*_create_couriers_table.php
│   └── seeders/CourierSeeder.php         ← 8 sample couriers
├── resources/views/couriers/
│   ├── index.blade.php                   ← main UI
│   └── partials/row.blade.php
├── routes/
│   ├── api.php                           ← Route::apiResource('couriers', ...)
│   └── web.php                           ← GET /couriers → Blade page
└── docs/
    ├── en/                               ← English documentation
    └── id/                               ← Indonesian documentation
```

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed --force
npm install
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Open in your browser:

- **UI**: http://127.0.0.1:8000/couriers
- **API root**: http://127.0.0.1:8000/api/couriers

## Tech stack

| Layer        | Choice                                        |
|--------------|-----------------------------------------------|
| PHP          | 8.3                                           |
| Framework    | Laravel 11                                    |
| Database     | SQLite (swap in `.env` for MySQL/Postgres)    |
| Frontend     | Blade + Tailwind v4 + Vite (vanilla JS)       |
| API style    | REST (apiResource controller)                |

## Endpoints

All endpoints are under the `/api/couriers` prefix.

| Method | Path                  | Action           |
|--------|-----------------------|------------------|
| GET    | `/api/couriers`       | List + filter    |
| POST   | `/api/couriers`       | Create           |
| GET    | `/api/couriers/{id}`  | Detail           |
| PUT    | `/api/couriers/{id}`  | Update           |
| DELETE | `/api/couriers/{id}`  | Delete           |

The `/couriers` Blade page exposes the same CRUD via a small vanilla-JS UI that calls the JSON API.

## Schema

| Column          | Type             | Notes                                          |
|-----------------|------------------|------------------------------------------------|
| `id`            | bigint PK        | auto-increment                                  |
| `code`          | string(32) unique| internal code, e.g. `KRR001`                     |
| `name`          | string(120)      | full name (required)                             |
| `phone`         | string(32)       | phone number                                     |
| `email`         | string(120)      | email                                           |
| `address`       | text             | address                                         |
| `vehicle_type`  | string(32)       | motor / mobil / van / truck / etc               |
| `vehicle_plate` | string(32)       | vehicle plate                                    |
| `level`         | unsignedTinyInt  | 1-5 (1 = junior, 5 = senior) — **required**     |
| `status`        | string(16)       | active / inactive / suspended (default active)   |
| `joined_at`     | timestamp        | start date (may differ from `created_at`)        |
| `created_at`    | timestamp        | registration date                                |
| `updated_at`    | timestamp        |                                                  |

## License

MIT — do whatever you want.