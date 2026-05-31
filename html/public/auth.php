<?php

$secret_raw = file_get_contents('/run/secrets/group_password');
$adminsecret_raw = file_get_contents('/run/secrets/admin_password');

if ($secret_raw === false || $adminsecret_raw === false) {
    http_response_code(500);
    exit("Auth config error");
}

$secret = trim($secret_raw);
$adminsecret = trim($adminsecret_raw);

function deny(): void {
    header('WWW-Authenticate: Basic realm="students"');
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

$header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if (!str_starts_with($header, 'Basic ')) { deny(); }

$decoded = base64_decode(substr($header, 6), true);

if ($decoded === false || !str_contains($decoded, ':')) { deny(); }

[$username, $password] = explode(':', $decoded, 2);

if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) { deny(); }

if (hash_equals($adminsecret, $password) && $username === "admin") {
  http_response_code(204);
}
else {
  $userDir = '/srv/html/mail/' . $username;

  if (!is_dir($userDir)) { deny(); }

  $expected = $secret . '-' . $username;

  if (!hash_equals($expected, $password)) { deny(); }

  http_response_code(204);
}
