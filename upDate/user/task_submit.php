<?php
require_once "../config.php";
$requireLogin = 'user';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: tasks.php"); exit;
}

$task_id = (int)($_POST['task_id'] ?? 0);
$remarks = trim($_POST['remarks'] ?? '');
$uid     = (int)($_SESSION['uid'] ?? 0);

if (!$task_id || !$uid) { die("Invalid request."); }

// File checks
if (!isset($_FILES['submission']) || $_FILES['submission']['error'] !== UPLOAD_ERR_OK) {
  die("Please upload a PDF.");
}

$f = $_FILES['submission'];
// basic validation
$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
if ($ext !== 'pdf') { die("Only PDF allowed."); }
if ($f['size'] > 10*1024*1024) { die("Max 10MB allowed."); }

$uniq = $task_id . "_" . time() . "_" . bin2hex(random_bytes(4)) . ".pdf";
$dest = __DIR__ . "/../uploads/tasks/" . $uniq;

if (!move_uploaded_file($f['tmp_name'], $dest)) {
  die("Upload failed.");
}

// Update task as submitted
$sql = "UPDATE tasks 
        SET submission_file=?, submitted_at=NOW(), status='complete', remarks=? 
        WHERE id=? AND user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $uniq, $remarks, $task_id, $uid);
$stmt->execute();
$stmt->close();

header("Location: tasks.php");
