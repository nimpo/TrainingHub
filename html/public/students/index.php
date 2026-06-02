<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

ini_set('display_errors', 1);
error_reporting(E_ALL);

require '/srv/html/vendor/autoload.php';

use Github\Client;
use Github\AuthMethod;

$token = trim(file_get_contents('/run/secrets/github_token'));
$githubOrg=getenv('GITHUB_ORG') ?: '';
$class=getenv('CLASSNAME') ?: '';

$client = new Client();

$client->authenticate(
    $token,
    null,
    AuthMethod::ACCESS_TOKEN
);

$mailRoot  = '/srv/html/mail';
$groupRoot = '/srv/html/teams';

$groups = [];
foreach (preg_grep('/^([^.])/', scandir($groupRoot)) as $file) {
    if ($file === '.' || $file === '..') { continue; }
    $fullPath = $groupRoot . '/' . $file;
    if (!is_file($fullPath)) { continue; }
    $members = file($fullPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $groups[$file] = $members;
}
echo '<html><head><title>Student Page</title><link rel="stylesheet" href="/css/base.css" /></head>';
echo '<body>';
echo '<h1>Student page</h1>';
echo '<p>Instructions for your first step: how to get a github account, can be found <a href="setup.html">here</a>.</p>';
echo '<p>Instructions for all your next steps: how use your github account and complete the project, can be found <a href="github.html">here</a>.</p>';
echo '<p>Schedule for the comming weeks: <a href="schedule.html">click here!</a>.</p>';
echo '<h3>Your class registration progress</h3>';
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Email.</th><th>GitHub</th><th>Team</th>";
echo "</tr>";

foreach (scandir($mailRoot) as $user) {
    if ($user === '.' || $user === '..') { continue; }
    $userDir = $mailRoot . '/' . $user;
    if (!is_dir($userDir)) { continue; }
    $githubFile = $userDir . '/Maildir/githublogin';
    $github = '';
    if (file_exists($githubFile)) { $github = trim(file_get_contents($githubFile)); }
    $userGroups = [];
    foreach ($groups as $groupName => $members) {
        if (in_array($github, $members)) {
            $userGroups[] = $groupName;
        }
    }

    // Output row https://email.rsemcr.uk/?_user=
    echo "<tr>";
    if ( $_SERVER['PHP_AUTH_USER'] == $user ) {
      echo '<td><a href="https://email.' .$_SERVER['HTTP_HOST'].'/?_user='.urlencode($user).'">'.htmlspecialchars($user)."</a></td>";
      echo '<td><a href="https://github.com/' . urlencode($github) . '">'.htmlspecialchars($github)."</a></td>";
      echo "<td>" . 
        implode(', ', 
          array_map( 
            fn($team) => sprintf('<a href="https://github.com/orgs/%s/teams/%s">%s</a>',urlencode($githubOrg), urlencode($team), htmlspecialchars($team)),
            array_filter($userGroups,fn($team) => $team !== $class)
          )  
        )."</td>";
    } else {
#      echo '<td>'.htmlspecialchars($user)."</td>";
      echo '<td></td>';
      echo '<td>'.htmlspecialchars($github)."</td>";
      echo "<td>".
        implode(', ', 
          array_map( 
            fn($team) => sprintf('%s',htmlspecialchars($team)),
            array_filter($userGroups,fn($team) => $team !== $class)
          )  
        )."</td>";
    }
    echo "</tr>";
}
echo "</table>";
?>

<script>

let refreshTimer;

// Start (or restart) 60 second refresh timer
function startRefreshTimer() {

    clearTimeout(refreshTimer);

    refreshTimer = setTimeout(function () {
        window.location.reload();
    }, 60000);
}

// If user clicks any dropdown, restart timer
document.querySelectorAll("select").forEach(function (dropdown) {

    dropdown.addEventListener("click", function () {
        startRefreshTimer();
    });

    dropdown.addEventListener("change", function () {
        startRefreshTimer();
    });
});

// Initial timer start
startRefreshTimer();

</script>
<p>Page refreshes every minute</p>
</body>
</html>
