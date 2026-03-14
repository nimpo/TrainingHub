import os

c = get_config()

# Basic hub config
c.JupyterHub.bind_url = "http://:8000"
c.JupyterHub.hub_ip = "0.0.0.0"

# Authentication
# NativeAuthenticator gives you local JupyterHub-managed accounts,
# without needing Linux users on the host.
c.JupyterHub.authenticator_class = "nativeauthenticator.NativeAuthenticator"

# Allow users to sign up
c.NativeAuthenticator.open_signup = True
c.Authenticator.allow_all = True

# Make the first admin account manually after signup if you want:
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
