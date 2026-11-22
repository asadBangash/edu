# EduX — Smart School on Laravel

Laravel 13 application that runs **Smart School** (CodeIgniter) through a HTTP bridge, with a gradual path to native Laravel modules.

## Requirements

- PHP 8.3+ (extensions: `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `curl`, `gd`, `zip`)
- Composer 2.x
- MySQL 8.x (database: `edu_x`)
- Node.js 20+ (optional, for Vite assets if you change front-end)

## Local development (Laragon)

1. Clone the repo into your web root (e.g. `www/edu-x`).
2. Set `SMART_SCHOOL_SRC` in `.env` to your licensed Smart School folder (read-only; never committed).
3. Copy `.env.example` to `.env` and set `APP_URL`, database credentials, and `APP_KEY` (`php artisan key:generate`).
4. `composer install`, then `php artisan edux:materialize-runtime` (copies CI into `storage/`, applies Laravel patches).
5. `composer install --working-dir=storage/app/smart-school-runtime/application/third_party/omnipay`
6. `php artisan migrate` (if using Laravel migrations) and import/seed your Smart School schema into `edu_x`.
7. Point the virtual host **document root** to `public/` (not the project root).
8. `php artisan edux:doctor` and `php artisan edux:smoke-test` — all checks should pass.

Default staff login matches Smart School demo data if seeded: adjust per your `staff` table.

## Artisan commands

| Command | Purpose |
|---------|---------|
| `php artisan edux:materialize-runtime` | Build CI runtime under `storage/` from `SMART_SCHOOL_SRC` |
| `php artisan edux:doctor` | Check runtime, bridge, logos; use `--fix` to seed assets |
| `php artisan edux:smoke-test` | HTTP smoke tests for bridge and admin |
| `php artisan edux:verify-data` | Data integrity checks |
| `php artisan edux:sync-smart-school` | Sync CI tree from `SMART_SCHOOL_SRC` (excludes EduX patches) |
| `php artisan edux:ci-inventory` | Controller/module inventory |
| `php artisan edux:seed-pakistan-demo` | Optional demo seed |

## Documentation

- [Deployment (production)](docs/DEPLOYMENT.md)
- [Migration checklist](docs/MIGRATION_CHECKLIST.md)
- [Laravel port plan](docs/SMART_SCHOOL_LARAVEL_PLAN.md)

## License

Proprietary — Smart School (Codecanyon) core materialized under `storage/` is subject to your purchase license. Do not redistribute it without compliance with that license.