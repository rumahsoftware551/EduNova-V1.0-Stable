#!/usr/bin/env bash
set -Eeuo pipefail
source "$(dirname "$0")/common.sh"
need_env
compose ps
echo
curl -fsS "http://127.0.0.1:$(grep '^APP_PORT=' "$ENV_FILE" | cut -d= -f2-)/api/v1/health" || true
echo
