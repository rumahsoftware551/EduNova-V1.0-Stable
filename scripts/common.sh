#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
COMPOSE_FILE="$ROOT_DIR/docker-compose.production.yml"
ENV_FILE="$ROOT_DIR/.env.production"

if docker info >/dev/null 2>&1; then
  DOCKER=(docker)
elif command -v sudo >/dev/null 2>&1 && sudo -n docker info >/dev/null 2>&1; then
  DOCKER=(sudo docker)
else
  DOCKER=(docker)
fi

compose() {
  "${DOCKER[@]}" compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" "$@"
}

docker_cmd() {
  "${DOCKER[@]}" "$@"
}

need_env() {
  if [[ ! -f "$ENV_FILE" ]]; then
    echo "ERROR: .env.production belum ada. Jalankan ./install-vps.sh atau copy .env.production.example." >&2
    exit 1
  fi
}
