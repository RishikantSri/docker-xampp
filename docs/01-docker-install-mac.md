# Docker installation (macOS)

## Recommended: Docker Desktop
1. Install Docker Desktop for Mac (pick Apple Silicon vs Intel correctly).
2. Open Docker Desktop once and complete the setup.
3. Verify:
```bash
docker --version
docker compose version
```

## Troubleshooting
- If `docker compose` fails, Docker Desktop is not running.
- If bind mounts are slow, check Docker Desktop → Settings → Resources and File Sharing.
