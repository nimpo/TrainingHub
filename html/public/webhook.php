<?php

#ini_set('display_errors', 0);
#error_reporting(E_ALL);

putenv('GIT_TERMINAL_PROMPT=0');
putenv('GIT_ASKPASS=/usr/local/bin/git-askpass');

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

#$allowedRepos = [
#    'my-repo' => '/srv/html/repos/my-repo',
#    'another-repo' => '/srv/html/repos/another-repo',
#];
#
#if (!isset($allowedRepos[$repo])) {
#    http_response_code(403);
#    echo 'Repo not allowed';
#    exit;
#}

#$repoDir = $allowedRepos[$repo];

#if (  is_dir('/srv/html/Repos/'.escapeshellarg($repo)) ) {
$cmd="";
$repoDir = "/srv/html/Repos/$repo";
if (  is_dir("/srv/html/Repos/$repo") ) {
#  $cmd = sprintf('cd "/srv/html/Repos/%s" && git pull --ff-only 2>&1', escapeshellarg($repo) );
  $cmd = sprintf('cd %s && git pull --ff-only 2>&1', escapeshellarg($repoDir)
    );
  $output = shell_exec($cmd);
}
else {
  $output="Nothing";
}

header('Content-Type: text/plain');
echo getmyuid();
echo "$output\n";
echo "$cmd\n";
?>
