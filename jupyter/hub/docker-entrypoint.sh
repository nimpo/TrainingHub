#!/usr/bin/env bash
set -euo pipefail

if [ -n "${JUPYTERHUB_ADMIN_PASSWORD:-}" ]; then
  python /usr/local/bin/bootstrap-admin.py
fi

exec jupyterhub -f /srv/jupyterhub/jupyterhub_config.py
