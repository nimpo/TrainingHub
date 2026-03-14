# Configure
Add settings to .env use the `env.template` to help.
Add a default profile to `secrets/aws-config` and `secrets/aws-credentials` for access to add records to Route53 for domain.
Add upstream password to the file `secrets/imap_password.txt`
Add group password to the file `secrets/group_password.txt`

# Build
docker compose --profile build-only build

# Run
docker compose up -d

#TODO
make some nice html content for https://${DOMAIN}
