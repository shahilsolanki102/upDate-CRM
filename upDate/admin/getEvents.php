<?php
require_once "../config.php";

$res = $conn->query("SELECT id, title, start, end FROM events");
$events = [];
while ($row = $res->fetch_assoc()) {
    $events[] = $row;
}
header('Content-Type: application/json');
echo json_encode($events);
