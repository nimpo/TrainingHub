# Purpose
Training hub is designed to provide a simple platform to demonstrate Research Software Engineering with an agile development methodology. You will need a domain registered in Route53 (e.g. `traininghub.example.org`)

An instance of TrainingHub provides several user facing services.
 - A frontend website at (in the case of our example domain) https://traininghub.example.org. This initially contains:
   - instructions which can be configured to suit your setup.
   - A password protected https://traininghub.example.org/class which will show the registration progress and allow you to manage your teams.
 - A stripped down webmail email client (receive only from a set of limited addresses) `https://email.traininghub.example.org`
 - A stripped down (no kernel) Jupyter Hub with docker image execution environment with git plugin, `https://jupyter.traininghub.example.org`
 - Your server will listen for GitHub webhook connections at `https://traininghub.example.org/webhooks.php`, if this is exposed then the server will populate `pages.traininghub.example.org` and `<repo>.pages.traininghub.example.org` with any `/public_html/` content from each `<repo>`. Alternatively you can setup a GitHub Pages for your repos.

The email serivce is primarily designed so that personal classroom email addresses, can be used to apply for a personal GitHub account [See here](https://docs.github.com/en/get-started/start-your-journey/creating-an-account-on-github#signing-up-for-a-new-personal-account).

TrainingHub is a docker environment. It can be run on a public or private network. Some outbound connectivity is needed to reach LetsEncrypt's ACME certificate service and pull mail from a catchall IMAP account of your choice I use [Forward Email](https://forwardemail.net). If on a public network access can be restricted by firewall, and in any case, https server will return Forbidden to any requests outside a set group of IP addresses which can be configured in the `.env` configuration file.

# Requirements
 - Route53 controled DNS zone with an api key with permissions to update the records (for Let's encrypt)
 - an EMAIL catchall service with MX rcords set for that domain that has IMAP service for getmail to draw down mails from (I use forwardemail.net)
 - A GitHub Organisation with reposotories and projects setup-up for team based pulls, pushes and issues.

# Configure
1. Add settings to `.env`, use the `env.template` to help.
1. Add a default profile to `secrets/aws-config` and `secrets/aws-credentials` for access to add records to Route53 for domain.
1. Add upstream imap password to the file `secrets/imap_password.txt`
1. Add group password to the file `secrets/group_password.txt`
1. Add a password for the Jupyter admin account in `jupyterhub_admin_password.txt`
1. Add a git personal access token to `secrets/github_token.txt` with sufficient permsissions to invite users to your GitHub Organisation and manage its teams, and to clone repos therein.
1. Create and add a secret webhooks key for your repositories and store it in `secrets/github_webhook_secret.txt`
1. At your preference, add a `./allowed-emails.txt` file, use the `./allowed-emails.txt.example` to help.

There are further instructions on *secret* files in the corresponding [README.md](secrets/README.md)  

# Build
- Need to build with additional profile as Jupyter single user container needs building too...
```
docker compose --profile build-only build
```

# Run
```
docker compose up -d
```

Have DNS records or entries in /etc/hosts that point to hosting service ip.
Consider creating the following records in DNS:

| Type   | Name                            | Target                          |
|--------|---------------------------------|---------------------------------|
| A      | traininghub.example.org         | `<SERVER_IP>`                   |
| CNAME  | *.traininghub.example.org       | `traininghub.example.org`       |
| CNAME  | *.pages.traininghub.example.org | `pages.traininghub.example.org` |

# Use
Browse to *https://\<your\_domain\>*

