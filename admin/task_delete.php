<?php
require_once "../config.php";
$requireLogin = 'admin';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
  // જો submission ફાઇલ હોય તો optional delete કરી શકીએ
  $q = $conn->prepare("SELECT submission_file FROM tasks WHERE id=?");
  $q->bind_param("i", $id); $q->execute();
  $file = ($q->get_result()->fetch_assoc())['submission_file'] ?? null;
  $q->close();

  $stmt = $conn->prepare("DELETE FROM tasks WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();

  if ($file && file_exists(__DIR__."/../uploads/tasks/".$file)) {
    @unlink(__DIR__."/../uploads/tasks/".$file);
  }
}

header("Location: tasks.php");
