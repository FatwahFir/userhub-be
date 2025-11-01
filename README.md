# UserHub Backend (Laravel 12 + PostgreSQL)

Laravel 12 API backend that fulfils the contract expected by the UserHub Flutter application. It exposes authentication, profile management, password reset, and user directory endpoints secured by JWT bearer tokens and returns responses wrapped in the standard envelope structure expected by the mobile client.

## Requirements

- PHP 8.3 with extensions: `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `curl`, `json`, `zip`, `gd`
- Composer 2.7+
- PostgreSQL 16
- Node.js 20+ (optional, only needed for asset builds)
- Mail driver credentials (Mailtrap / SMTP) for password reset notifications

## Initial Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

Update `.env` with database, mail, and filesystem credentials. Default connection is PostgreSQL; adjust values if required.

Run database migrations, seed demo data, and expose the storage symlink so avatars resolve correctly:

```bash
php artisan migrate --seed
php artisan storage:link
```

Finally, start the API server:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

The Flutter client should target `http://127.0.0.1:8000/api` during local development.

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

Default admin seed credentials:

```
email: admin@example.com
username: admin
password: Password123!
```

The password reset link emails point to `/reset-password` which renders a lightweight HTML form posting back to `/api/auth/reset-password`.

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
