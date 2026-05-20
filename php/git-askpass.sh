#!/bin/sh
case "$1" in
  *Username*) echo "x-access-token" ;;
  *Password*) cat /run/secrets/github_token ;;
esac
