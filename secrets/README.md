# Secrets

## In this directory you should have seven files:
### `aws-config` 
containing AWS Cli config

Example file content:
```
[default]
region=eu-west-2
output=json
```
### `aws-credentials` 
containing AWS Cli secrets (for API access limited to actions in your single DNS zone)

Example file content:
```
# "Default" key with permissions to alter your domain's DNS entries
[default]
aws_access_key_id = AKEYAKEYAKEYAKEYAKEY
aws_secret_access_key = /AWSSECRETfooBAR0123456789abcdefghijklmn
```
### `group_password.txt` 
A single line which contains the prefix to your group password

Example file content:
```
SingleLineGroupPasswordPrefix
```
### `imap_password.txt`
The password of the account you set up in forwardemail for incoming emails

Example file content:
```
SingleLinePasswordForForwardemail.netIMAPAccount
```
### `jupyterhub_admin_password.txt`
The password of the jupyter admin account

Example file content:
```
SingleLinePassword
```

### `github_token.txt`
The ghthub token with sufficient permissions to admin the organisation and pull/push/create repos and actions

Example file content:
```
gho_VG9rZW4gd2l0aCBnb29kIHBlcm1pc3Npb25z
```

### `github_webhook_secret.txt`
The token that we will add to the repos to authenticate back to the webhook endpoint on this server.

Generate this :
```
openssl rand -hex 32 > github_webhook_secret.txt
```