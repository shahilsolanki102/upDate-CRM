<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once "config.php";

if (!isset($_SESSION['uid'])) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$uid    = (int)$_SESSION['uid'];
$role   = $_SESSION['role'] ?? 'user';
$action = $_POST['action'] ?? $_GET['action'] ?? 'fetch';

// Ensure notifications table and is_read column exist
$conn->query("
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");
$conn->query("ALTER TABLE notifications ADD COLUMN IF NOT EXISTS is_read TINYINT(1) DEFAULT 0");

if ($action === 'fetch') {
    $whereClause = ($role === 'admin') ? "(user_id = $uid OR user_id = 1 OR user_id = 0)" : "(user_id = $uid OR user_id = 0)";
    $res = $conn->query("SELECT * FROM notifications WHERE $whereClause ORDER BY id DESC LIMIT 10");
    $list = [];
    $unreadCnt = 0;

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $isRead = isset($row['is_read']) ? (int)$row['is_read'] : 1;
            if ($isRead == 0) $unreadCnt++;

            // Compute smart target URL based on notification message and user role
            $msg = $row['message'];
            $url = ($role === 'admin') ? 'admin/dashboard.php' : 'user/dashboard.php';

            if (stripos($msg, 'Task Ticket') !== false || stripos($msg, 'Task') !== false) {
                $url = ($role === 'admin') ? 'admin/tasks.php' : 'user/tasks.php';
            } elseif (stripos($msg, 'Announcement') !== false) {
                $url = ($role === 'admin') ? 'admin/announcements.php' : 'user/announcements.php';
            } elseif (stripos($msg, 'Punch-Out') !== false || stripos($msg, 'Shift') !== false) {
                $url = ($role === 'admin') ? 'admin/attendance.php' : 'user/dashboard.php';
            } elseif (stripos($msg, 'Profile') !== false) {
                $url = 'user/profile.php';
            }

            $list[] = [
                'id' => $row['id'],
                'message' => htmlspecialchars($row['message']),
                'is_read' => $isRead,
                'url' => $url,
                'time' => date('d M, h:i A', strtotime($row['created_at']))
            ];
        }
    }

    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'unread' => $unreadCnt,
        'notifications' => $list
    ]);
    exit;
}

if ($action === 'mark_read') {
    if ($role === 'admin') {
        $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $uid OR user_id = 1 OR user_id = 0");
    } else {
        $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $uid OR user_id = 0");
    }

    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
    exit;
}

ob_clean();
header('Content-Type: application/json');
echo json_encode(['success' => false, 'error' => 'Invalid action']);
