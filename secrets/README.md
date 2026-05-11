# Secrets

## In this directory you should have five files:
* `aws-config` containing AWS Cli config
Example file content:
```
[default]
region=eu-west-2
output=json
```
* `aws-credentials` containing AWS Cli secrets (for API access limited to actions in your single DNS zone)
Example file content:
```
# "Default" key with permissions to alter your domain's DNS entries
[default]
aws_access_key_id = AKEYAKEYAKEYAKEYAKEY
aws_secret_access_key = /AWSSECRETfooBAR0123456789abcdefghijklmn
```
* `group_password.txt` A single line which contains the prefix to your group password
Example file content:
```
SingleLineGroupPasswordPrefix
```
* `imap_password.txt` The password of the account you set up in forwardemail for incoming emails
Example file content:
```
SingleLinePasswordForForwardemail.netIMAPAccount
```
* `jupyterhub_admin_password.txt` The password of the jupyter admin account
Example file content:
```
SingleLinePassword
```
