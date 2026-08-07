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

// Helper function to mark active sidebar links
function isActive($pageName) {
    global $current_path;
    return (strpos($current_path, $pageName) !== false) ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>upDate CRM — Modern Management Portal</title>
<!-- Google Fonts & Bootstrap Icons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?php echo $depth; ?>assets/css/style.css">
<style>
  .search-wrapper { position: relative; }
  .search-results-dropdown {
    position: absolute;
    top: 48px;
    left: 0;
    width: 320px;
    background: #ffffff;
    border: 1px solid var(--border-light);
    border-radius: 16px;
    box-shadow: 0 14px 35px rgba(0,0,0,0.15);
    z-index: 999;
    max-height: 380px;
    overflow-y: auto;
    display: none;
    padding: 8px 0;
  }
  .search-result-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    text-decoration: none;
    color: var(--text-dark);
    transition: background 0.15s;
  }
  .search-result-item:hover {
    background: #f1f5f9;
  }
  .search-result-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
  }
</style>
</head>
<body>
<div class="layout">
<aside class="sidebar">
  <div class="logoBox">
    <img src="<?php echo $depth; ?>assets/images/logo.png" class="miniLogo" alt="upDate CRM Logo"/>
    <div class="brand">
      <div class="brand-main">upDate CRM</div>
      <div class="brand-sub">upDt Technology Pvt. Ltd.</div>
    </div>
  </div>

  <nav class="sideLinks">
    <a href="<?php echo ($isAdmin ? $depth.'admin/dashboard.php' : $depth.'user/dashboard.php');?>" class="link<?php echo isActive('dashboard.php'); ?>">
      <i class="bi bi-grid-1x2-fill icon-side"></i>
      <span>Dashboard</span>
    </a>

    <?php if($isAdmin): ?>
      <a href="<?php echo $depth; ?>admin/users.php" class="link<?php echo isActive('users.php'); ?>">
        <i class="bi bi-people-fill icon-side"></i>
        <span>Users / Employees</span>
      </a>
      <a href="<?php echo $depth; ?>admin/tasks.php" class="link<?php echo isActive('tasks.php'); ?>">
        <i class="bi bi-check2-square icon-side"></i>
        <span>Task Management</span>
      </a>
      <a href="<?php echo $depth; ?>admin/notes.php" class="link<?php echo isActive('notes.php'); ?>">
        <i class="bi bi-journal-bookmark-fill icon-side"></i>
        <span>Notes & Reminders</span>
      </a>
      <a href="<?php echo $depth; ?>admin/announcements.php" class="link<?php echo isActive('announcements.php'); ?>">
        <i class="bi bi-megaphone-fill icon-side"></i>
        <span>Announcements</span>
      </a>
      <a href="<?php echo $depth; ?>admin/calendar.php" class="link<?php echo isActive('calendar.php'); ?>">
        <i class="bi bi-calendar3 icon-side"></i>
        <span>Calendar & Schedule</span>
      </a>
      <a href="<?php echo $depth; ?>admin/performance.php" class="link<?php echo isActive('performance.php'); ?>">
        <i class="bi bi-bar-chart-line-fill icon-side"></i>
        <span>Performance & Analytics</span>
      </a>
      <a href="<?php echo $depth; ?>admin/activity_log.php" class="link<?php echo isActive('activity_log.php'); ?>">
        <i class="bi bi-clock-history icon-side"></i>
        <span>Activity Log</span>
      </a>
      <a href="<?php echo $depth; ?>admin/knowledge.php" class="link<?php echo isActive('knowledge.php'); ?>">
        <i class="bi bi-book-half icon-side"></i>
        <span>Knowledge Base</span>
      </a>
      <a href="<?php echo $depth; ?>admin/whatsapp.php" class="link<?php echo isActive('whatsapp.php'); ?>">
        <i class="bi bi-whatsapp icon-side" style="color: #25d366;"></i>
        <span>WhatsApp Portal</span>
      </a>
      <a href="<?php echo $depth; ?>admin/gmail.php" class="link<?php echo isActive('gmail.php'); ?>">
        <i class="bi bi-envelope-at-fill icon-side" style="color: #ea4335;"></i>
        <span>Email Dispatcher</span>
      </a>
      <a href="<?php echo $depth; ?>admin/settings.php" class="link<?php echo isActive('settings.php'); ?>">
        <i class="bi bi-gear-wide-connected icon-side"></i>
        <span>System Settings</span>
      </a>
    <?php else: ?>
      <a href="<?php echo $depth; ?>user/tasks.php" class="link<?php echo isActive('tasks.php'); ?>">
        <i class="bi bi-check2-square icon-side"></i>
        <span>My Tasks</span>
      </a>
      <a href="<?php echo $depth; ?>user/notes.php" class="link<?php echo isActive('notes.php'); ?>">
        <i class="bi bi-journal-bookmark-fill icon-side"></i>
        <span>Personal Notes</span>
      </a>
      <a href="<?php echo $depth; ?>user/announcements.php" class="link<?php echo isActive('announcements.php'); ?>">
        <i class="bi bi-megaphone-fill icon-side"></i>
        <span>Announcements</span>
      </a>
      <a href="<?php echo $depth; ?>user/calendar.php" class="link<?php echo isActive('calendar.php'); ?>">
        <i class="bi bi-calendar3 icon-side"></i>
        <span>My Calendar</span>
      </a>
      <a href="<?php echo $depth; ?>user/performance.php" class="link<?php echo isActive('performance.php'); ?>">
        <i class="bi bi-bar-chart-line-fill icon-side"></i>
        <span>Analytics</span>
      </a>
      <a href="<?php echo $depth; ?>user/activity_log.php" class="link<?php echo isActive('activity_log.php'); ?>">
        <i class="bi bi-clock-history icon-side"></i>
        <span>Activity Log</span>
      </a>
      <a href="<?php echo $depth; ?>user/knowledge.php" class="link<?php echo isActive('knowledge.php'); ?>">
        <i class="bi bi-book-half icon-side"></i>
        <span>Knowledge Base</span>
      </a>
      <a href="<?php echo $depth; ?>user/profile.php" class="link<?php echo isActive('profile.php'); ?>">
        <i class="bi bi-person-circle icon-side"></i>
        <span>My Profile</span>
      </a>
      <a href="<?php echo $depth; ?>user/contact_admin.php" class="link<?php echo isActive('contact_admin.php'); ?>">
        <i class="bi bi-chat-left-dots-fill icon-side" style="color: #60a5fa;"></i>
        <span>Contact Admin</span>
      </a>
    <?php endif; ?>

    <a href="<?php echo $depth; ?>logout.php" class="link danger">
      <i class="bi bi-box-arrow-right icon-side"></i>
      <span>Logout</span>
    </a>
  </nav>
