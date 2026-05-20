<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use Github\Client;
use Github\AuthMethod;

$token = trim(file_get_contents('/run/secrets/github_token'));

$client = new Client();

$client->authenticate(
    $token,
    null,
    AuthMethod::ACCESS_TOKEN
);

$org = getenv('GITHUB_ORG') ?: '';
$user = $client->currentUser()->show();
$teams = $client->api('organization')->teams()->all($org);
$teammembers = [];
$students = [];
foreach($teams as $team) {
  $slug = $team['slug'];
  $members = $client->getHttpClient()->get(
        '/orgs/' . rawurlencode($org) . '/teams/' . rawurlencode($slug) . '/members'
    );
  $members=$client->api('organization')->teams()->members($slug,$org);
  $teamMembers[$slug] = [];
  foreach ($members as $member) {
    $teamMembers[$slug][] = $member['login'];
    $students[$member['login']] = $slug;
  }
}
?>

<h1>GitHub Teams for <?= $org ?></h1>

<ul>
<?php foreach ($teams as $team): ?>
    <li><?= htmlspecialchars($team['slug']) ?></li>
<?php endforeach; ?>
</ul>
<?php
print_r($teamMembers);
print("<br />\n");
print_r($students);
?>

