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
