# Data Model v0.1

## Users

Table: `users`

- `id` (bigint, primary key)
- `name` (string)
- `email` (string, unique)
- `email_verified_at` (timestamp, nullable)
- `password` (string, hashed cast)
- `role` (string(20), indexed, default `USER`)
- `remember_token` (string, nullable)
- `created_at` / `updated_at` (timestamps)

### Role values (v0.1)

- `USER`
- `OPERATOR`
- `ADMIN`

Application enum: `App\UserRole` (backed enum `string`).

Model casts (`App\Models\User`)

- `email_verified_at` => `datetime`
- `password` => `hashed`
- `role` => `App\UserRole`

## Auth & Tokens

Authentication uses Laravel Sanctum personal access tokens.

Table: `personal_access_tokens`

- Token created on `register` and `login`
- Current token revoked on `logout`
- All tokens revoked after successful password reset

