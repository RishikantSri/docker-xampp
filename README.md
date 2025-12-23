# Docker Dev Kit (PHP 8.3 + Apache + MySQL + phpMyAdmin + Node)

This repo gives you a ready-to-run local development environment for Laravel / PHP projects using Docker Compose.

## Prerequisites (Mac)
1. Install Docker Desktop
2. Ensure Docker Desktop is running

## Quick start
```bash
cp .env.example .env   # optional, defaults exist
docker compose up -d --build
```

### Open
- Web: http://localhost
- phpMyAdmin: http://localhost:8081

## Where to put your projects
Put your code inside:
- `./projects/<your-project-name>`

It is mounted into the container as:
- `/var/www/html/<your-project-name>`

## Common commands
```bash
docker compose ps
docker compose logs -f
docker compose exec web bash
docker compose exec node bash
docker compose down
```

## Optional queue worker (for ./projects/saasapp)
Enable the worker profile:
```bash
docker compose --profile worker up -d
```

## Security notes (local dev)
- Never commit `.env`
- Do not reuse these example DB passwords in production
