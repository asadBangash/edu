# Production deployment (GitHub → live server)

Goal: production should behave like local **edu-x.test** — same URLs for admin, student login, frontend CMS, static theme, and uploads.

## 1. Push from your machine

The repo is intended to be: `https://github.com/asadBangash/edu`

- `.env` is **not** in git; copy `.env.example` on the server.
- Run `composer install` on the server (do not commit `vendor/`).

## 2. Server stack

- **PHP** 8.3+ with FPM (recommended) or Apache `mod_php`
- **MySQL** 8.x
- **Nginx** or **Apache**
- **Composer** on the server (or deploy `vendor` from CI — not recommended)
- **SSL** certificate (Let's Encrypt)

## 3. Clone on the server

```bash
cd /var/www
git clone https://github.com/asadBangash/edu.git edu-x
cd edu-x
```

## 3b. Smart School runtime — not in Git

The CodeIgniter tree is **not stored in this repository** (size, licensing). Do **not** keep an `edux/` folder in the project root.

1. Set `SMART_SCHOOL_SRC` in `.env` to your licensed package path (read-only).
2. `php artisan edux:materialize-runtime` — copies CI into `storage/app/smart-school-runtime` and applies committed patches from `resources/smart-school/patches/`.
3. `composer install --no-dev --working-dir=storage/app/smart-school-runtime/application/third_party/omnipay`
4. `php artisan edux:seed-assets` (branding / login images into `storage/app/school-uploads`).

Writable uploads live in **`storage/app/school-uploads/`** (junction-linked into the runtime). Back up this folder with your database.

## 4. Environment file

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` for production:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=edu_x
DB_USERNAME=your_user
DB_PASSWORD=your_password

SESSION_DRIVER=file
SESSION_LIFETIME=7200

# Optional: path to vanilla Smart School source for sync command only
# SMART_SCHOOL_SRC=/opt/smart_school_src
```

Never set `APP_DEBUG=true` on a public server.

## 5. Dependencies and Laravel bootstrap

```bash
composer install --no-dev --optimize-autoloader
php artisan edux:materialize-runtime
composer install --no-dev --working-dir=storage/app/smart-school-runtime/application/third_party/omnipay
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Create writable directories:

```bash
mkdir -p storage/framework/ci-sessions
chmod -R ug+rwx storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

(Replace `www-data` with your PHP-FPM user.)

## 6. Database

1. Create database `edu_x` and a MySQL user with full rights on it.
2. Import your Smart School / local dump (same schema as local `edu_x`).
3. Copy **`edux/uploads/`** from your local machine to the server (not stored in Git — logos, certificates, student images).
4. If you use Laravel migrations in addition, run `php artisan migrate --force`.

The CodeIgniter bridge reads the same MySQL database configured in `DB_*` and in `edux/application/config/database.php` (EduX-patched; do not overwrite with vanilla sync).

## 7. Web server document root

**Document root must be `public/`**, not the project root.

### Nginx example

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/edu-x/public;
    index index.php;

  # SSL directives here ...

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Smart School backend assets (local Laragon used an alias — mirror on server)
    location ^~ /backend/ {
        alias /var/www/edu-x/edux/backend/;
    }

    location ^~ /uploads/ {
        alias /var/www/edu-x/edux/uploads/;
    }

    location = /theme.css {
        alias /var/www/edu-x/edux/backend/theme_css/custom.css;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }
}
```

Adjust paths and PHP socket version.

### Apache example

```apache
<VirtualHost *:443>
    ServerName your-domain.com
    DocumentRoot /var/www/edu-x/public

    Alias /backend /var/www/edu-x/edux/backend
    Alias /uploads /var/www/edu-x/edux/uploads
    Alias /theme.css /var/www/edu-x/edux/backend/theme_css/custom.css

    <Directory /var/www/edu-x/public>
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /var/www/edu-x/edux/backend>
        Require all granted
    </Directory>
</VirtualHost>
```

`public/.htaccess` handles Laravel routing; aliases cover what Laragon mapped outside `public/`.

### Symlink alternative (shared hosting)

If aliases are not allowed:

```bash
cd /var/www/edu-x/public
ln -s ../edux/backend backend
ln -s ../edux/uploads uploads
```

Theme CSS may need a small route or copy — prefer server alias for `/theme.css` if possible.

## 8. CodeIgniter session path (bridge)

The bridge sets `EDUX_CI_SESSION_PATH` to Laravel's `storage/framework/ci-sessions`. That directory must exist and be writable (step 5).

After deploy, log out once and log in again so admin session and RBAC load correctly.

## 9. Cron (Smart School)

Smart School expects a cron URL. Route it through Laravel (see `routes/ci-bridge.php`). Example crontab:

```cron
* * * * * curl -s https://your-domain.com/cron/index >/dev/null 2>&1
```

Or use Laravel scheduler if you wrap it in `routes/console.php`.

## 10. Post-deploy verification

```bash
php artisan edux:smoke-test
```

Manually check:

- `https://your-domain.com/site/login` — staff login
- `https://your-domain.com/admin/admin/dashboard` — dashboard, sidebar, charts
- `https://your-domain.com/frontend` — public CMS
- `https://your-domain.com/backend/...` — CSS/JS load (no 404)
- Student login URL if enabled

## 11. Updating production

```bash
cd /var/www/edu-x
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan edux:smoke-test
```

**Do not** run `edux:sync-smart-school` on production unless you have set `SMART_SCHOOL_SRC` and understand it will not overwrite EduX-patched files (see command exclude list).

## 12. Security checklist

- `APP_DEBUG=false`, strong `APP_KEY`
- Restrict file permissions; block direct HTTP access to `edux/application/`, `.env`, `storage/` except via Laravel
- HTTPS only; secure cookies in production (`SESSION_SECURE_COOKIE=true` behind HTTPS)
- Regular backups of `edu_x` and `edux/uploads/`

## 13. Parity with local Laragon

| Local (Laragon) | Production |
|-----------------|------------|
| `https://edu-x.test` | `APP_URL=https://your-domain.com` |
| Document root `public/` | Same |
| Aliases `/backend`, `/uploads`, `/theme.css` | Nginx `alias` or Apache `Alias` |
| MySQL `edu_x` | Same schema + data |
| `storage/framework/ci-sessions` | Created + writable |

If menus or RBAC are empty after deploy, clear browser cookies, hit `/site/logout`, log in again, and confirm `edux/application/config/config.php` still has EduX bridge settings (not overwritten by vanilla CI).