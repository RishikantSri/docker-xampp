#!/usr/bin/env bash
set -euo pipefail
echo "This will DELETE the MySQL volume (db_data)."
read -p "Type YES to continue: " ans
if [ "$ans" = "YES" ]; then
  docker compose down -v
  echo "DB volume removed."
else
  echo "Cancelled."
fi
