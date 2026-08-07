<?php
$conn = new mysqli('localhost', 'root', '', 'updt_crm');
if ($conn->connect_error) die("DB err: " . $conn->connect_error);

$resT = $conn->query("SHOW TABLES");
while ($rT = $resT->fetch_array()) {
    $t = $rT[0];
    echo "=== TABLE: $t ===\n";
    $res = $conn->query("SHOW COLUMNS FROM `$t`");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo "  " . $r['Field'] . " (" . $r['Type'] . ")\n";
        }
    }
    echo "\n";
}
?>
