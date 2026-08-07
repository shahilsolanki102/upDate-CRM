<?php
require_once "../config.php";
$requireLogin = 'admin';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: tasks.php"); exit;
}

$id         = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$user_id    = (int)$_POST['user_id'];
$title      = trim($_POST['title'] ?? '');
$desc       = trim($_POST['description'] ?? '');
$due_date   = $_POST['due_date'] ?: null;
$admin_id   = $_SESSION['uid'] ?? null; // admin id

if (!$user_id || $title === '') {
  die("Missing required fields.");
}

if ($id > 0) {
  $stmt = $conn->prepare("UPDATE tasks SET user_id=?, title=?, description=?, due_date=? WHERE id=?");
  $stmt->bind_param("isssi", $user_id, $title, $desc, $due_date, $id);
  $stmt->execute();
  $stmt->close();
} else {
  $stmt = $conn->prepare("INSERT INTO tasks (user_id, created_by, title, description, status, due_date, created_at) VALUES (?,?,?,?,'pending',?,NOW())");
  $stmt->bind_param("iisss", $user_id, $admin_id, $title, $desc, $due_date);
  $stmt->execute();
  $stmt->close();
}

header("Location: tasks.php");
