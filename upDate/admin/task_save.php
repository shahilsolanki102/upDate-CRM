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
$priority   = trim($_POST['priority'] ?? 'Normal');
$due_date   = $_POST['due_date'] ?: null;
$admin_id   = $_SESSION['uid'] ?? 1;

if (!$user_id || $title === '') {
  die("Missing required fields.");
}

// Ensure ticket_id and priority columns exist
$conn->query("ALTER TABLE tasks ADD COLUMN IF NOT EXISTS ticket_id VARCHAR(50) NULL");
$conn->query("ALTER TABLE tasks ADD COLUMN IF NOT EXISTS priority VARCHAR(20) DEFAULT 'Normal'");

if ($id > 0) {
  $stmt = $conn->prepare("UPDATE tasks SET user_id=?, assigned_to=?, title=?, description=?, priority=?, due_date=? WHERE id=?");
  $stmt->bind_param("iissssi", $user_id, $user_id, $title, $desc, $priority, $due_date, $id);
  $stmt->execute();
  $stmt->close();
  
  $conn->query("INSERT INTO activity_log (user_id, action, created_at) VALUES ($admin_id, 'Updated Task Ticket for user ID $user_id', NOW())");
} else {
  // Generate Unique Ticket ID (Format: TK-YYYY-XXXXX)
  $ticket_id = "TK-" . date('Y') . "-" . rand(10000, 99999);

  $stmt = $conn->prepare("INSERT INTO tasks (ticket_id, user_id, assigned_to, created_by, title, description, priority, status, due_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())");
  $stmt->bind_param("siiisssss", $ticket_id, $user_id, $user_id, $admin_id, $title, $desc, $priority, $due_date);
  $stmt->execute();
  $stmt->close();

  // Create notification for assigned employee
  $conn->query("INSERT INTO notifications (user_id, message) VALUES ($user_id, '🎟️ New Task Ticket #$ticket_id assigned to you: $title')");
  $conn->query("INSERT INTO activity_log (user_id, action, created_at) VALUES ($admin_id, 'Generated new Task Ticket #$ticket_id for user ID $user_id', NOW())");
}

header("Location: tasks.php");
exit;
