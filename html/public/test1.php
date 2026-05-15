<?php
$people = [
    1 => "Alice",
    2 => "Bob",
    3 => "Charlie",
    4 => "Dana"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $groupName = $_POST["groupname"] ?? "";
    $members = $_POST["members"] ?? [];

    echo "<h2>Submitted group</h2>";
    echo "Group name: " . htmlspecialchars($groupName) . "<br>";

    echo "Members:<br>";
    foreach ($members as $personId) {
        if (isset($people[$personId])) {
            echo htmlspecialchars($people[$personId]) . "<br>";
        }
    }
}
?>

<form method="post">
    <label for="groupname">Group name</label><br>
    <input type="text" id="groupname" name="groupname" required>

    <h3>Select members</h3>

    <?php foreach ($people as $id => $name): ?>
        <label>
            <input type="checkbox" name="members[]" value="<?= htmlspecialchars($id) ?>">
            <?= htmlspecialchars($name) ?>
        </label><br>
    <?php endforeach; ?>

    <br>
    <button type="submit">Create group</button>
</form>
