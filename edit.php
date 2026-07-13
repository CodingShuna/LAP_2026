<?php
require "config.php";
$id = (int) ($_GET["id"] ?? 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $conn->prepare("UPDATE mitarbeiter SET vorname=?, nachname=?, email=? WHERE id=?");
    $stmt->execute([$_POST["vorname"], $_POST["nachname"], $_POST["email"], $id]);
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM mitarbeiter WHERE id=?");
$stmt->execute([$id]);
$r = $stmt->fetch();
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Mitarbeiter bearbeiten</title>
</head>
<body>
    <form method="post">
        <input name="vorname" value="<?= h($r["vorname"] ?? "") ?>">
        <input name="nachname" value="<?= h($r["nachname"] ?? "") ?>">
        <input name="email" value="<?= h($r["email"] ?? "") ?>">
        <button>Speichern</button>
    </form>
</body>
</html>
