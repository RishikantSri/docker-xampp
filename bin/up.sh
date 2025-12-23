#!/usr/bin/env bash
set -euo pipefail
docker compose up -d --build
echo ""
echo "Started. Open:"
echo "  - http://localhost (Apache web)"
echo "  - http://localhost:8081 (phpMyAdmin)"
