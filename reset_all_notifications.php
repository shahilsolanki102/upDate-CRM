<?php
require_once "config.php";

// Mark all notifications as read in MySQL
$conn->query("UPDATE notifications SET is_read = 1");

$res = $conn->query("SELECT * FROM notifications");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        echo "ID: " . $r['id'] . " | UserID: " . $r['user_id'] . " | Msg: " . $r['message'] . " | IsRead: " . $r['is_read'] . "\n";
    }
}
?>
