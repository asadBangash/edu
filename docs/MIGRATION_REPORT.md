# EduX migration report

**Date:** 2025-11-17  
**Product:** EduX — Smart School Management System  
**Powered by:** Global TechX Solutions  

## Summary

The CodeIgniter EduX installation (`D:\laragon\www\edux`, database `sms`) is a full Smart School–class application (**233 controllers**, **~154 models**, **196 tables**). A new **Laravel 13** application has been created at **`D:\laragon\www\edu-x`** with an independent database **`edu_x`** containing a verified full data clone.

The **original CodeIgniter project and `sms` database were not modified** (clone-only migration).

A complete line-by-line rewrite of every module in one pass is not feasible; this report documents what is **done**, what is **in progress**, and the **path to completion**.

## Original architecture (CodeIgniter)

- **Framework:** CodeIgniter 3 (`system/`, `application/`)
- **MVC:** `application/controllers`, `models`, `views`
- **Core:** `MY_Controller`, role-specific bases (`Admin_Controller`, `Student_Controller`, …)
- **Auth:** Custom `Auth` library; sessions `admin` (staff) and `student` (student/parent/roles)
- **Passwords:** Staff — `password_hash` via `Enc_lib`; users — legacy field comparison + bcrypt where present
- **RBAC:** `Rbac` library + `rolepermission` tables
- **Front:** `Welcome` + CMS tables + theme `material_pink`
- **Assets:** `backend/`, `uploads/`
- **Default route:** `welcome/index` (front); root `demo.html` for demo hub

## New architecture (Laravel)

- **Framework:** Laravel 13.42 (`composer.json`)
- **PHP:** 8.3
- **Config:** `config/edux.php` (branding)
- **Auth services:** `StaffAuthService`, `StudentAuthService` (CI-compatible session keys)
- **Middleware:** `staff.auth` → `EnsureStaffAuthenticated`
- **Routes:** `routes/web.php`, `routes/edux.php`
- **DB default:** `edu_x`; read-only source connection `sms_source` → `sms`
- **Artisan:** `edux:sync-database`, `edux:verify-data`
- **Schema reference:** `database/schema/edux-schema.sql`

## Database migration

| Item | Result |
|------|--------|
| Tables in `edu_x` | 196 |
| Data clone from `sms` | Completed |
| Row-count verification | **All tables match** (`php artisan edux:verify-data`) |
| Laravel migrations for all tables | Schema SQL exported; per-table migrations **pending** (use generator or incremental) |
| Password hashes | Preserved in clone; staff login uses `password_verify` |

## Modules identified vs migrated

| Area | Identified | Migrated |
|------|-----------:|----------:|
| Controllers | 233 | 4 (Demo, Site auth, Admin dashboard, Front welcome) |
| Models | ~154 | 5 base models |
| Staff authentication | 1 | 1 |
| Student/parent login | 1 | Partial |
| Admin dashboard | 1 | Partial UI |
| Front CMS | 1 large | Partial |
| Payment gateways | 50+ | 0 |
| Reports | 10+ | 0 |

See `docs/MIGRATION_CHECKLIST.md` for the full controller list and porting order.

## Routes implemented

| URL | Purpose |
|-----|---------|
| `/` | Demo landing (from `demo.html`) |
| `/site/login` | Staff/admin login |
| `/site/userlogin` | Student/parent login |
| `/admin/admin/dashboard` | Staff dashboard (authenticated) |
| `/frontend`, `/welcome` | Front CMS home |
| `/page/{slug}` | CMS pages |

## Laragon configuration

- **Project path:** `D:\laragon\www\edu-x\public`
- **URL:** `http://edu-x.test`
- **Vhost:** `D:\laragon\etc\apache2\sites-enabled\auto.edu-x.test.conf`
- **Assets:** Apache `Alias` for `/backend` and `/uploads` → original CI tree (no duplicate copies)

> If `edu-x.test` does not resolve, restart Laragon (Apache) or add `127.0.0.1 edu-x.test` to the hosts file.

## Testing performed

- [x] Composer install + `APP_KEY` generated
- [x] MySQL connection to `edu_x`
- [x] `edux:verify-data` — all row counts match `sms`
- [x] Route registration (`php artisan route:list`)
- [ ] Full browser regression vs CodeIgniter (requires Apache restart + manual QA)
- [ ] Every module functional test — **not complete**

## Local run instructions

```bash
cd D:\laragon\www\edu-x
composer install
cp .env.example .env   # if needed; configure DB as in current .env
php artisan key:generate

# Fresh DB clone from CodeIgniter (optional)
php artisan edux:sync-database --fresh

# Verify data integrity
php artisan edux:verify-data

# Dev server (alternative to Laragon vhost)
php artisan serve
```

Open **http://edu-x.test/** for the demo hub, then use role links to log in (demo password **Demo@123** as in `demo.html`).

## Outstanding issues / limitations

1. **Most admin/student modules** are not yet ported; only core entry points work on Laravel.
2. **Admin UI** uses a simplified layout; full sidebar/menus/charts from CI dashboard not yet migrated.
3. **Front CMS** does not yet load the `material_pink` theme and all `Welcome` actions (admission, exam result, etc.).
4. **RBAC**, **captcha**, **2FA (google_authenticator)**, **cron**, **API (App)**, and **payment gateways** are not implemented in Laravel yet.
5. **196-table Laravel migrations** are represented by schema SQL + clone command; generating individual migration files is still required for greenfield installs without mysqldump.
6. User requested path `C:\laragon\www\edu-x`; project is on **`D:\laragon\www\edu-x`** (same Laragon instance as `edux`).

## Next steps (recommended)

1. Port `application/views/layout/*` to Blade and wire sidebar from `sidebarmenu` tables.
2. Implement `Rbac` middleware and `ModuleService`.
3. Port high-traffic modules: Student, Staff, Feemaster, Stuattendence, Exam.
4. Use `database/schema/edux-schema.sql` + `edux:sync-database` for new environments; add `kitloong/laravel-migrations-generator` to emit migrations from `edu_x` if required.
5. Module-by-module QA against the CodeIgniter instance.

---

**Original CI URL:** typically `http://edux.test` (unchanged)  
**Laravel URL:** `http://edu-x.test`