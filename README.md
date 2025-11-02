# UserHub Backend (Laravel 12 + PostgreSQL)

Laravel 12 API backend that fulfils the contract expected by the UserHub Flutter application. It exposes authentication, profile management, password reset, and user directory endpoints secured by JWT bearer tokens and returns responses wrapped in the standard envelope structure expected by the mobile client.

## Requirements

- PHP 8.3 with extensions: `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `curl`, `json`, `zip`, `gd`
- Composer 2.7+
- PostgreSQL 16
- Node.js 20+ (optional, only needed for asset builds)
- Mail driver credentials (Mailtrap / SMTP) for password reset notifications

## Running Locally (No Docker)

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
   The generated `APP_KEY` and `JWT_SECRET` are written into `.env` automatically (`APP_KEY` should be prefixed with `base64:`).

3. **Configure `.env`**
   - Set `APP_URL=http://127.0.0.1:8000`.
   - Point the database configuration to your PostgreSQL instance.
   - Provide mail credentials so password reset emails send.
   - Confirm `FILESYSTEM_DISK=public` and `FILESYSTEM_PUBLIC_URL=${APP_URL}/storage`.
   - Example mail configuration:
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

4. **Prepare the database and storage**
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```

5. **Run the services**
   ```bash
   php artisan serve --host=127.0.0.1 --port=8000
   php artisan queue:work --queue=default --tries=3   # optional, only if you queue mail
   npm run dev                                       # optional, rebuild assets if you modify views
   ```

The API is now reachable at `http://127.0.0.1:8000/api/v1`. The seeded admin user is listed in [Seed Data](#seed-data).

## Running with Docker

Build and run the backend with Docker (Apache + PHP 8.3) and a PostgreSQL sidecar.

1. **Prepare Docker environment variables**
   ```bash
   cp docker/.env.example docker/.env
   ```
   Generate secrets and paste them into `docker/.env` before building:
   ```bash
   docker compose run --rm app php artisan key:generate --show
   docker compose run --rm app php artisan jwt:secret --show
   ```
   These commands require the database container; they will start it automatically if necessary. Ensure the resulting values include the `base64:` prefix for `APP_KEY`.

2. **Build the image**
   ```bash
   docker compose build app
   ```
   (Alternatively: `docker build -t userhub-be:latest .`)

3. **Start the stack**
   ```bash
   docker compose up app db
   ```
   The API is reachable at `http://localhost:8000/api/v1`. PostgreSQL is exposed on `localhost:5432` with the credentials defined in `docker/.env`.

4. **Run just the API container (external DB)**
   ```bash
   docker run -d \
     --name userhub-be \
     --env-file docker/.env \
     -p 8000:80 \
     userhub-be:latest
   ```
   Override `DB_HOST`, `DB_USERNAME`, and related variables in `docker/.env` to point at your production database.

**Helpful commands**
- `docker compose logs -f app` — tail application logs.
- `docker compose exec app php artisan migrate` — trigger migrations manually (`RUN_MIGRATIONS=true` auto-runs them on container start).
- `docker compose down -v` — stop containers and clear named volumes.

**Publishing**
```bash
docker buildx build --platform linux/amd64,linux/arm64 \
  -t docker.io/<username>/userhub-be:latest \
  --push .
```
Pull the image on your server and run the `docker run` command above with production-ready environment variables, or integrate the image into your CI/CD pipeline.

### Running with Laravel Herd or Valet

If you use [Laravel Herd](https://herd.laravel.com/) or Valet, point the site to `public/` and ensure the PHP version is 8.3+. Queue workers can be managed through Supervisor or Laravel Horizon if desired.

## Seed Data

The database seeder creates a baseline administrator:

```
email: admin@example.com
username: admin
password: Password123!
```

The password reset link emails point to `/reset-password` (served from `resources/views/auth/reset-password.blade.php`), which posts back to `/api/v1/auth/reset-password`.

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
| POST | `/api/v1/auth/register` | Register new user (optional avatar upload) |
| POST | `/api/v1/auth/login` | Login with username or email + password |
| POST | `/api/v1/auth/forgot-password` | Issue password reset email |
| POST | `/api/v1/auth/reset-password` | Complete password reset flow |
| POST | `/api/v1/auth/logout` | Invalidate current token |
| POST | `/api/v1/auth/refresh` | Refresh token |

### Profile & Users (JWT required)

| Method | URI | Description |
| --- | --- | --- |
| GET | `/api/v1/me` | Fetch authenticated user profile |
| PUT | `/api/v1/me` | Update profile details and avatar |
| GET | `/api/v1/users` | Paginated user directory (`page`, `size`, `q`) |
| GET | `/api/v1/users/{id}` | User detail by id |

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
