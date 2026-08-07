<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once "config.php";

$q = trim($_GET['q'] ?? '');
$results = [];

if (strlen($q) >= 2) {
    $q_escaped = $conn->real_escape_string($q);

    // Search Users
    $resUsers = $conn->query("SELECT id, name, email, role FROM users WHERE name LIKE '%$q_escaped%' OR email LIKE '%$q_escaped%' LIMIT 4");
    if ($resUsers) {
        while ($u = $resUsers->fetch_assoc()) {
            $results[] = [
                'type' => 'user',
                'title' => htmlspecialchars($u['name']),
                'subtitle' => htmlspecialchars($u['email']) . ' (' . ucfirst($u['role']) . ')',
                'url' => ($u['role'] === 'admin' ? 'admin/users.php' : 'admin/users.php'),
                'icon' => 'bi-person-circle',
                'color' => '#2563eb'
            ];
        }
    }

    // Search Tasks
    $resTasks = $conn->query("SELECT id, title, status FROM tasks WHERE title LIKE '%$q_escaped%' OR description LIKE '%$q_escaped%' LIMIT 4");
    if ($resTasks) {
        while ($t = $resTasks->fetch_assoc()) {
            $results[] = [
                'type' => 'task',
                'title' => htmlspecialchars($t['title']),
                'subtitle' => 'Task Status: ' . ucfirst($t['status']),
                'url' => 'view_ticket.php?id=' . $t['id'],
                'icon' => 'bi-ticket-perforated',
                'color' => '#10b981'
            ];
        }
    }

    // Search Knowledge Base
    $resKb = $conn->query("SELECT id, title, category FROM knowledge WHERE title LIKE '%$q_escaped%' OR description LIKE '%$q_escaped%' LIMIT 4");
    if ($resKb) {
        while ($k = $resKb->fetch_assoc()) {
            $results[] = [
                'type' => 'knowledge',
                'title' => htmlspecialchars($k['title']),
                'subtitle' => 'Doc Category: ' . htmlspecialchars($k['category'] ?? 'General'),
                'url' => 'user/knowledge.php',
                'icon' => 'bi-book',
                'color' => '#8b5cf6'
            ];
        }
    }
}

ob_clean();
header('Content-Type: application/json');
echo json_encode($results);
