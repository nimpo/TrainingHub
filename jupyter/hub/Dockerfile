FROM jupyterhub/jupyterhub:5.2

RUN pip install --no-cache-dir \
    dockerspawner \
    jupyterhub-nativeauthenticator

COPY jupyterhub_config.py /srv/jupyterhub/jupyterhub_config.py
COPY bootstrap-admin.py /usr/local/bin/bootstrap-admin.py
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

#CMD ["jupyterhub", "-f", "/srv/jupyterhub/jupyterhub_config.py"]
