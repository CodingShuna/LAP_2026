<!DOCTYPE html>
<html>
<head>
    <title>Termin anlegen</title>
</head>
<body>

<?php
/*
 * DATENBANKVERBINDUNG
 * Quelle: tutorialrepublic.com (config.php, Object-Oriented-Tab)
 * Identisch zu list.php - jede Datei, die mit der DB spricht,
 * braucht ihre eigene Verbindung.
 */
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "LAP_Exercise";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/*
 * DIE DREI DROPDOWNS
 * Quelle: w3schools.com/php/php_mysql_select.asp
 * Gleicher while-Block dreimal angewendet, weil wir drei
 * Fremdschlüssel-Tabellen haben. Keine der Referenzseiten zeigt das
 * "3x hintereinander" explizit - das ist reine Wiederholung desselben
 * Bausteins, kein neues Konzept.
 */
$mitglieder = '<option value="">WÄHLEN</option>';
$sql = "SELECT idMITGLIEDER, MITGLIEDERname FROM MITGLIEDER";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $mitglieder .= '<option value="' . $row["idMITGLIEDER"] . '">' . $row["MITGLIEDERname"] . '</option>';
}

$trainer = '<option value="">WÄHLEN</option>';
$sql = "SELECT idTRAINER, TRAINERname FROM TRAINER";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $trainer .= '<option value="' . $row["idTRAINER"] . '">' . $row["TRAINERname"] . '</option>';
}

$kurse = '<option value="">WÄHLEN</option>';
$sql = "SELECT idKURSE, KURSEbezeichnung FROM KURSE";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $kurse .= '<option value="' . $row["idKURSE"] . '">' . $row["KURSEbezeichnung"] . '</option>';
}
?>

<!--
  FORMULAR
  Quelle: tutorialrepublic.com (create.php)
  action="edit.php" schickt das Formular zurück an diese eigene Datei -
  dieselbe Datei zeigt also sowohl das leere Formular (beim ersten
  Aufruf) als auch die Verarbeitung (nach dem Abschicken).
-->
<form action="edit.php" method="POST">
<table>
<tr><td>Mitglied:</td><td>
<select name="mitglied"><?php echo $mitglieder; ?></select>
</td></tr>
<tr><td>Trainer:</td><td>
<select name="trainer"><?php echo $trainer; ?></select>
</td></tr>
<tr><td>Kurs:</td><td>
<select name="kurs"><?php echo $kurse; ?></select>
</td></tr>
<tr><td>Datum:</td><td>
<input type="text" name="datum" value="<?php echo date('Y-m-d H:i:s'); ?>">
</td></tr>
<tr><td>Dauer (Minuten):</td><td>
<input type="text" name="dauer" value="60">
</td></tr>
<tr><td></td><td>
<input type="submit" value="Speichern">
</td></tr>
</table>
</form>

<?php
/*
 * FORMULAR AUSWERTEN
 * Quelle: tutorialrepublic.com (create.php)
 *
 * Dieser Block läuft nur, wenn die Seite per POST aufgerufen wurde
 * (= das Formular oben wurde abgeschickt). Beim allerersten Aufruf
 * der Seite (GET) wird er komplett übersprungen, nur das leere
 * Formular oben wird angezeigt.
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Werte aus dem Formular holen. (int) für die Dropdown-IDs,
    // weil das Fremdschlüssel sein müssen; Datum/Dauer bleiben
    // als eingegebener Wert bzw. werden zu Zahl gecastet.
    $Mitglied = (int) $_POST['mitglied'];
    $Trainer  = (int) $_POST['trainer'];
    $Kurs     = (int) $_POST['kurs'];
    $datum    = $_POST['datum'];
    $dauer    = (int) $_POST['dauer'];

    // VALIDIERUNG
    // Für jedes Pflichtfeld ein empty()-Check. empty() ist wahr,
    // wenn die Variable leer, 0 oder gar nicht gesetzt ist.
    // "if (Bedingung) einzelneAnweisung;" ohne { } ist erlaubt,
    // weil nach dem if nur EINE Anweisung folgt.
    $fehler = "";
    if (empty($Mitglied)) $fehler .= "Kein Mitglied gewählt!<br>";
    if (empty($Trainer))  $fehler .= "Kein Trainer gewählt!<br>";
    if (empty($Kurs))     $fehler .= "Kein Kurs gewählt!<br>";
    if (empty($datum))    $fehler .= "Kein Datum gewählt!<br>";
    if (empty($dauer))    $fehler .= "Keine Dauer angegeben!<br>";

    if (!empty($fehler)) {
        // Falls mindestens ein Fehler gesammelt wurde, zeigen wir
        // ihn an und speichern NICHT.
        echo "<b>" . $fehler . "</b>";
    } else {
        /*
         * INSERT MIT PREPARED STATEMENT
         * Quelle: tutorialrepublic.com (create.php)
         *
         * Die ?-Platzhalter stehen an der Stelle, wo später echte
         * Werte eingesetzt werden. prepare() bereitet die Query vor,
         * OHNE die Werte schon einzusetzen (Schutz gegen SQL-Injection).
         */
        $stmt = $conn->prepare("INSERT INTO TERMINE
            (MITGLIEDER_idMITGLIEDER, TRAINER_idTRAINER, KURSE_idKURSE, TERMINEdatum, TERMINEdauer)
            VALUES (?, ?, ?, ?, ?)");

        /*
         * bind_param ordnet den ?-Platzhaltern echte Variablen zu.
         * Der erste Parameter ("iiisi") sagt PHP, welchen TYP jeder
         * Wert hat, in der exakt gleichen Reihenfolge wie die ?:
         *   i = integer (Mitglied, Trainer, Kurs, Dauer)
         *   s = string  (Datum)
         * Reihenfolge der Buchstaben MUSS zur Reihenfolge der
         * Variablen und der ?-Platzhalter oben passen.
         */
        $stmt->bind_param("iiisi", $Mitglied, $Trainer, $Kurs, $datum, $dauer);

        if ($stmt->execute()) {
            // Bei Erfolg: zur Liste weiterleiten, damit man das
            // Formular nicht bei einem Reload versehentlich nochmal
            // abschickt.
            header("location: list.php");
            exit();
        } else {
            echo "Fehler beim Speichern: " . $conn->error;
        }
    }
}

$conn->close();
?>

</body>
</html>
