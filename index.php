<?php
require "config.php";
$rows = $conn->query("SELECT * FROM mitarbeiter")->fetchAll();
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Mitarbeiter</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Mitarbeiter</h1>
    <a href="create.php">Neu</a>
    <table>
        <tr>
            <th>ID</th>
            <th>Vorname</th>
            <th>Nachname</th>
            <th>Email</th>
            <th></th>
        </tr>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= $r["id"] ?></td>
                <td><?= h($r["vorname"]) ?></td>
                <td><?= h($r["nachname"]) ?></td>
                <td><?= h($r["email"]) ?></td>
                <td><a href="edit.php?id=<?= $r["id"] ?>">Bearbeiten</a> <a href="delete.php?id=<?= $r["id"] ?>">Löschen</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
