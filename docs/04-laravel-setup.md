# Laravel setup (inside the container)

1) Put your Laravel project in:
- `./projects/myapp`

2) Enter web container:
```bash
docker compose exec web bash
cd /var/www/html/myapp
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

## Option A: Run Laravel via `php artisan serve` (simple)
```bash
php artisan serve --host=0.0.0.0 --port=8000
```
Open: http://localhost:8000

## Option B: Run via Apache vhost (more production-like)
Edit `web/apache/000-default.conf` (or use `saasapp.local.conf.example`) to point DocumentRoot to `public/`.
Then rebuild:
```bash
docker compose up -d --build
```
