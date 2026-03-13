# API v0.1

Base path: `/api`

Authentication: Bearer token via Sanctum (`Authorization: Bearer <token>`).

## Auth

### POST `/auth/register`

Create user and return auth token.

Request body:

- `name` (required, string, max 120)
- `email` (required, email, unique)
- `password` (required, string, min 8, confirmed)
- `password_confirmation` (required)

Response `201`:

- `user`: `{ id, name, email }`
- `token`: `string`

### POST `/auth/login`

Authenticate and return auth token.

Request body:

- `email` (required, email)
- `password` (required, string)

Response `200`:

- `user`: `{ id, name, email }`
- `token`: `string`

On invalid credentials: `422`.

### GET `/auth/me`

Protected by `auth:sanctum`.

Response `200`:

- `user`: `{ id, name, email }`

Without token: `401`.

### POST `/auth/logout`

Protected by `auth:sanctum`.

Behavior: revokes current access token.

Response `200`:

- `ok`: `true`

Without token: `401`.

### POST `/auth/forgot-password`

Send password reset link.

Request body:

- `email` (required, email)

Response `200`:

- `ok`: `true`
- `status`: localized status message

### POST `/auth/reset-password`

Reset password from token.

Request body:

- `token` (required, string)
- `email` (required, email)
- `password` (required, string, min 8, confirmed)
- `password_confirmation` (required)

Response `200` on success:

- `ok`: `true`
- `status`: localized status message

Response `422` on failure:

- `ok`: `false`
- `message`: localized error message

After success: all existing Sanctum tokens for the user are revoked.

## Backoffice

### GET `/backoffice/ping`

Protected by:

- `auth:sanctum`
- `role:OPERATOR,ADMIN`

Responses:

- `200`: `{ "ok": true, "ping": "pong" }`
- `401`: unauthenticated
- `403`: authenticated but role not allowed (e.g. `USER`)

