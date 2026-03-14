FROM jupyterhub/jupyterhub:5.2

RUN pip install --no-cache-dir \
    dockerspawner \
    jupyterhub-nativeauthenticator

COPY jupyterhub_config.py /srv/jupyterhub/jupyterhub_config.py

CMD ["jupyterhub", "-f", "/srv/jupyterhub/jupyterhub_config.py"]