</aside>

<main class="content">
  <div class="topbar">
    <div class="welcome">
      <div class="avatar-sm">
        <?php echo strtoupper(substr($name, 0, 1)); ?>
      </div>
      <div>
        <div style="font-size: 15px; font-weight: 700; color: var(--text-dark);">
          Welcome back, <?php echo htmlspecialchars($name); ?> 👋
        </div>
        <div style="font-size: 12px; color: var(--text-muted); font-weight: 400;">
          Role: <strong style="text-transform: capitalize; color: var(--brand-primary);"><?php echo $role; ?></strong>
        </div>
      </div>
      <span class="badge">v1.0 Pro</span>
    </div>

    <div class="actions">
      <div class="search-wrapper">
        <i class="bi bi-search search-icon"></i>
        <input class="search" id="liveSearchInput" placeholder="Search tasks, notes, users..." autocomplete="off"/>
        <div class="search-results-dropdown" id="searchResultsDropdown"></div>
      </div>
      <a class="icon" href="#" title="Notifications"><i class="bi bi-bell-fill" style="color: #f59e0b;"></i></a>
      <a class="icon" href="<?php echo ($isAdmin ? $depth.'admin/whatsapp.php' : $depth.'user/contact_admin.php');?>" title="WhatsApp Messaging"><i class="bi bi-whatsapp" style="color: #25d366;"></i></a>
      <a class="icon" href="<?php echo ($isAdmin ? $depth.'admin/gmail.php' : $depth.'user/contact_admin.php');?>" title="Email Dispatcher"><i class="bi bi-envelope-at-fill" style="color: #ea4335;"></i></a>
      <a class="btn danger-btn" href="<?php echo $depth; ?>logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </div>
  <div class="page">

<script>
document.addEventListener("DOMContentLoaded", function() {
  const searchInput = document.getElementById("liveSearchInput");
  const dropdown = document.getElementById("searchResultsDropdown");
  const basePath = "<?php echo $depth; ?>";
  let searchTimer;

  if (searchInput) {
    searchInput.addEventListener("input", function() {
      clearTimeout(searchTimer);
      const query = this.value.trim();

      if (query.length < 2) {
        dropdown.style.display = "none";
        dropdown.innerHTML = "";
        return;
      }

      searchTimer = setTimeout(() => {
        fetch(basePath + "api_search.php?q=" + encodeURIComponent(query))
          .then(res => res.json())
          .then(data => {
            if (!data || data.length === 0) {
              dropdown.innerHTML = '<div style="padding:12px; text-align:center; color:#777; font-size:13px;">No matching results found</div>';
            } else {
              let html = '';
              data.forEach(item => {
                html += `
                  <a href="${basePath}${item.url}" class="search-result-item">
                    <div class="search-result-icon" style="background:${item.color}15; color:${item.color};">
                      <i class="bi ${item.icon}"></i>
                    </div>
                    <div>
                      <div style="font-weight:600; font-size:13.5px;">${item.title}</div>
                      <div style="font-size:11.5px; color:#64748b;">${item.subtitle}</div>
                    </div>
                  </a>
                `;
              });
              dropdown.innerHTML = html;
            }
            dropdown.style.display = "block";
          })
          .catch(() => {
            dropdown.style.display = "none";
          });
      }, 250);
    });

    document.addEventListener("click", function(e) {
      if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.style.display = "none";
      }
    });
  }
});
</script>
