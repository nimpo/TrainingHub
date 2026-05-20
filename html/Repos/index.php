<?php
$repos = '/srv/html/Repos';
$domain = getenv('DOMAIN') ?: 'example.net';

$items = [];

foreach (glob($repos . '/*/public_html', GLOB_ONLYDIR) as $publicHtml) {
    $name = basename(dirname($publicHtml));
    // Only list names that are valid single DNS labels.
#    if (!preg_match('/^[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?$/', $name)) {
#        continue;
#    }
    $items[] = $name;
}

sort($items, SORT_NATURAL | SORT_FLAG_CASE);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pages index</title>
</head>
<body>
    <h1>Pages index</h1>

    <?php if (!$items): ?>
        <p>No published pages found.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($items as $name): ?>
                <li>
                    <a href="https://<?= htmlspecialchars($name) ?>.pages.<?= htmlspecialchars($domain) ?>/">
                        <?= htmlspecialchars($name) ?>.pages.<?= htmlspecialchars($domain) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
