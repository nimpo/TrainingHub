#!/usr/bin/env python3
import mailbox
import os
import re
import sys
from email import policy
from email.parser import BytesParser

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

user = m.group(1)
maildir = f"/srv/vmail/{user}/Maildir"

os.makedirs(f"{maildir}/cur", exist_ok=True)
os.makedirs(f"{maildir}/new", exist_ok=True)
os.makedirs(f"{maildir}/tmp", exist_ok=True)

md = mailbox.Maildir(maildir, create=True)
md.add(raw)
md.flush()
