<?php
require_once "config.php";

header('Content-Type: application/json');

if (!isset($_SESSION['uid'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$uid    = (int)$_SESSION['uid'];
$action = $_POST['action'] ?? $_GET['action'] ?? 'fetch';

// Ensure notifications table exists
$conn->query("
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

if ($action === 'fetch') {
    $res = $conn->query("SELECT * FROM notifications WHERE user_id = $uid ORDER BY id DESC LIMIT 10");
    $list = [];
    $unreadCnt = 0;

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if ($row['is_read'] == 0) $unreadCnt++;
            $list[] = [
                'id' => $row['id'],
                'message' => htmlspecialchars($row['message']),
                'is_read' => (int)$row['is_read'],
                'time' => date('d M, h:i A', strtotime($row['created_at']))
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'unread' => $unreadCnt,
        'notifications' => $list
    ]);
    exit;
}

if ($action === 'mark_read') {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $uid");
    echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
