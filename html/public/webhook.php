<?php
putenv('GIT_TERMINAL_PROMPT=0');
putenv('GIT_ASKPASS=/usr/local/bin/git-askpass');
$githubOrg = $_ENV['GITHUB_ORG'] ?? '';

$secret = trim(file_get_contents('/run/secrets/github_webhook_secret'));
$payload = file_get_contents('php://input');

$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

$expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    echo "signature=$signature\n";
    echo "expecting=$expected\n";
    echo 'Invalid signature';
    exit;
}

$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';

if ($event !== 'push') {
    http_response_code(202);
    echo 'Ignored event';
    exit;
}

$data = json_decode($payload, true);

if (($data['ref'] ?? '') !== 'refs/heads/main') {
    http_response_code(202);
    echo 'Ignored branch';
    exit;
}

$repo = $data['repository']['name'] ?? null;

$cmd="";
if ( is_dir("/srv/html/Repos/$repo") ) {
  $cmd = sprintf('cd %s && git pull --ff-only 2>&1', escapeshellarg("/srv/html/Repos/$repo"));
  $output = shell_exec($cmd);
}
else {
  $cmd = sprintf('cd /srv/html/Repos && git clone %s 2>&1', escapeshellarg("https://github.com/$githubOrg/$repo.git"));
  $output = shell_exec($cmd);
}

header('Content-Type: text/plain');
echo "OK\n";
echo "Command = $cmd\n";
echo "Output - $output\n";
?>
