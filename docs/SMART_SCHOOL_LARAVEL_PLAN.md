# Smart School → Laravel (edu-x)

**Reference source:** `E:\school management ssyte\smart_school_src` (v7.2.0)  
**Runtime CI tree:** `D:\laragon\www\edu-x\edux` (served via bridge)  
**Database:** `edu_x`

## Current implementation strategy

1. **Full product today** — All 236 CodeIgniter controllers run through `CodeIgniterBridgeController` + expanded routes in `routes/ci-bridge.php` and `routes/web.php`.
2. **Sync** — `php artisan edux:sync-smart-school` mirrors `application`, `backend`, `system` from `SMART_SCHOOL_SRC` (excludes `application/config/database.php`).
3. **Inventory** — `php artisan edux:ci-inventory` tracks which URIs still use the bridge vs native Laravel.
4. **Native port** — One module at a time into `app/Http/Controllers/...` per `config/edux-modules.php` phases.

## Commands

```bash
php artisan edux:sync-smart-school --dry-run
php artisan edux:sync-smart-school
php artisan edux:smoke-test
php artisan edux:ci-inventory
php artisan edux:verify-data
```

## Addon ZIPs

Install via **Admin → Addons** in the running app (copies into `edux/addons/`), then port to Laravel modules under Phase 9 of the main plan.