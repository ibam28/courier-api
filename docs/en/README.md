# English Documentation

> Default language for this project. See also: [Bahasa Indonesia](../id/README.md).

## Table of contents

- [Overview](#overview)
- [Quick start](#quick-start)
- [Running in your browser](#running-in-your-browser)
- [Database schema](#database-schema)
- [REST API reference](#rest-api-reference)
- [Web UI reference](#web-ui-reference)
- [Validation rules](#validation-rules)
- [Screenshots](#screenshots)

---

## Overview

Courier API is a Laravel 11 project that exposes a full CRUD for a `couriers` master-data table through both:

1. A JSON REST API under `/api/couriers` (apiResource controller).
2. A small Blade + Tailwind UI at `/couriers` that consumes the same JSON API via vanilla JavaScript.

The point is to keep the server thin and the API as the single source of truth — the UI is just a thin client.

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

- UI: http://127.0.0.1:8000/couriers
- API root: http://127.0.0.1:8000/api/couriers

## Running in your browser

The default serve binds to `127.0.0.1`. To run on this host (`alsa`) and reach it from your **local browser**, two options:

### A. SSH port-forward (recommended — safest, no firewall changes)

From your local machine:

```bash
ssh -L 8000:127.0.0.1:8000 alsa@<host>
```

Then open http://127.0.0.1:8000/couriers on your local browser.

### B. Expose on the LAN (requires opening ufw)

```bash
php artisan serve --host=0.0.0.0 --port=8000
sudo ufw allow 8000/tcp
```

Then on any machine on the same LAN, open http://<host-ip>:8000/couriers.

> This host (`alsa`) runs `ufw` with policy DROP on INPUT. You must open the port explicitly if you bind to `0.0.0.0`.

## Database schema

| Column          | Type             | Notes                                          |
|-----------------|------------------|------------------------------------------------|
| `id`            | bigint PK        | auto-increment                                  |
| `code`          | string(32) unique| internal code, e.g. `KRR001`                     |
| `name`          | string(120)      | full name (**required**)                         |
| `phone`         | string(32)       | phone number                                     |
| `email`         | string(120)      | email                                            |
| `address`       | text             | address                                          |
| `vehicle_type`  | string(32)       | motor / mobil / van / truck / etc                |
| `vehicle_plate` | string(32)       | vehicle plate                                    |
| `level`         | unsignedTinyInt  | 1-5 (1 = junior, 5 = senior) — **required**      |
| `status`        | string(16)       | active / inactive / suspended (default `active`) |
| `joined_at`     | timestamp        | start date                                       |
| `created_at`    | timestamp        | registration date                                |
| `updated_at`    | timestamp        |                                                  |

Indexes: `name`, `level`, `status`, plus a unique index on `code`.

## REST API reference

Base URL: `/api/couriers`

### `GET /api/couriers` — List with filtering

#### Query parameters

| Param      | Default  | Allowed values                          | Notes                                                                  |
|------------|----------|-----------------------------------------|------------------------------------------------------------------------|
| `search`   | —        | string (≤120 chars)                     | Multi-token via spaces: `budi+agung` matches `Budiono Hadi Agung`. Every token must be a substring of `name`. |
| `level`    | —        | comma-separated ints (1-5)              | `level=2,3` returns couriers with level 2 OR 3.                        |
| `sort`     | `name`   | `name` \| `created_at`                  | Default is `name` ascending.                                            |
| `order`    | `asc`    | `asc` \| `desc`                         |                                                                       |
| `per_page` | 15       | int 1-100                               | Items per page.                                                         |
| `page`     | 1        | int ≥ 1                                 | Page number.                                                            |

#### Response — `200 OK`

Standard Laravel paginator:

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "code": "KRR001",
      "name": "Budiono Hadi Agung",
      "phone": "081111",
      "email": "budi@example.com",
      "address": null,
      "vehicle_type": "motor",
      "vehicle_plate": "B 1234 ABC",
      "level": 2,
      "status": "active",
      "joined_at": "2024-01-15T00:00:00.000000Z",
      "created_at": "2026-08-12T02:24:47.000000Z",
      "updated_at": "2026-08-12T02:24:47.000000Z"
    }
  ],
  "first_page_url": "...",
  "from": 1,
  "last_page": 3,
  "last_page_url": "...",
  "links": [...],
  "next_page_url": "...",
  "path": "...",
  "per_page": 15,
  "prev_page_url": null,
  "to": 8,
  "total": 22
}
```

#### Examples

```bash
# Default (sorted by name asc)
curl http://127.0.0.1:8000/api/couriers

# Search 'budi agung' — every token must be a substring of name
curl "http://127.0.0.1:8000/api/couriers?search=budi+agung"

# Filter level 2 or 3
curl "http://127.0.0.1:8000/api/couriers?level=2,3"

# Sort by registration date, newest first
curl "http://127.0.0.1:8000/api/couriers?sort=created_at&order=desc"

# Combine filter + search + sort
curl "http://127.0.0.1:8000/api/couriers?level=2,3&search=budi&sort=name"

# Pagination — page 2 with 5 per page
curl "http://127.0.0.1:8000/api/couriers?per_page=5&page=2"
```

### `POST /api/couriers` — Create

#### Body

| Field           | Type    | Required | Constraints                            |
|-----------------|---------|----------|----------------------------------------|
| `name`          | string  | yes      | max 120                                |
| `level`         | int     | yes      | 1-5                                    |
| `code`          | string  | no       | max 32, must be unique                 |
| `phone`         | string  | no       | max 32                                 |
| `email`         | string  | no       | valid email, max 120                   |
| `address`       | string  | no       | max 1000                               |
| `vehicle_type`  | string  | no       | max 32                                 |
| `vehicle_plate` | string  | no       | max 32                                 |
| `status`        | string  | no       | one of: `active`, `inactive`, `suspended` (default `active`) |
| `joined_at`     | date    | no       | ISO 8601                               |

#### Response — `201 Created`

```json
{
  "message": "Courier created",
  "data": { ...the new row... }
}
```

#### Example

```bash
curl -X POST http://127.0.0.1:8000/api/couriers \
  -H "Content-Type: application/json" \
  -d '{"name":"Joko Anwar","level":3,"code":"KRR010","phone":"08123","email":"joko@example.com"}'
```

### `GET /api/couriers/{id}` — Read one

```bash
curl http://127.0.0.1:8000/api/couriers/1
```

Response — `200 OK`:

```json
{ "data": { "id": 1, "name": "...", ... } }
```

If the id does not exist → `404` with a message.

### `PUT /api/couriers/{id}` — Update

Same body schema as `POST` (everything stays optional, except `name` and `level` are still required). The `code` unique rule ignores the row's own id so re-saving the same code is fine.

```bash
curl -X PUT http://127.0.0.1:8000/api/couriers/1 \
  -H "Content-Type: application/json" \
  -d '{"name":"Joko Anwar","level":4,"status":"inactive"}'
```

Response — `200 OK`:

```json
{ "message": "Courier updated", "data": { ...the updated row... } }
```

### `DELETE /api/couriers/{id}` — Delete

```bash
curl -X DELETE http://127.0.0.1:8000/api/couriers/1
```

Response — `200 OK`:

```json
{
  "message": "Courier deleted",
  "id": 1,
  "still_in_db": "no"
}
```

After this, `GET /api/couriers/1` returns `404`.

## Web UI reference

Open http://127.0.0.1:8000/couriers.

The page is a single Blade view that ships with **8 sample couriers** (from `CourierSeeder`). It exposes:

- **Search box** — same logic as the API `search` param (multi-token, all must match).
- **Level filter** — `All levels`, `Level 1-5`, or compound `Level 2 or 3`, `Level 1, 2, or 3`.
- **Sort by** — `Name` (default) or `Date registered`.
- **Order** — Ascending / Descending.
- **Apply / Reset** buttons.
- **+ New Courier** — opens a modal form to create.
- **Edit / Delete** buttons per row.
- **Pagination** — `‹ Previous` / page indicator / `Next ›`.

All operations call the same REST endpoints; the page never submits a full HTML form.

## Validation rules

For `POST` and `PUT`:

| Field           | Rule                                                         |
|-----------------|--------------------------------------------------------------|
| `name`          | `required`, `string`, `max:120`                              |
| `level`         | `required`, `integer`, `between:1,5`                          |
| `code`          | `nullable`, `string`, `max:32`, `unique:couriers,code`       |
| `phone`         | `nullable`, `string`, `max:32`                               |
| `email`         | `nullable`, `email`, `max:120`                               |
| `address`       | `nullable`, `string`, `max:1000`                             |
| `vehicle_type`  | `nullable`, `string`, `max:32`                               |
| `vehicle_plate` | `nullable`, `string`, `max:32`                               |
| `status`        | `nullable`, `in:active,inactive,suspended`                   |
| `joined_at`     | `nullable`, `date`                                           |

Failed validation → `422`:

```json
{
  "message": "Validation failed",
  "errors": {
    "level": ["The level field must be between 1 and 5."]
  }
}
```

For query params on `GET /api/couriers`:

| Param      | Rule                                  |
|------------|---------------------------------------|
| `search`   | `nullable`, `string`, `max:120`       |
| `level`    | `nullable`, `string`, `max:32`        |
| `sort`     | `nullable`, `in:name,created_at`      |
| `order`    | `nullable`, `in:asc,desc`             |
| `per_page` | `nullable`, `integer`, `1..100`       |
| `page`     | `nullable`, `integer`, `min:1`        |

## Screenshots

Visual references captured from the running app.

<!-- Screenshots will be added here once you provide the files. Example below — uncomment and update paths after you drop the files into ./screenshots/ -->

| What                                                      | File                                          |
|-----------------------------------------------------------|-----------------------------------------------|
| Courier list page — default sort                          | `screenshots/couriers-list.png`               |
| Filtered search — `search=budi+agung`                     | `screenshots/couriers-search.png`             |
| Filtered by level 2 or 3                                  | `screenshots/couriers-level-filter.png`       |
| Sort by `created_at desc`                                 | `screenshots/couriers-sort-by-date.png`       |
| Create modal                                              | `screenshots/couriers-create-modal.png`       |
| Edit modal prefilled                                      | `screenshots/couriers-edit-modal.png`         |
| Validation error inside the form                          | `screenshots/couriers-validation-error.png`   |