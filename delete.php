<?php
require "config.php";
$id = (int) ($_GET["id"] ?? 0);
$stmt = $conn->prepare("DELETE FROM mitarbeiter WHERE id=?");
$stmt->execute([$id]);
header("Location: index.php");
exit;
