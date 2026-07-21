<!DOCTYPE html>
<html>
<head>
    <title>Termine</title>
</head>
<body>

<?php
/*
 * DATENBANKVERBINDUNG
 * Quelle: tutorialrepublic.com (config.php, Object-Oriented-Tab)
 * $servername/$username/$password/$dbname sind deine Zugangsdaten,
 * new mysqli(...) baut die eigentliche Verbindung auf.
 */
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "LAP_Exercise";

$conn = new mysqli($servername, $username, $password, $dbname);

// Falls die Verbindung fehlschlägt (falscher Username, DB existiert nicht, etc.),
// bricht das Skript hier sofort ab und zeigt die Fehlermeldung.
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/*
 * DROPDOWN 1: MITGLIEDER
 * Quelle: w3schools.com/php/php_mysql_select.asp
 * Wir bauen einen String mit <option>-Tags, gefüllt aus der DB.
 * $mitglieder startet mit "ALLE" als Standardoption (kein Filter),
 * dann wird für jede Zeile aus der Tabelle eine weitere <option> angehängt.
 */
$mitglieder = '<option value="">ALLE</option>';
$sql = "SELECT idMITGLIEDER, MITGLIEDERname FROM MITGLIEDER";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    // .= hängt Text an das Ende der Variable an (nicht = , das würde überschreiben)
    $mitglieder .= '<option value="' . $row["idMITGLIEDER"] . '">' . $row["MITGLIEDERname"] . '</option>';
}

/*
 * DROPDOWN 2: TRAINER
 * Exakt dasselbe Prinzip wie oben, nur andere Tabelle/Spalten.
 */
$trainer = '<option value="">ALLE</option>';
$sql = "SELECT idTRAINER, TRAINERname FROM TRAINER";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $trainer .= '<option value="' . $row["idTRAINER"] . '">' . $row["TRAINERname"] . '</option>';
}
?>

<!--
  FILTERFORMULAR
  Quelle: w3schools.com/php/php_forms.asp
  method="post" schickt die Auswahl unsichtbar im Hintergrund (nicht in der URL).
  action="" bedeutet: das Formular schickt sich selbst wieder an diese Datei.
-->
<form action="" method="post">
<select name="filter_mitglied"><?php echo $mitglieder; ?></select>
<select name="filter_trainer"><?php echo $trainer; ?></select>
<input type="submit" value="filtern">
</form>

<?php
/*
 * FILTERWERTE AUSLESEN
 * isset() prüft: wurde das Formular überhaupt schon abgeschickt?
 * Beim allerersten Seitenaufruf ist $_POST leer, dann wird 0 verwendet
 * (= "kein Filter gesetzt", zeigt später alle Termine).
 * (int) wandelt den Text aus dem Formular in eine echte Zahl um.
 */
$filterMitglied = isset($_POST['filter_mitglied']) ? (int) $_POST['filter_mitglied'] : 0;
$filterTrainer  = isset($_POST['filter_trainer']) ? (int) $_POST['filter_trainer'] : 0;

/*
 * HAUPTABFRAGE MIT JOIN
 * Quelle JOIN-Syntax: w3schools.com/mysql/mysql_join_inner.asp
 *
 * TERMINE ist die "Kreuzung": die einzige Tabelle, die alle drei
 * Fremdschlüssel (zu MITGLIEDER, TRAINER, KURSE) gleichzeitig hat.
 * Deshalb steht sie nach FROM, und jede Nachschlagetabelle wird
 * über ihren Fremdschlüssel in TERMINE angebunden:
 *   Primärschlüssel der Nachschlagetabelle = Fremdschlüssel in TERMINE
 *
 * TERMINEdauer/60 * KURSEpreis AS GESAMT:
 * Dauer steht in Minuten, /60 macht daraus Stunden, mal Stundenpreis
 * ergibt den Gesamtpreis für diesen Termin. AS GESAMT vergibt einen
 * Spaltennamen für diese berechnete Spalte, damit wir sie später mit
 * $row["GESAMT"] auslesen können.
 */
$sql = "SELECT MITGLIEDERname, TRAINERname, KURSEbezeichnung, KURSEpreis, TERMINEdauer,
        TERMINEdauer/60 * KURSEpreis AS GESAMT
        FROM TERMINE
        INNER JOIN MITGLIEDER ON MITGLIEDER.idMITGLIEDER = TERMINE.MITGLIEDER_idMITGLIEDER
        INNER JOIN TRAINER ON TRAINER.idTRAINER = TERMINE.TRAINER_idTRAINER
        INNER JOIN KURSE ON KURSE.idKURSE = TERMINE.KURSE_idKURSE
        WHERE 1=1";

/*
 * DYNAMISCHES FILTERN
 * Quelle: Medium-Artikel "How to Create and Filter Records with PHP
 * and MySQL from multiple table"
 *
 * WHERE 1=1 ist immer wahr und ändert an sich nichts an der Abfrage -
 * es ist nur ein Ankerpunkt, an den wir beliebig viele AND-Bedingungen
 * anhängen können, ohne uns fragen zu müssen "ist das die erste
 * Bedingung oder nicht".
 * Nur wenn tatsächlich gefiltert werden soll (Wert ist nicht 0),
 * wird die jeweilige Zeile überhaupt drangehängt.
 */
if (!empty($filterMitglied)) $sql .= " AND MITGLIEDER.idMITGLIEDER=" . $filterMitglied;
if (!empty($filterTrainer))  $sql .= " AND TRAINER.idTRAINER=" . $filterTrainer;

/*
 * ABFRAGE AUSFÜHREN UND AUSGEBEN
 * Quelle: tutorialrepublic.com (index.php) für num_rows-Check + Schleife
 * Quelle laufende Summe: w3resource.com/php-exercises/php-for-loop-exercise-2.php
 *
 * num_rows > 0 prüft, ob überhaupt Ergebnisse da sind, bevor wir
 * anfangen, eine Summe zu bilden oder eine Schleife zu starten.
 */
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // $summe wird VOR der Schleife auf 0 gesetzt, damit wir ab der
    // ersten Zeile draufaddieren können.
    $summe = 0;

    // fetch_assoc() holt jeweils EINE Zeile als assoziatives Array
    // ($row["Spaltenname"] => Wert). Die Schleife läuft, bis keine
    // Zeilen mehr übrig sind (fetch_assoc gibt dann null zurück).
    while ($row = $result->fetch_assoc()) {
        echo $row["MITGLIEDERname"] . " - " . $row["TRAINERname"] . " - "
            . $row["KURSEbezeichnung"] . " - " . $row["GESAMT"] . "<br>";

        // += addiert den aktuellen GESAMT-Wert zur laufenden Summe dazu
        $summe += $row["GESAMT"];
    }

    echo "Gesamt: " . $summe;
} else {
    echo "0 results";
}

$conn->close();
?>
</body>
</html>
