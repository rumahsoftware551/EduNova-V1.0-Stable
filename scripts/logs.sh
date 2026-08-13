#!/usr/bin/env bash
set -Eeuo pipefail
source "$(dirname "$0")/common.sh"
need_env
compose logs --tail=200 -f "${1:-backend}"
