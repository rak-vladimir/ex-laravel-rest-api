# Minimal Laravel REST API example

Dockerized REST API with PostgreSQL, Mailpit, and GitHub Actions CI.

## Quick start

1. Copy `.env.example` to `.env` and adjust variables if needed:

   ```bash
   cp .env.example .env
   ```

2. Make entrypoint executable:

   ```bash
   chmod +x _docker/entrypoint.sh
   ```

3. Start environment:

   ```bash
   make up
   ```

4. Run migrations:
   ```bash
   make migrate
   ```
5. Open the app: http://localhost
6. Mailpit UI: http://localhost:8025

Docker configs are in _docker/.

## Makefile commands

The project has a **Makefile** that contains a set of convenient commands for managing the local environment via `docker compose`
and performing typical development tasks (building images, starting and stopping containers, viewing logs, executing
artisan/composer/npm commands, migrations, etc.).

To see a full list of available targets and a brief description of each, run:

```bash
make help
```

Examples of frequently used commands:

* make up — start containers;
* make down — stop containers;
* make build — build images;
* make logs — view logs;
* make shell — open a shell in the application container;
* make artisan migrate — run migrations.

## Authentication

The project uses **Laravel Sanctum** for authentication via **Bearer tokens** (Personal Access Tokens).
This allows protecting API routes and working with applications, mobile clients, or Postman.

### Authentication Endpoints

- **POST /api/register** — register a new user
  Request body:
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }
  ```
  Response (201 Created):
  ```json
  {
  "user": { ... },
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
  ```

- **POST /api/login** — user login
  Request body:
  ```json
    {
    "email": "john@example.com",
    "password": "password123"
    }
  ```
  Response:
  ```json
  {
  "user": { ... },
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
  ```

- **POST /api/logout** — logout (revoke current token)
  Requires header:

  `Authorization: Bearer <your_token>`

  Response (200):
  ```json
  { "message": "Logged out" }
  ```

- **GET /api/user** — get current user information
  Requires header:

  `Authorization: Bearer <your_token>`

  Response (200):
  ```json
    {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    ...
    }
  ```
