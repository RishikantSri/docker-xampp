# Troubleshooting

## Port already in use
If Docker fails with "port is already allocated":
- Stop the process using that port, or change the port mapping in `docker-compose.yml`.

## Slow file sync on Mac
- Docker Desktop → Settings → Resources (increase CPU/RAM)
- Docker Desktop → Settings → File Sharing (make sure your repo folder is shared)

## MySQL connection
From host:
- Host: 127.0.0.1
- Port: 3306
- User: laravel_user (default)
- Password: secret (default)

From containers (Laravel .env):
- DB_HOST=db
- DB_PORT=3306
