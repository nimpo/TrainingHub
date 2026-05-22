#!/bin/bash
set -eu

export GH_TOKEN=`cat /run/secrets/github_token`

if [ -f /state/setup-complete ]
then
  gh api -H "Accept: application/vnd.github+json"   -H "X-GitHub-Api-Version: 2026-03-10" "/orgs/$GITHUB_ORG/members" |jq -r .[].login > /tmp/members

  flock -n "/teams/.lock" bash -c '
    ME=`gh api user |jq -j .login`
    while read team
    do
      echo "Pulling $team onto /teams/$team"
      gh api -H "Accept: application/vnd.github+json" -H "X-GitHub-Api-Version: 2026-03-10" "/orgs/$GITHUB_ORG/teams/$team/members" \
        | jq -r .[].login \
        | grep -v "^$ME$" \
        > /teams/$team
      if [ "$team" = "$CLASSNAME" ]
      then
        while read member 
        do
          if [ "$member" ]
          then
            if ! grep -q "^$member$" /teams/$team 
            then
              echo "Member $member is not in the team $team, lets add them"
              gh api --method PUT -H "Accept: application/vnd.github+json" -H "X-GitHub-Api-Version: 2026-03-10" "/orgs/$GITHUB_ORG/teams/$team/memberships/$member" | jq .
              echo "$member" >> /teams/$team
            fi
          fi   
        done < <( grep -v "^$ME$" /tmp/members )
      fi       
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

