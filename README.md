# Purpose
Training hub is designed to provide a simple platform to demonstrate Research Software Engineering with an agile development methodology. You will need a domain registered in Route53 (e.g. traininghub.example.org)

An instance of TrainingHub provides three user facing services.
 - A frontend website at (in the case of our example domain) https://traininghub.example.org
 - A stripped down webmail email client (receive only) https://email.traininghub.example.org
 - A stripped down (no kernel) Jupyter Hub with docker image execution environment with git plugin, https://jupyter.traininghub.example.org

The email serivce is so that personal classroom email addresses, can be used to apply for a personal github account [See here](https://docs.github.com/en/get-started/start-your-journey/creating-an-account-on-github#signing-up-for-a-new-personal-account).

TrainingHub is a docker environment. It can be run on a public or private network. Some outbound connectivity is needed to reach LetsEncrypt's ACME certificate service and pull mail from a catchall IMAP account of your choice I use [Forward Email](https://forwardemail.net).

# Requirements
 - Route53 controled DNS zone with an api key with permissions to update the records (for Let's encrypt)
 - an EMAIL catchall service with MX rcords set for that domain that has IMAP service for getmail to draw down mails from (I use forwardemail.net)

# Configure
1. Add settings to .env, use the `env.template` to help.
1. Add a default profile to `secrets/aws-config` and `secrets/aws-credentials` for access to add records to Route53 for domain.
1. Add upstream password to the file `secrets/imap_password.txt`
1. Add group password to the file `secrets/group_password.txt`

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
