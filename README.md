# Courier API — Laravel 13

[![Tests](https://github.com/ibam28/courier-api/actions/workflows/tests.yml/badge.svg)](https://github.com/ibam28/courier-api/actions/workflows/tests.yml)

REST API + admin UI for courier master data. Built with Laravel 13 + SQLite + Tailwind v4.

🌐 **Documentation languages:**
- 🇬🇧 [English](./docs/en/README.md) — default
- 🇮🇩 [Bahasa Indonesia](./docs/id/README.md)

---

## Repository structure

```text
courier-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/AuthController.php        ← /api/auth/{register,login,logout,me}
│   │   │   ├── Api/CourierController.php     ← REST CRUD (/api/couriers)
│   │   │   └── CourierPageController.php     ← Blade page (/couriers)
│   │   └── Requests/Auth/                    ← RegisterRequest, LoginRequest
│   ├── Models/{Courier,User}.php
│   └── Providers/AppServiceProvider.php      ← Gate 'manage-courier' (admin)
├── database/
│   ├── migrations/                           ← couriers, role column on users
│   ├── seeders/CourierSeeder.php             ← sample couriers
│   └── factories/{Courier,User}Factory.php
├── resources/views/couriers/                 ← main UI
├── routes/
│   ├── api.php                               ← /api/couriers + /api/auth/* (Sanctum)
│   └── web.php                               ← GET /couriers → Blade
├── tests/Feature/{Auth,CourierApi}Test.php   ← 35 tests, 106 assertions
└── .github/workflows/tests.yml               ← CI on every push/PR
```

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed --force
php artisan serve --host=127.0.0.1 --port=8000
```

Open in your browser:

- **UI**: http://127.0.0.1:8000/couriers
- **API root**: http://127.0.0.1:8000/api/couriers
- **Auth**: register/login at `/api/auth/*` to get a Sanctum token

## Testing

```bash
php artisan test           # 35 tests, 106 assertions
vendor/bin/pint --test     # 0 style issues
```

Tests cover CRUD, validation, duplicate-code protection, level filter, search, sort, pagination, 404, Sanctum auth (401 for unauth, 403 for staff writes, full register/login/logout/me flow). Each push and PR runs the same suite on GitHub Actions.

## Tech stack

| Layer        | Choice                                                   |
|--------------|----------------------------------------------------------|
| PHP          | 8.3                                                      |
| Framework    | Laravel 13                                               |
| Auth         | Laravel Sanctum (token-based, role-gated writes)         |
| Authorization| Gate `manage-courier` — admin only for create/update/delete |
| Database     | SQLite (swap in `.env` for MySQL/Postgres)               |
| Frontend     | Blade + Tailwind v4 + Vite (vanilla JS)                  |
| API style    | REST (apiResource controller) + JSON Form Requests       |
| CI           | GitHub Actions — PHPUnit + Pint on every push/PR         |

## Endpoints

All under the `/api` prefix. All courier endpoints require a Sanctum token; write operations additionally require `role=admin`.

| Method | Path                       | Auth     | Role      |
|--------|----------------------------|----------|-----------|
| POST   | `/api/auth/register`       | public   | —         |
| POST   | `/api/auth/login`          | public   | —         |
| POST   | `/api/auth/logout`         | Sanctum  | any       |
| GET    | `/api/auth/me`             | Sanctum  | any       |
| GET    | `/api/couriers`            | Sanctum  | any       |
| POST   | `/api/couriers`            | Sanctum  | **admin** |
| GET    | `/api/couriers/{id}`       | Sanctum  | any       |
| PUT    | `/api/couriers/{id}`       | Sanctum  | **admin** |
| DELETE | `/api/couriers/{id}`       | Sanctum  | **admin** |

### List query parameters

- `search` — search courier names by all entered terms
- `level` — comma-separated levels, e.g. `2,3`
- `sort` — `name` or `created_at`
- `order` — `asc` or `desc`
- `per_page` — 1–100 items per page
- `page` — page number

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
| `level`         | unsignedTinyInt  | 1-5 (1 = junior, 5 = senior) — required        |
| `status`        | string(16)       | active / inactive / suspended (default active)   |
| `joined_at`     | timestamp        | start date (may differ from `created_at`)        |
| `created_at`    | timestamp        | registration date                                |
| `updated_at`    | timestamp        |                                                  |

Users table additionally has `role` (`admin` or `staff`, default `staff`).

## License

MIT — do whatever you want.

Copyright © 2026 Bambang Saputra Jaya. Seluruh hak cipta dilindungi.