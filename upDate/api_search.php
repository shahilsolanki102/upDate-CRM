<?php
require_once "config.php";

header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    echo json_encode([]);
    exit;
}

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$isAdmin = ($_SESSION['role'] === 'admin');
$uid     = $_SESSION['uid'] ?? 0;
$results = [];

$searchTerm = "%" . $conn->real_escape_string($q) . "%";

// 1. Search Users (Admin only)
if ($isAdmin) {
    $userRes = $conn->query("SELECT id, name, email, department FROM users WHERE name LIKE '$searchTerm' OR email LIKE '$searchTerm' OR department LIKE '$searchTerm' LIMIT 4");
    if ($userRes) {
        while ($u = $userRes->fetch_assoc()) {
            $results[] = [
                'type'     => 'user',
                'icon'     => 'bi-person-badge-fill',
                'color'    => '#3b82f6',
                'title'    => $u['name'],
                'subtitle' => $u['email'] . ($u['department'] ? " (" . $u['department'] . ")" : ""),
                'url'      => 'admin/users.php'
            ];
        }
    }
}

// 2. Search Tasks
if ($isAdmin) {
    $taskRes = $conn->query("SELECT id, title, status, due_date FROM tasks WHERE title LIKE '$searchTerm' OR description LIKE '$searchTerm' LIMIT 4");
    $taskUrl = 'admin/tasks.php';
} else {
    $taskRes = $conn->query("SELECT id, title, status, due_date FROM tasks WHERE (user_id=$uid OR assigned_to=$uid) AND (title LIKE '$searchTerm' OR description LIKE '$searchTerm') LIMIT 4");
    $taskUrl = 'user/tasks.php';
}

if ($taskRes) {
    while ($t = $taskRes->fetch_assoc()) {
        $results[] = [
            'type'     => 'task',
            'icon'     => 'bi-check2-circle',
            'color'    => '#10b981',
            'title'    => $t['title'],
            'subtitle' => "Status: " . ucfirst($t['status'] ?? 'pending') . ($t['due_date'] ? " | Due: " . $t['due_date'] : ""),
            'url'      => $taskUrl
        ];
    }
}

// 3. Search Notes
if ($isAdmin) {
    $noteRes = $conn->query("SELECT id, note, created_at FROM notes WHERE note LIKE '$searchTerm' LIMIT 4");
    $noteUrl = 'admin/notes.php';
} else {
    $noteRes = $conn->query("SELECT id, note, created_at FROM notes WHERE user_id=$uid AND note LIKE '$searchTerm' LIMIT 4");
    $noteUrl = 'user/notes.php';
}

if ($noteRes) {
    while ($n = $noteRes->fetch_assoc()) {
        $text = $n['note'] ?? '';
        $shortText = strlen($text) > 40 ? substr($text, 0, 40) . "..." : $text;
        $results[] = [
            'type'     => 'note',
            'icon'     => 'bi-journal-text',
            'color'    => '#f59e0b',
            'title'    => $shortText,
            'subtitle' => "Created: " . $n['created_at'],
            'url'      => $noteUrl
        ];
    }
}

echo json_encode($results);
