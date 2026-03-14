FROM debian:stable-slim

RUN apt-get update && apt-get install -y --no-install-recommends \
    bash \
    git \
    jq \
    curl \
    openssl \
    grep \
    coreutils \
    sed \
    sudo \
    findutils \
    gawk \
    awscli \
    dnsutils \
    ca-certificates \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /app

RUN git clone https://github.com/nimpo/acme-le-bash.git

WORKDIR /app/acme-le-bash
