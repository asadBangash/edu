# EduX CodeIgniter → Laravel migration checklist

**Source (read-only):** `D:\laragon\www\edux`  
**Target:** `D:\laragon\www\edu-x` (`http://edu-x.test`)  
**Source DB:** `sms` (untouched)  
**Target DB:** `edu_x` (196 tables, full clone verified via `php artisan edux:verify-data`)

## Infrastructure

| Item | Status |
|------|--------|
| Laravel 13 app scaffold | Done |
| `.env` → `edu_x` MySQL | Done |
| Laragon vhost `edu-x.test` + `/backend` & `/uploads` aliases | Done |
| DB clone `sms` → `edu_x` | Done |
| Schema export `database/schema/edux-schema.sql` | Done |
| `edux:sync-database` / `edux:verify-data` commands | Done |
| Session-compatible staff auth (`admin` session) | Done |
| Student/parent auth (`student` session) | Partial (login only) |
| Landing `demo.html` → Blade `/` | Done |
| Staff login (`site/login`) | Done |
| User login (`site/userlogin`) | Done |
| Admin dashboard (`admin/admin/dashboard`) | Partial (sidebar + notifications) |
| Front CMS (`frontend`, `page/{slug}`) | Partial |
| RBAC (`RbacService`, `staff.privilege` middleware) | Done (foundation) |
| Module toggles (`ModuleService` / `permission_group`) | Done (foundation) |
| Admin sidebar (DB-driven menus) | Done (foundation) |
| `edux:ci-inventory` controller manifest | Done |

## CodeIgniter inventory

| Layer | Count |
|-------|------:|
| Controllers | 233 |
| Models (`application/models`) | ~154 |
| DB tables | 196 |
| Payment gateway controllers | 50+ |
| Languages / locale packs | 80+ |

## Controllers — migration status

Legend: **Done** = Laravel route + logic, **Partial** = started, **Pending** = not ported.

### Core / public (priority 1)

| CI controller | Laravel status |
|---------------|----------------|
| `Site` (login, userlogin, logout, …) | Partial |
| `Welcome` (front CMS) | Partial |
| `demo.html` | Done |
| `FrontTheme` / `Theme` | Pending |
| `Cron` | Pending |
| `Common` | Pending |

### Admin (`Admin_Controller`)

All under `application/controllers/admin/` plus root admin modules — **Pending** except `Admin::dashboard` → `DashboardController` (**Partial**).

Includes: Student, Staff, Feemaster, Exam, Transport, Library, Payroll, CMS, Online exam, Certificates, Reports, Settings, etc. (100+ controllers).

### Student panel (`Student_Controller`)

`application/controllers/user/*` — **Pending** (auth only).

### Role panels

Teacher, Parent, Accountant, Librarian controllers — **Pending**.

### Online admission + gateways

`onlineadmission/*`, `user/gateway/*`, `gateway_ins/*` — **Pending**.

### API / mobile

`App`, `Biometric`, `Webhooks` — **Pending**.

### Reports

`Report`, `Financereports`, `Attendencereports`, `Balancefees` — **Pending**.

## Laravel architecture mapping

| CodeIgniter | Laravel target |
|-------------|----------------|
| `MY_Controller` / `Admin_Controller` | `App\Http\Controllers\Controller` + middleware |
| `Auth` library | `StaffAuthService`, `StudentAuthService` |
| `Enc_lib` | `EncLibService` (bcrypt `password_verify`) |
| `Rbac` | Pending → middleware + `RolePermission` service |
| Models | `App\Models\*` (incremental Eloquent) |
| Views | `resources/views/**` Blade |
| `application/config/routes.php` | `routes/web.php`, `routes/edux.php` |
| Libraries (40+) | `App\Services\*` |
| Uploads | Laragon alias → original `uploads/` |
| Static assets | Laragon alias → original `backend/` |

## Suggested porting order

1. Layout: admin header/sidebar/footer (match CI `application/views/layout/*`)
2. RBAC + `module_lib`
3. Students, Staff, Classes, Sections, Sessions
4. Fees (`Studentfee`, `Feemaster`, gateways)
5. Attendance, Exams, Timetable
6. Front CMS (full `Welcome` theme `material_pink`)
7. Reports & exports (PDF/Excel)
8. Notifications, SMS, email
9. Online admission + payment gateways
10. Cron / queues

## Verification per module

For each ported module, compare against `http://edux.test` (CI):

- [ ] UI matches (Blade + same CSS)
- [ ] CRUD + validation
- [ ] Permissions
- [ ] DB reads/writes on `edu_x`
- [ ] File uploads
- [ ] Reports/print