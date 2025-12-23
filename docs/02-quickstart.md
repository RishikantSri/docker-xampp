# Quickstart

```bash
git clone <repo-url>
cd <repo-folder>
cp .env.example .env   # optional
docker compose up -d --build
```

Open:
- http://localhost
- http://localhost:8081 (phpMyAdmin)

Stop:
```bash
docker compose down
```
Reset DB (deletes volume):
```bash
./bin/reset-db.sh
```
