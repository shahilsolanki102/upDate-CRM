<?php
require_once "config.php";

$res = $conn->query("SELECT id, name, email, profile_pic FROM users");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        echo "ID: " . $r['id'] . " | Name: " . $r['name'] . " | Pic: " . ($r['profile_pic'] ?: 'NULL') . "\n";
    }
}
?>
