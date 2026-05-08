#!/usr/bin/env bash
set -euo pipefail

python /usr/local/bin/bootstrap-admin.py

exec jupyterhub -f /srv/jupyterhub/jupyterhub_config.py
