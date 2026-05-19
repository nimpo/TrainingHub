#!/bin/bash
set -eu

export GH_TOKEN=`cat /run/secrets/github_token`

if [ -f /state/setup-complete ]
then
  flock -n "/teams/.lock" bash -c '
    ME=`gh api user |jq -j .login`
    echo "Polling for groups"
    while read team
    do
      echo "Got $team:"
      gh api -H "Accept: application/vnd.github+json" -H "X-GitHub-Api-Version: 2026-03-10" /orgs/$GITHUB_ORG/teams/$team/members \
        | jq -r .[].login \
        | grep -v "^$ME$" \
        | tee /teams/$team
    done < <( gh api "/orgs/$GITHUB_ORG/teams" |jq -r .[].slug )
  ' || true
else
  echo "Running one-time setup..."
  if [ "$GITHUB_ORG" ]
  then
    if gh api "/orgs/$GITHUB_ORG/teams" |jq -r .[].slug |grep "^$CLASSNAME$"
    then
      echo "Classname team already defined"
    else
      gh api --method POST "/orgs/$GITHUB_ORG/teams" -f name="$CLASSNAME" -f privacy=closed
    fi
    touch /state/setup-complete
    echo "Setup complete"
  else
    echo "WARNING GITHUB_ORG not defined"
  fi
fi

