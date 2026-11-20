# EduX QA report (automated + spot checks)

**Project:** `edu-x.test` (Laravel shell + CodeIgniter modules on `edu_x` DB)  
**Last automated run:** `php artisan edux:smoke-test`  
**Output:** `storage/app/edux-smoke-report.json`

## Scope (honest limits)

A manual click-through of **every screen, modal, payment gateway, and report** (233 CI controllers) would take weeks. This project uses:

1. **Automated HTTP smoke tests** — critical routes must return **200** for Super Admin.
2. **CodeIgniter bridge** — admin modules run the original Smart School UI/logic.
3. **Spot checks** — login, dashboard, student list, session/RBAC fixes.

**Not fully automated without you:** every Bootstrap modal, DataTables AJAX, file upload, PDF/Excel export, SMS/email send, and 50+ payment gateways (need real API keys).

## How to re-run tests

```bash
cd D:\laragon\www\edu-x
php artisan edux:smoke-test
php artisan edux:verify-data          # DB clone integrity
php artisan edux:seed-pakistan-demo   # Peshawar demo + charts
```

## Fixes applied during QA

| Issue | Fix |
|-------|-----|
| Dashboard 500 after login | CodeIgniter `Rbac` + session bridge: `roles` null in `ci_session` |
| CSS / charts | HTTPS `base_url`, local Chart.js, demo seed |
| Super Admin RBAC crash | Patched `Rbac.php` / `Customlib::getStaffRole()` for PHP 8 |

## Manual checklist (recommended per release)

Use Super Admin (`superadmin@gmail.com` / `Demo@123`) and tick modules you care about:

- [ ] **Auth:** staff login, logout, user login, role demo buttons  
- [ ] **Student:** list, admission form, edit, disable, delete, search  
- [ ] **Fees:** collect fees, master, discount, receipt print  
- [ ] **Attendance:** student + staff, approve leave  
- [ ] **Exams:** schedule, marks, admit card print  
- [ ] **HR:** staff CRUD, payroll, apply leave  
- [ ] **Modals:** open/close add forms (Student, Staff, Income, Expense) — Escape / X / Save  
- [ ] **Settings:** General, Session, Roles, Payment methods (no live charge)  
- [ ] **Front CMS:** `/frontend` pages  
- [ ] **Reports:** open each report menu (export if used in production)

## Regression command (extend me)

Add failing URLs to `EduxSmokeTestCommand::$adminCases` as you port or fix modules.