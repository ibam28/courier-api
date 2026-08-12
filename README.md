# Courier API — Laravel 11

REST API sederhana untuk master data kurir, dibangun dengan Laravel 11 + SQLite.

## Stack

- PHP 8.3
- Laravel 11
- SQLite (default; ganti di `.env` untuk MySQL/Postgres)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8000
```

API siap di `http://127.0.0.1:8000/api/couriers`.

## Skema tabel `couriers`

| Kolom           | Tipe              | Keterangan                                     |
|-----------------|-------------------|------------------------------------------------|
| `id`            | bigint (PK)       | auto-increment                                 |
| `code`          | string(32) unik   | kode internal kurir (mis. KRR001)               |
| `name`          | string(120)       | nama lengkap kurir (wajib)                     |
| `phone`         | string(32)        | nomor telepon                                  |
| `email`         | string(120)       | email                                          |
| `address`       | text              | alamat                                         |
| `vehicle_type`  | string(32)        | motor / mobil / van / truck / etc              |
| `vehicle_plate` | string(32)        | plat nomor kendaraan                           |
| `level`         | unsignedTinyInt   | 1-5 (1 = junior, 5 = senior) — **wajib**       |
| `status`        | string(16)        | active / inactive / suspended (default active)  |
| `joined_at`     | timestamp         | tanggal mulai aktif (bisa beda dari created_at)|
| `created_at`    | timestamp         | tanggal didaftarkan                             |
| `updated_at`    | timestamp         |                                                  |

## Endpoint

Semua endpoint berada di bawah prefix `/api/couriers`.

| Method | Path                | Aksi            |
|--------|---------------------|-----------------|
| GET    | `/api/couriers`     | List + filter   |
| POST   | `/api/couriers`     | Create          |
| GET    | `/api/couriers/{id}`| Detail          |
| PUT    | `/api/couriers/{id}`| Update          |
| DELETE | `/api/couriers/{id}`| Hapus           |

### Query string untuk `GET /api/couriers`

| Param      | Default    | Keterangan                                                                 |
|------------|------------|----------------------------------------------------------------------------|
| `search`   | -          | Pencarian nama. Multi-token via spasi (semua token harus match): `budi+agung` cocok ke `Budiono Hadi Agung`. |
| `level`    | -          | Daftar level dipisah koma: `level=2,3` → hanya kurir level 2 atau 3.        |
| `sort`     | `name`     | `name` atau `created_at`.                                                   |
| `order`    | `asc`      | `asc` atau `desc`.                                                          |
| `per_page` | 15         | Item per halaman (1-100).                                                   |
| `page`     | 1          | Nomor halaman.                                                              |

### Contoh

```bash
# List default (sort by name asc, pagination 15)
curl http://127.0.0.1:8000/api/couriers

# Search "budi agung" -> match 'Budiono Hadi Agung'
curl "http://127.0.0.1:8000/api/couriers?search=budi+agung"

# Hanya level 2 atau 3, urut dari yang paling baru didaftarkan
curl "http://127.0.0.1:8000/api/couriers?level=2,3&sort=created_at&order=desc"

# Pagination: 5 per halaman, halaman 2
curl "http://127.0.0.1:8000/api/couriers?per_page=5&page=2"

# Create
curl -X POST http://127.0.0.1:8000/api/couriers \
  -H "Content-Type: application/json" \
  -d '{"name":"Joko Anwar","level":3,"phone":"08123","code":"KRR010"}'

# Update
curl -X PUT http://127.0.0.1:8000/api/couriers/1 \
  -H "Content-Type: application/json" \
  -d '{"name":"Joko Anwar","level":4}'

# Delete
curl -X DELETE http://127.0.0.1:8000/api/couriers/1
```

## Validasi

`POST` dan `PUT` divalidasi:

- `name` wajib, string, max 120
- `level` wajib, integer 1-5
- `code` (opsional) harus unik, max 32
- `email` (opsional) format email valid
- `status` (opsional) salah satu dari `active` / `inactive` / `suspended`

Response 422 jika gagal:

```json
{
  "message": "Validation failed",
  "errors": {
    "level": ["The level field must be between 1 and 5."]
  }
}
```

## Response

- `GET /api/couriers` mengembalikan Laravel paginator standar (Laravel auto-generates `links`, `meta`, `next_page_url`, `prev_page_url`).
- `GET /api/couriers/{id}` mengembalikan `{ "data": { ...courier } }`.
- `POST/PUT` mengembalikan `{ "message": "...", "data": { ...courier } }`.
- `DELETE` mengembalikan `{ "message": "Courier deleted", "id": <id>, "still_in_db": "no" }`.