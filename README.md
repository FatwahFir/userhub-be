# UserHub Backend (Laravel 12 + PostgreSQL)

Laravel 12 API backend that fulfils the contract expected by the UserHub Flutter application. It exposes authentication, profile management, password reset, and user directory endpoints secured by JWT bearer tokens and returns responses wrapped in the standard envelope structure expected by the mobile client.

## Requirements

- PHP 8.3 with extensions: `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `curl`, `json`, `zip`, `gd`
- Composer 2.7+
- PostgreSQL 16
- Node.js 20+ (optional, only needed for asset builds)
- Mail driver credentials (Mailtrap / SMTP) for password reset notifications

## Quick Start

1. **Install dependencies**
   ```bash
   composer install
   npm install       # optional, only if you want to rebuild front-end assets
   ```

2. **Copy environment file & generate keys**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

3. **Configure `.env`**
   - Set `APP_URL=http://127.0.0.1:8000`
   - Point the database configuration to your PostgreSQL instance.
   - Provide mail credentials (e.g. Mailtrap) so password reset emails send.
   - Confirm `FILESYSTEM_DISK=public` and `FILESYSTEM_PUBLIC_URL=${APP_URL}/storage`.
   - Update SMTP settings, for example:
     ```dotenv
     MAIL_MAILER=smtp
     MAIL_HOST=sandbox.smtp.mailtrap.io
     MAIL_PORT=2525
     MAIL_USERNAME=your_mailtrap_username
     MAIL_PASSWORD=your_mailtrap_password
     MAIL_ENCRYPTION=tls
     MAIL_FROM_ADDRESS="no-reply@userhub.test"
     MAIL_FROM_NAME="UserHub"
     ```
     Replace the values with credentials from your mail provider (Mailtrap, Postmark, SES, etc.).

4. **Prepare the database and storage**
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```

5. **Run the development services**
   ```bash
   php artisan serve --host=127.0.0.1 --port=8000
   php artisan queue:work --queue=default --tries=3   # optional, only if you queue mail
   npm run dev                                       # optional, rebuild assets if you modify views
   ```

The API is now reachable at `http://127.0.0.1:8000/api/v1`. The seeded admin user is listed in [Seed Data](#seed-data).

### Running with Laravel Herd or Valet

If you use [Laravel Herd](https://herd.laravel.com/) or Valet, point the site to `public/` and ensure the PHP version is 8.3+. Queue workers can be managed through Supervisor or Laravel Horizon if desired.

## Seed Data

The database seeder creates a baseline administrator:

```
email: admin@example.com
username: admin
password: Password123!
```

The password reset link emails point to `/reset-password` (served from `resources/views/auth/reset-password.blade.php`), which posts back to `/api/auth/reset-password`.

## API Overview

All endpoints return JSON envelopes shaped as:

```json
{
  "success": true,
  "data": {},
  "error": null,
  "pagination": null,
  "meta": {
    "request_id": "uuid",
    "timestamp": "ISO-8601"
  }
}
```

### Authentication & Passwords

| Method | URI | Description |
| --- | --- | --- |
| POST | `/api/auth/register` | Register new user (optional avatar upload) |
| POST | `/api/auth/login` | Login with username or email + password |
| POST | `/api/auth/forgot-password` | Issue password reset email |
| POST | `/api/auth/reset-password` | Complete password reset flow |
| POST | `/api/auth/logout` | Invalidate current token |
| POST | `/api/auth/refresh` | Refresh token |

### Profile & Users (JWT required)

| Method | URI | Description |
| --- | --- | --- |
| GET | `/api/me` | Fetch authenticated user profile |
| PUT | `/api/me` | Update profile details and avatar |
| GET | `/api/users` | Paginated user directory (`page`, `size`, `q`) |
| GET | `/api/users/{id}` | User detail by id |

## Testing & Quality

- Run feature tests: `php artisan test`
- Optional static analysis: `./vendor/bin/phpstan analyse`
- Optional code style: `./vendor/bin/pint`

## File Storage

User avatars are stored on the `public` disk under `storage/app/public/avatars`. Ensure `php artisan storage:link` (or your hosting equivalent) is executed so generated URLs resolve correctly.

## Deployment Notes

- Set `APP_ENV=production`, `APP_DEBUG=false`, and configure logging appropriately.
- Generate and set `JWT_SECRET` per environment.
- Back the queue connection with Redis if you enable queued notifications.
- Consider storing avatars on an S3-compatible disk in production by updating `FILESYSTEM_DISK` and `FILESYSTEM_PUBLIC_URL`.
