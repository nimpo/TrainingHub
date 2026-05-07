import os
from jupyterhub.app import JupyterHub
from nativeauthenticator import NativeAuthenticator

password_file = os.environ.get("JUPYTERHUB_ADMIN_PASSWORD_FILE")

if password_file:
  with open(password_file, "r") as f:
    password = f.read().strip()
else:
  raise RuntimeError("Admin password nto found in docker secrets")

app = JupyterHub.instance()
app.load_config_file("/srv/jupyterhub/jupyterhub_config.py")
app.init_db()

auth = NativeAuthenticator(db=app.db)
auth.admin_users = {"admin"}
auth.open_signup = True

if not auth.user_exists("admin"):
  created = auth.create_user(username, password)
  if created is None:
    raise RuntimeError( "Admin user was not created. Password may not meet NativeAuthenticator password rules.")

print(f"Admin user '{username}' exists.")
