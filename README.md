````md
# Docker Dev Kit (PHP 8.3 + Apache + MySQL + phpMyAdmin + Node)

This repository provides a ready-to-run local development environment for Laravel / PHP projects using Docker Compose.

---

## Services and ports
- **web** (PHP + Apache): http://localhost (also supports ports: `8000`, `8001` if configured via vhosts)
- **db** (MySQL): `127.0.0.1:3306`
- **phpMyAdmin**: http://localhost:8081
- **node** (Vue/Vite helper): http://localhost:3000

---

## Mac users

### Prerequisites (Mac)
1. Install Docker Desktop
2. Ensure Docker Desktop is running
3. (Optional) Install Git + VS Code

Verify:
```bash
docker --version
docker compose version
git --version
````

### Setup (Mac)

#### 1) Clone the repo

```bash
git clone <REPO_URL> docker-xampp
cd docker-xampp
```

#### 2) Create `.env` (optional but recommended)

```bash
cp .env.example .env
```

#### 3) Validate compose + start containers

```bash
docker compose config
docker compose up -d --build
docker compose ps
```

#### 4) Open

* Web: [http://localhost](http://localhost)
* phpMyAdmin: [http://localhost:8081](http://localhost:8081)

---

## Windows users

### Prerequisites (Windows 10/11)

1. Install Docker Desktop (WSL2 backend recommended)
2. Install Git for Windows
3. (Optional) Install VS Code

Verify (Git Bash or PowerShell):

```bash
docker --version
docker compose version
git --version
```

### Setup (Windows)

> IMPORTANT: Run commands in Git Bash or PowerShell from the repo root (folder that contains `docker-compose.yml`).

#### 1) Clone the repo

Git Bash:

```bash
cd /d
git clone <REPO_URL> docker-xampp
cd docker-xampp
```

PowerShell:

```powershell
cd D:\
git clone <REPO_URL> docker-xampp
cd docker-xampp
```

#### 2) Create `.env` (optional but recommended)

Git Bash:

```bash
cp .env.example .env
```

PowerShell:

```powershell
copy .env.example .env
```

#### 3) Validate compose + start containers

```bash
docker compose config
docker compose up -d --build
docker compose ps
```

#### 4) Open

* Web: [http://localhost](http://localhost)
* phpMyAdmin: [http://localhost:8081](http://localhost:8081)

---

## Where to put your projects

Put your code inside:

* `./projects/<your-project-name>`

It is mounted into containers as:

* `/var/www/html/<your-project-name>`

Example:

* Host: `./projects/myapp`
* Container: `/var/www/html/myapp`

---

## Laravel project commands (inside `projects/`)

### Example: Laravel app in `./projects/myapp`

#### 1) Enter the web container

```bash
docker compose exec web bash
```

#### 2) Install dependencies + app key

```bash
cd /var/www/html/myapp
composer install
cp .env.example .env
php artisan key:generate
```

#### 3) Configure DB (IMPORTANT)

Edit: `./projects/myapp/.env` and set:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=app
DB_USERNAME=laravel_user
DB_PASSWORD=secret
```

#### 4) Migrate

```bash
php artisan migrate
```

#### 5) Serve Laravel correctly (Apache should point to `/public`)

Laravel must be served from `public/`. Create an Apache vhost file in `./web/apache/`
and map your app to one of the extra ports (8000 / 8001).

Example (port 8000): create `./web/apache/myapp-8000.conf`

```apache
Listen 8000

<VirtualHost *:8000>
  DocumentRoot /var/www/html/myapp/public

  <Directory /var/www/html/myapp/public>
    AllowOverride All
    Require all granted
  </Directory>
</VirtualHost>
```

Restart Apache:

```bash
docker compose restart web
```

Open:

* [http://localhost:8000](http://localhost:8000)

---

## Vue / Node commands (run in `node` container)

> IMPORTANT: Node is NOT installed inside the `web` container. Use the `node` container for npm/Vite/Vue commands.

### 1) Enter the node container

```bash
docker compose exec node sh
```

### Case A: Separate Vue project (SPA)

If Vue app is `./projects/vueapp`:

```sh
cd /var/www/html/vueapp
npm install
npm run dev -- --host 0.0.0.0 --port 3000
```

Open:

* [http://localhost:3000](http://localhost:3000)

Windows watcher fix (if hot reload not working):

```sh
CHOKIDAR_USEPOLLING=true npm run dev -- --host 0.0.0.0 --port 3000
```

### Case B: Vue inside Laravel (Laravel + Vite)

If Vite is inside `./projects/myapp`:

```sh
cd /var/www/html/myapp
npm install
npm run dev -- --host 0.0.0.0 --port 3000
```

Open Laravel:

* [http://localhost:8000](http://localhost:8000) (or your configured Apache vhost port)

---

## Optional queue worker

Enable worker profile:

```bash
docker compose --profile worker up -d
```

---

## Common Docker commands (Mac + Windows)

### Check status / validate

```bash
docker compose ps
docker compose config
```

### View logs

```bash
docker compose logs -f --tail=200
docker compose logs -f --tail=200 web
docker compose logs -f --tail=200 db
docker compose logs -f --tail=200 phpmyadmin
```

### Enter containers

```bash
docker compose exec web bash
docker compose exec node sh
```

### Restart containers

```bash
docker compose restart
docker compose restart web
```

### Stop / Start

```bash
docker compose stop
docker compose start
```

### Remove containers (keep DB data)

```bash
docker compose down
```

### FULL RESET (deletes DB data)

```bash
docker compose down -v
```

---

## Troubleshooting (Windows)

### Port 80/443 already in use (common with IIS)

Check:

```powershell
netstat -ano | findstr :80
netstat -ano | findstr :443
```

Fix:

* Stop IIS / “World Wide Web Publishing Service”, OR
* Change mappings in `docker-compose.yml` (example: `8080:80`, `8443:443`)

### MySQL 3306 already in use

Check:

```powershell
netstat -ano | findstr :3306
```

Fix:

* Stop local MySQL service, OR
* Change mapping to `127.0.0.1:3307:3306`

---

## Security notes (local dev)

* Never commit `.env`
* Do not reuse these example DB passwords in production
* MySQL is bound to localhost (127.0.0.1) by default to avoid LAN exposure

