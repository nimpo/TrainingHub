<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../vendor/autoload.php';

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

#################################
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lockHandle = fopen($groupRoot.'/.lock', 'c');
    if (flock($lockHandle, LOCK_EX)) {
        foreach ($_POST['group'] as $email => $teamSlug) {
            if ($teamSlug === '') { continue; }
            $githubFile = $mailRoot . '/' . $email . '/Maildir/githubname';
            if (!file_exists($githubFile)) { continue; }
            $githubUsername = trim(file_get_contents($githubFile));
            if ($githubUsername === '') { continue; }
            if ($_POST['action'] == "add") {
                try {
                    $res=$client->api('organization')->teams()->addMember($teamSlug, $githubUsername,$githubOrg);
                } catch (Exception $e) {
                    file_put_contents('php://stderr', "Failed to add ".$githubUsername." to ".$teamSlug.": ".$e->getMessage());
                }
            }
            elseif ($_POST['action'] == "remove") {
                try {
                    $res=$client->api('organization')->teams()->removeMember($teamSlug, $githubUsername,$githubOrg);
                } catch (Exception $e) {
                    file_put_contents('php://stderr', "Failed to remove ".$githubUsername." from ".$teamSlug.": ".$e->getMessage());
                }
            }
        }
        # Do the refresh
        $teams = $client->api('organization')->teams()->all($githubOrg);
        foreach ($teams as $team) {
          $slug = $team['slug'];
          $members=$client->api('organization')->teams()->members($slug,$githubOrg);
          $usernames = [];
          foreach ($members as $member) { $usernames[] = $member['login']; }
          file_put_contents($groupRoot .'/'.$slug, implode("\n", $usernames));
        }
    }
    fclose($lockHandle);
}
####################################

$groups = [];
foreach (preg_grep('/^([^.])/', scandir($groupRoot)) as $file) {
    if ($file === '.' || $file === '..') { continue; }
    $fullPath = $groupRoot . '/' . $file;
    if (!is_file($fullPath)) { continue; }
    $members = file($fullPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $groups[$file] = $members;
}

echo "<form method='POST'>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Email.</th><th>GitHub</th><th>Groups</th><th>Add To Group</th>";
echo "</tr>";

foreach (scandir($mailRoot) as $user) {
    if ($user === '.' || $user === '..') { continue; }
    $userDir = $mailRoot . '/' . $user;
    if (!is_dir($userDir)) { continue; }
    $githubFile = $userDir . '/Maildir/githubname';
    $github = '';
    if (file_exists($githubFile)) { $github = trim(file_get_contents($githubFile)); }
    $userGroups = [];
    foreach ($groups as $groupName => $members) {
        if (in_array($github, $members)) {
            $userGroups[] = $groupName;
        }
    }

    // Output row
    echo "<tr>";
    echo "<td>" . htmlspecialchars($user) . "</td>";
    echo "<td>" . htmlspecialchars($github) . "</td>";
    echo "<td>" . htmlspecialchars(implode(', ', $userGroups)) . "</td>";
    echo "<td>";
    if ($github != "") {
        echo "<select name='group[$user]'>";
        echo "<option value=''>Select group</option>";
        foreach ($groups as $groupName => $members) {
            if ($groupName == $class) {continue;}
            echo "<option value='" . htmlspecialchars($groupName) . "'>";
            echo htmlspecialchars($groupName);
            echo "</option>";
        }
        echo "</select>";
    }
    echo "</td></tr>";
}
echo "<tr>";
echo "<td colspan='3'></td>";
echo "<td>";
echo "<input type='submit' name='action' value='add'>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td colspan='3'></td>";
echo "<td>";
echo "<input type='submit' name='action' value='remove'>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</form>";
?>

<script>

let refreshTimer;

// Start (or restart) 60 second refresh timer
function startRefreshTimer() {

    clearTimeout(refreshTimer);

    refreshTimer = setTimeout(function () {
      window.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>";
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
