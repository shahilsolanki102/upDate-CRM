<?php
require_once "../config.php";

$requireLogin = 'user';
include __DIR__."/../includes/header.php";

if (!isset($_SESSION['uid'])) {
    header("Location: ../user_login.php");
    exit;
}

$uid = $_SESSION['uid'];

// Tasks & Notes Count (supports user_id or assigned_to)
$tasksRes = $conn->query("SELECT COUNT(*) c FROM tasks WHERE user_id=$uid OR assigned_to=$uid");
$tasks    = $tasksRes ? ($tasksRes->fetch_assoc()['c'] ?? 0) : 0;

$notesRes = $conn->query("SELECT COUNT(*) c FROM notes WHERE user_id=$uid");
$notes    = $notesRes ? ($notesRes->fetch_assoc()['c'] ?? 0) : 0;
?>

<div class="grid">
  <div class="card">
    <h3>My Tasks</h3>
    <div class="metric"><?php echo $tasks; ?></div>
  </div>
  <div class="card">
    <h3>My Notes</h3>
    <div class="metric"><?php echo $notes; ?></div>
  </div>
</div>

<div class="card">
  <h3>Welcome to Employee Portal</h3>
  <p style="color:#666; margin-top:6px;">Manage your assigned tasks, notes, announcements, and track your activity logs.</p>
</div>

<?php include __DIR__."/../includes/footer.php"; ?>
