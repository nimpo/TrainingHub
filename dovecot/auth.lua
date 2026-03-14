local function read_secret(path)
  local f = io.open(path, "r")
  if not f then
    return nil
  end

  local value = f:read("*l") 
  f:close()

  return value
end

function auth_password_verify(request, password)

  local user = request.user
  local mailbox = "/srv/vmail/" .. user .. "/Maildir"

  local prefix = read_secret("/run/secrets/group_password")
  if not prefix then
    return dovecot.auth.PASSDB_RESULT_INTERNAL_FAILURE, "group password secret not available"
  end

  local expected = prefix .. "-" .. user

  -- check password rule
  if password ~= expected then
    return dovecot.auth.PASSDB_RESULT_PASSWORD_MISMATCH, "invalid password"
  end

  -- check mailbox exists
  local f = io.open(mailbox, "r")
  if f == nil then
    return dovecot.auth.PASSDB_RESULT_USER_UNKNOWN, "mailbox not created yet"
  end
  f:close()

  return dovecot.auth.PASSDB_RESULT_OK, {}
end
