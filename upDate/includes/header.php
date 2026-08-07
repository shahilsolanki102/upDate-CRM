<?php
require_once __DIR__ . "/../config.php";

// Redirect if not logged in
if (!isset($_SESSION['role'])) { 
    header("Location: ../user_login.php"); 
    exit; 
}

$role     = $_SESSION['role']; 
$name     = $_SESSION['name'] ?? ($role === 'admin' ? 'Admin' : 'User');
$isAdmin  = ($role === 'admin');

// 🔐 Role-based restriction
$current_path = $_SERVER['PHP_SELF'];

if (!$isAdmin && strpos($current_path, '/admin/') !== false) {
    header("Location: ../user/dashboard.php");
    exit;
}

if ($isAdmin && strpos($current_path, '/user/') !== false) {
    header("Location: ../admin/dashboard.php");
    exit;
}

// Calculate relative base path dynamically
$depth = (strpos($current_path, '/admin/') !== false || strpos($current_path, '/user/') !== false) ? '../' : './';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>upDate CRM — Dashboard</title>
<link rel="stylesheet" href="<?php echo $depth; ?>assets/css/style.css">
</head>
<body>
<div class="layout">
<aside class="sidebar">
  <div class="logoBox">
    <img src="<?php echo $depth; ?>assets/images/logo.png" class="miniLogo" alt="logo"/>
    <div class="brand">
      <div class="brand-main">upDate CRM</div>
      <div class="brand-sub">upDt Technology Pvt. Ltd.</div>
    </div>
  </div>
  <nav class="sideLinks">
    <a href="<?php echo ($isAdmin ? $depth.'admin/dashboard.php' : $depth.'user/dashboard.php');?>" class="link">Dashboard</a>

    <?php if($isAdmin): ?>
      <a href="<?php echo $depth; ?>admin/users.php" class="link">Users / Employees</a>
      <a href="<?php echo $depth; ?>admin/tasks.php" class="link">Tasks</a>
      <a href="<?php echo $depth; ?>admin/notes.php" class="link">Notes</a>
      <a href="<?php echo $depth; ?>admin/announcements.php" class="link">Announcements</a>
      <a href="<?php echo $depth; ?>admin/calendar.php" class="link">Calendar / Schedule</a>
      <a href="<?php echo $depth; ?>admin/performance.php" class="link">Performance / Analytics</a>
      <a href="<?php echo $depth; ?>admin/activity_log.php" class="link">Activity Log</a>
      <a href="<?php echo $depth; ?>admin/knowledge.php" class="link">Knowledge Base</a>
      <a href="<?php echo $depth; ?>admin/whatsapp.php" class="link">WhatsApp</a>
      <a href="<?php echo $depth; ?>admin/gmail.php" class="link">Gmail</a>
      <a href="<?php echo $depth; ?>admin/settings.php" class="link">Settings</a>
    <?php else: ?>
      <a href="<?php echo $depth; ?>user/tasks.php" class="link">Tasks</a>
      <a href="<?php echo $depth; ?>user/notes.php" class="link">Notes</a>
      <a href="<?php echo $depth; ?>user/announcements.php" class="link">Announcements</a>
      <a href="<?php echo $depth; ?>user/calendar.php" class="link">Calendar</a>
      <a href="<?php echo $depth; ?>user/performance.php" class="link">Performance</a>
      <a href="<?php echo $depth; ?>user/activity_log.php" class="link">Activity</a>
      <a href="<?php echo $depth; ?>user/knowledge.php" class="link">Knowledge Base</a>
      <a href="<?php echo $depth; ?>user/profile.php" class="link">Profile</a>
    <?php endif; ?>

    <a href="<?php echo $depth; ?>logout.php" class="link danger">Logout</a>
  </nav>
</aside>

<main class="content">
  <div class="topbar">
    <div class="welcome">
      Welcome, <?php echo htmlspecialchars($name); ?> 
      <span class="badge">v1.0</span>
    </div>
    <div class="actions">
      <input class="search" placeholder="Search users, notes..."/>
      <a class="icon" href="#" title="Notifications">🔔</a>
      <a class="icon" href="<?php echo ($isAdmin ? $depth.'admin/whatsapp.php' : $depth.'user/contact_admin.php');?>" title="WhatsApp">🟢</a>
      <a class="icon" href="<?php echo ($isAdmin ? $depth.'admin/gmail.php' : $depth.'user/contact_admin.php');?>" title="Email">✉️</a>
      <a class="btn" href="<?php echo $depth; ?>logout.php">Logout</a>
    </div>
  </div>
  <div class="page">
