# UserHub API Reference

The API follows REST conventions and responds with JSON envelopes that share the following structure:

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

- `success` indicates whether the call was successful.
- `error` contains `{ code, message, details }` when `success` is `false`.
- `meta.request_id` can be used for log correlation.

## Base URL

All endpoints are prefixed with `/api/v1`. Examples in this document assume a local environment at `http://localhost`.

```
Base URL: http://localhost/api/v1
```

## Authentication

The API uses JWT bearer tokens.

1. Obtain a token via **Login** or **Register**.
2. Include the token on protected requests:

```
Authorization: Bearer <token>
```

Tokens can be refreshed via `/auth/refresh` and invalidated via `/auth/logout`.

---

## Auth Endpoints

### POST `/auth/register`

Create a new account and receive an access token.

**Body (multipart/form-data or JSON):**

```json
{
  "username": "jdoe",
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123",
  "password_confirmation": "secret123",
  "phone": "+62-812-1234",
  "avatar": <file, optional>
}
```

**Success (201):**

```json
{
  "success": true,
  "data": {
    "token": "jwt-token",
    "user": { "...user resource..." }
  },
  "error": null,
  "pagination": null,
  "meta": { "...meta..." }
}
```

Validation failures return `422 VALIDATION_ERROR` with field errors in `error.details`.

---

### POST `/auth/login`

Authenticate with either username or email plus password.

**Body:**

```json
{
  "username": "jdoe", // or email e.g. "john@example.com"
  "password": "secret123"
}
```

**Success (200):** same envelope as `register` with `token` and `user`.

**Errors:**
- `401 INVALID_CREDENTIALS` when the username/email & password pair is invalid.

---

### POST `/auth/forgot-password`

Request a password reset link. Always returns success to avoid account enumeration.

**Body:**

```json
{ "email": "john@example.com" }
```

**Success (200):**

```json
{
  "success": true,
  "data": { "message": "If the email exists, a password reset link has been sent." },
  "error": null,
  "pagination": null,
  "meta": { "...meta..." }
}
```

---

### POST `/auth/reset-password`

Complete a password reset using the token sent via email.

**Body:**

```json
{
  "token": "reset-token",
  "email": "john@example.com",
  "password": "newSecret123",
  "password_confirmation": "newSecret123"
}
```

**Success (200):** Message confirming the password was reset.

**Errors:**
- `400 INVALID_TOKEN` if the token or email is invalid/expired.
- Validation errors return `422 VALIDATION_ERROR`.

---

### POST `/auth/logout` _(requires auth)_

Invalidates the current token. No body required.

**Success (200):** Message confirming logout.

---

### POST `/auth/refresh` _(requires auth)_

Refresh the current JWT and receive a new token plus the user resource.

**Success (200):**

```json
{
  "success": true,
  "data": {
    "token": "new-jwt",
    "user": { "...user resource..." }
  },
  "error": null,
  "pagination": null,
  "meta": { "...meta..." }
}
```

**Errors:** `401 TOKEN_REFRESH_FAILED` if the token cannot be refreshed.

---

## User Endpoints

### GET `/me` _(requires auth)_

Retrieve the authenticated user.

**Success (200):**

```json
{
  "success": true,
  "data": { "...user resource..." },
  "error": null,
  "pagination": null,
  "meta": { "...meta..." }
}
```

---

### PUT `/me` _(requires auth)_

Update the authenticated user's profile. Supports partial updates and optional avatar upload.

**Body (multipart/form-data or JSON):**

```json
{
  "username": "new-username",
  "email": "new-email@example.com",
  "name": "New Name",
  "phone": "+62-812-0000",
  "avatar": <file, optional>
}
```

**Success (200):** Returns the updated user resource.

**Errors:** Validation issues return `422 VALIDATION_ERROR`.

---

### GET `/users` _(requires auth)_

List users with optional pagination and search.

**Query Parameters:**

| Name | Type | Default | Notes |
|------|------|---------|-------|
| `page` | integer | `1` | Page number |
| `size` | integer | `20` | Page size (1-100) |
| `q` | string | – | Search term applied to name, email, username |

**Success (200):**

```json
{
  "success": true,
  "data": [
    { "...user resource..." }
  ],
  "error": null,
  "pagination": {
    "page": 1,
    "page_size": 20,
    "total": 42,
    "total_pages": 3
  },
  "meta": { "...meta..." }
}
```

---

### GET `/users/{id}` _(requires auth)_

Fetch a single user by ID.

**Success (200):** Returns the user resource.

**Errors:** `404 NOT_FOUND` when the user does not exist.

---

## Error Responses

- `401 UNAUTHENTICATED` when the bearer token is missing or invalid.
- `422 VALIDATION_ERROR` includes `error.details` keyed by field.
- `404 NOT_FOUND` when a resource is missing.
- `500 INTERNAL_SERVER_ERROR` includes exception details when `APP_DEBUG` is `true`.

Each error response follows the same envelope format with `success: false`.
