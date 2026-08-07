<?php
require_once "config.php";

header('Content-Type: application/json');

if (!isset($_SESSION['uid'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$uid    = $_SESSION['uid'];
$uname  = $_SESSION['name'] ?? 'Employee';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Ensure attendance_logs table exists
$conn->query("
CREATE TABLE IF NOT EXISTS attendance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    work_mode VARCHAR(20) DEFAULT 'remote',
    clock_in DATETIME NOT NULL,
    clock_out DATETIME NULL,
    total_minutes INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'active',
    tasks_done TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

// 1. Get Current Status
if ($action === 'status') {
    $res = $conn->query("SELECT * FROM attendance_logs WHERE user_id=$uid AND status='active' ORDER BY id DESC LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $activeLog = $res->fetch_assoc();
        echo json_encode([
            'success'   => true,
            'punchedIn' => true,
            'clockIn'   => date('h:i A', strtotime($activeLog['clock_in'])),
            'mode'      => ucfirst($activeLog['work_mode']),
            'logId'     => $activeLog['id']
        ]);
    } else {
        echo json_encode([
            'success'   => true,
            'punchedIn' => false
        ]);
    }
    exit;
}

// 2. Punch In
if ($action === 'punch_in') {
    $mode = $_POST['mode'] ?? 'remote';
    
    // Close any previous active shifts
    $conn->query("UPDATE attendance_logs SET status='completed', clock_out=NOW() WHERE user_id=$uid AND status='active'");

    $stmt = $conn->prepare("INSERT INTO attendance_logs (user_id, work_mode, clock_in, status) VALUES (?, ?, NOW(), 'active')");
    $stmt->bind_param("is", $uid, $mode);
    $stmt->execute();
    $stmt->close();

    $logMsg = "Started work shift ($mode mode)";
    $conn->query("INSERT INTO activity_log (user_id, action, created_at) VALUES ($uid, '$logMsg', NOW())");

    echo json_encode(['success' => true, 'message' => 'Punched In successfully!']);
    exit;
}

// 3. Punch Out
if ($action === 'punch_out') {
    $res = $conn->query("SELECT * FROM attendance_logs WHERE user_id=$uid AND status='active' ORDER BY id DESC LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $log = $res->fetch_assoc();
        $logId = $log['id'];
        $clockIn = new DateTime($log['clock_in']);
        $clockOut = new DateTime();
        $diff = $clockIn->diff($clockOut);
        $totalMins = ($diff->h * 60) + $diff->i;

        $stmt = $conn->prepare("UPDATE attendance_logs SET clock_out=NOW(), total_minutes=?, status='completed' WHERE id=?");
        $stmt->bind_param("ii", $totalMins, $logId);
        $stmt->execute();
        $stmt->close();

        $hoursStr = floor($totalMins / 60) . "h " . ($totalMins % 60) . "m";
        $logMsg = "Ended work shift (Worked: $hoursStr)";
        $conn->query("INSERT INTO activity_log (user_id, action, created_at) VALUES ($uid, '$logMsg', NOW())");

        echo json_encode(['success' => true, 'message' => "Punched Out! Total work duration: $hoursStr"]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No active shift found']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
