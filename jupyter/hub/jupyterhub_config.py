import os
from pathlib import Path
from jupyterhub.auth import Authenticator
import re

c = get_config()

# Basic hub config
c.JupyterHub.bind_url = "http://:8000"
c.JupyterHub.hub_ip = "0.0.0.0"

# Authentication
GROUP_PASSWORD = Path("/run/secrets/group_password").read_text().strip()
ADMIN_PASSWORD = Path("/run/secrets/jupyterhub_admin_password").read_text().strip()

class GroupPasswordAuthenticator(Authenticator):
    async def authenticate(self, handler, data):
        username = data["username"].strip().lower()
        password = data["password"]

        if not re.fullmatch(r"[a-z0-9._-]+", username):
            return None

        if username == "admin":
            if password == ADMIN_PASSWORD:
                return username
            return None

        if not os.path.isdir(os.path.join('/srv/vmail', username)):
            return None

        expected = f"{GROUP_PASSWORD}-{username}"

        if password == expected:
            return username

        return None

c.JupyterHub.authenticator_class = GroupPasswordAuthenticator
c.Authenticator.allow_all = True
c.Authenticator.admin_users = {"admin"}

# Spawner
c.JupyterHub.spawner_class = "dockerspawner.DockerSpawner"

c.DockerSpawner.image = os.environ.get(
    "DOCKER_JUPYTER_IMAGE",
    "jupyterhub-singleuser-nokernel:latest"
)

c.DockerSpawner.network_name = os.environ.get(
    "DOCKER_NETWORK_NAME",
    "jupyterhub-net"
)

# One persistent volume per user
c.DockerSpawner.volumes = {
    "jupyterhub-user-{username}": "/home/jovyan"
}

# Start users in JupyterLab
c.Spawner.default_url = "/lab"
c.DockerSpawner.notebook_dir = "/home/jovyan"

# Remove stopped containers, keep data in volumes
c.DockerSpawner.remove = True

# Run containers on the shared docker network
c.DockerSpawner.extra_host_config = {
    "network_mode": c.DockerSpawner.network_name,
    "cap_drop": ["ALL"],
    "security_opt": ["no-new-privileges:true"],
}

# Optional resource limits
c.DockerSpawner.mem_limit = "256M"
c.DockerSpawner.cpu_limit = 0.25

# Timeouts
c.Spawner.http_timeout = 120
c.Spawner.start_timeout = 120

