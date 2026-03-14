FROM python:3.12-slim

RUN apt-get update \
 && apt-get install -y --no-install-recommends ca-certificates tzdata gettext-base\
 && rm -rf /var/lib/apt/lists/*

RUN python -m pip install --no-cache-dir getmail6

RUN groupadd -g 1000 getmail \
 && useradd -u 1000 -g 1000 -m -s /bin/sh getmail

COPY deliver-by-recipient.py /usr/local/bin/deliver-by-recipient.py
RUN chmod +x /usr/local/bin/deliver-by-recipient.py

USER getmail
WORKDIR /home/getmail
