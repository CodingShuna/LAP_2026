<?php
require "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $conn->prepare("INSERT INTO mitarbeiter(vorname, nachname, email) VALUES(?,?,?)");
    $stmt->execute([$_POST["vorname"], $_POST["nachname"], $_POST["email"]]);
    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Mitarbeiter anlegen</title>
</head>
<body>
    <form method="post">
        <input name="vorname">
        <input name="nachname">
        <input name="email">
        <button>Speichern</button>
    </form>
</body>
</html>
