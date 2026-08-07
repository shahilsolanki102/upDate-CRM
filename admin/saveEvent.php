<?php
require_once "../config.php";

if ($_POST['title']) {
    $stmt = $conn->prepare("INSERT INTO events (title, start, end) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['title'], $_POST['start'], $_POST['end']);
    $stmt->execute();
}
