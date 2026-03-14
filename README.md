# Requires
 - Route53 controled DNS zone with an api key with permissions to update the records (for Let's encrypt)
 - an EMAIL catchall service with MX rcords set for that domain that has IMAP service for getmail to draw down mails from (I use forwardemail.net)

# Configure
Add settings to .env, use the `env.template` to help.
Add a default profile to `secrets/aws-config` and `secrets/aws-credentials` for access to add records to Route53 for domain.
Add upstream password to the file `secrets/imap_password.txt`
Add group password to the file `secrets/group_password.txt`

# Build
- Need to build with additional profile as Jupyter single user container needs building too.
```
docker compose --profile build-only build
```

# Run
```
docker compose up -d
```

Have DNS records or entries in /etc/hosts that point to hosting service ip.

Browse to https://\<your\_domain\>

# TODO
make some nice html content for https://${DOMAIN}
