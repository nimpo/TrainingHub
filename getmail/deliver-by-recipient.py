#!/usr/bin/env python3
import mailbox
import os
import re
import sys
import logging
from email import policy
from email.parser import BytesParser
from email.utils import parseaddr
import json
import urllib.request
import urllib.error

classname = os.environ.get("CLASSNAME","default")

def invite_email_to_github_org(email):
    org = os.environ.get("GITHUB_ORG")
    if not org:
        return

    with open("/run/secrets/github_token") as f:
        token = f.read().strip()

    payload = { "email": email, "role": "direct_member", }

    print(f"Inviting {email} to {classname}")

    req = urllib.request.Request(
        f"https://api.github.com/orgs/{org}/invitations",
        data=json.dumps(payload).encode("utf-8"),
        method="POST",
        headers={
            "Accept": "application/vnd.github+json",
            "Authorization": f"Bearer {token}",
            "X-GitHub-Api-Version": "2022-11-28",
            "Content-Type": "application/json",
        },
    )

    try:
        with urllib.request.urlopen(req, timeout=10) as response:
            return response.status
    except urllib.error.HTTPError as e:
        print(f"GitHub org invite failed for {email}; Status: {e.code}")
        return None

domain = os.environ.get("DOMAIN")
if not domain:
    sys.exit("DOMAIN environment variable not set")

raw = sys.stdin.buffer.read()
msg = BytesParser(policy=policy.default).parsebytes(raw)

recipient = msg.get("X-Original-To")
if not recipient:
    sys.exit("missing X-Original-To header")

recipient = recipient.strip().lower()

pattern = rf"([A-Za-z0-9._-]+)@{re.escape(domain)}"
m = re.fullmatch(pattern, recipient)
if not m:
    sys.exit(f"invalid recipient address: {recipient}")

# Get message type and authenticity
from_addr = parseaddr(msg.get("From", ""))[1].lower()
from_domain = from_addr.rsplit("@", 1)[1]
subject = str(msg.get("Subject", ""))
auth_results = msg.get("ARC-Authentication-Results","")
dkim = "\n".join(msg.get_all("DKIM-Signature", []))
categories = msg.get("categories", "")

if not re.search(rf"\bdkim=pass\b.*?\bdmarc=pass\b.*?\bheader\.from={re.escape(from_domain)}\b",auth_results,re.IGNORECASE):
    sys.exit(1)
try:
    with open("/home/getmail/allowed-emails.txt") as f:
        allowed_emails = {
            line.strip().lower()
            for line in f
            if line.strip() and not line.startswith("#")
        }
except FileNotFoundError:
    allowed_emails = set()

if re.search(r"@(?:.*\.)?github\.com$", from_addr, re.IGNORECASE):
    print("Valid Github mail.")
elif from_addr.lower() in allowed_emails:
    print("Mail from allowed list.")
else:
    sys.exit(1)

# If is a github launch code then invite to org
if "Your GitHub launch code" in subject:
    invite_email_to_github_org(recipient)

user = m.group(1)
maildir = f"/srv/vmail/{user}/Maildir"

os.makedirs(f"{maildir}", exist_ok=True)

# Special case where github mail says added to special team
if "org-team-add-member" in categories and classname in subject:
    to_name, to_addr = parseaddr(msg.get("To", ""))
    username = to_name.strip()
    with open(f"{maildir}/githubname", "w") as f:
        f.write(username)

os.makedirs(f"{maildir}/cur", exist_ok=True)
os.makedirs(f"{maildir}/new", exist_ok=True)
os.makedirs(f"{maildir}/tmp", exist_ok=True)

md = mailbox.Maildir(maildir, create=True)
md.add(raw)
md.flush()
