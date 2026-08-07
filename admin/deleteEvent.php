<?php
require_once "../config.php";

if ($_POST['id']) {
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
}
