# Common commands

## Status / logs
```bash
docker compose ps
docker compose logs -f
```

## Shell access
```bash
docker compose exec web bash
docker compose exec node bash
docker compose exec db bash
```

## Rebuild
```bash
docker compose up -d --build
```

## Full reset (containers + volumes)
```bash
docker compose down -v
```
