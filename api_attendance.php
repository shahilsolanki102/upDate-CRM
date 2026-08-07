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

// Ensure tables exist
$conn->query("
CREATE TABLE IF NOT EXISTS attendance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    work_mode VARCHAR(20) DEFAULT 'remote',
    clock_in DATETIME NOT NULL,
    clock_out DATETIME NULL,
    total_minutes INT DEFAULT 0,
    status VARCHAR(30) DEFAULT 'active',
    early_reason TEXT NULL,
    tasks_done TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

// Auto Midnight 11:59 PM Punch Out check for past shifts or past 11:59 PM
$conn->query("
    UPDATE attendance_logs 
    SET clock_out = CONCAT(DATE(clock_in), ' 23:59:00'),
        total_minutes = TIMESTAMPDIFF(MINUTE, clock_in, CONCAT(DATE(clock_in), ' 23:59:00')),
        status = 'completed_auto'
    WHERE status = 'active' AND (DATE(clock_in) < CURDATE() OR (CURDATE() = DATE(clock_in) AND CURRENT_TIME() >= '23:59:00'))
");

// Check current server shift hours (9:00 AM to 5:00 PM IST)
$currentHour = (int)date('H'); // 0-23 in IST
$isShiftHours = ($currentHour >= 9 && $currentHour < 17); // True strictly between 09:00 and 16:59 IST

// 1. Get Current Status
if ($action === 'status') {
    $res = $conn->query("SELECT * FROM attendance_logs WHERE user_id=$uid AND status IN ('active', 'pending_approval') ORDER BY id DESC LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $activeLog = $res->fetch_assoc();
        echo json_encode([
            'success'          => true,
            'punchedIn'        => true,
            'pendingApproval' => ($activeLog['status'] === 'pending_approval'),
            'isShiftHours'     => $isShiftHours,
            'clockIn'          => date('h:i A', strtotime($activeLog['clock_in'])),
            'mode'             => ucfirst($activeLog['work_mode']),
            'logId'            => $activeLog['id']
        ]);
    } else {
        echo json_encode([
            'success'      => true,
            'punchedIn'    => false,
            'isShiftHours' => $isShiftHours
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

// 3. Punch Out (Instant after 5:00 PM IST; Reason & Admin approval between 9:00 AM & 5:00 PM IST)
if ($action === 'punch_out') {
    $res = $conn->query("SELECT * FROM attendance_logs WHERE user_id=$uid AND status IN ('active', 'pending_approval') ORDER BY id DESC LIMIT 1");
    if (!$res || $res->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'No active work shift found']);
        exit;
    }

    $log = $res->fetch_assoc();
    $logId = $log['id'];

    // Strictly between 9:00 AM and 5:00 PM IST -> Requires Admin Approval & Reason
    if ($isShiftHours && $log['status'] !== 'pending_approval') {
        $reason = trim($_POST['reason'] ?? '');
        if (empty($reason)) {
            echo json_encode([
                'success'          => false,
                'requiresReason'   => true,
                'error'            => 'Official shift hours (9:00 AM to 5:00 PM). Emergency punch-out requires Admin approval and a valid reason.'
            ]);
            exit;
        }

        // Mark as pending_approval and notify Admin
        $stmt = $conn->prepare("UPDATE attendance_logs SET status='pending_approval', early_reason=? WHERE id=?");
        $stmt->bind_param("si", $reason, $logId);
        $stmt->execute();
        $stmt->close();

        // Notify Admin
        $conn->query("INSERT INTO notifications (user_id, message) VALUES (1, '⚠️ Early Punch-Out Request from $uname: Reason: $reason')");
        $conn->query("INSERT INTO activity_log (user_id, action, created_at) VALUES ($uid, 'Requested early Punch-Out from Admin (Reason: $reason)', NOW())");

        echo json_encode([
            'success'          => true,
            'pendingApproval' => true,
            'message'          => 'Emergency Punch-Out request submitted to Admin! Waiting for Admin approval.'
        ]);
        exit;
    }

    if ($log['status'] === 'pending_approval') {
        echo json_encode([
            'success'          => false,
            'pendingApproval' => true,
            'error'            => 'Your early Punch-Out request is pending Admin approval.'
        ]);
        exit;
    }

    // Direct Instant Punch Out (After 5:00 PM IST or Before 9:00 AM IST)
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

    echo json_encode(['success' => true, 'message' => "Punched Out successfully! Total work duration: $hoursStr"]);
    exit;
}

// 4. Admin Approve Early Punch-Out
if ($action === 'admin_approve') {
    if (($_SESSION['role'] ?? '') !== 'admin') {
        echo json_encode(['success' => false, 'error' => 'Admin access required']);
        exit;
    }

    $logId = (int)($_POST['log_id'] ?? 0);
    $res = $conn->query("SELECT * FROM attendance_logs WHERE id=$logId");
    if ($res && $res->num_rows > 0) {
        $log = $res->fetch_assoc();
        $targetUid = $log['user_id'];
        $clockIn = new DateTime($log['clock_in']);
        $clockOut = new DateTime();
        $diff = $clockIn->diff($clockOut);
        $totalMins = ($diff->h * 60) + $diff->i;

        $stmt = $conn->prepare("UPDATE attendance_logs SET clock_out=NOW(), total_minutes=?, status='completed' WHERE id=?");
        $stmt->bind_param("ii", $totalMins, $logId);
        $stmt->execute();
        $stmt->close();

        $conn->query("INSERT INTO notifications (user_id, message) VALUES ($targetUid, '✓ Admin Approved your Early Punch-Out request.')");
        $conn->query("INSERT INTO activity_log (user_id, action, created_at) VALUES (1, 'Approved early punch-out for user ID $targetUid', NOW())");

        echo json_encode(['success' => true, 'message' => 'Early Punch-Out request approved!']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Log not found']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
