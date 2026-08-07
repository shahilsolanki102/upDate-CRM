<?php
require_once "config.php";

if (!isset($_SESSION['role'])) {
    header("Location: user_login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$task = null;

if ($id > 0) {
    $res = $conn->query("
        SELECT t.*, u.name AS user_name, u.email AS user_email, u.department AS user_dept, u.phone AS user_phone,
               a.name AS admin_name
        FROM tasks t
        LEFT JOIN users u ON u.id = COALESCE(NULLIF(t.user_id,0), NULLIF(t.assigned_to,0))
        LEFT JOIN users a ON a.id = t.created_by
        WHERE t.id = $id
    ");
    if ($res && $res->num_rows > 0) {
        $task = $res->fetch_assoc();
    }
}

if (!$task) {
    die("Ticket not found.");
}

$ticketId = $task['ticket_id'] ?: ("TK-" . date('Y') . "-" . str_pad($task['id'], 5, '0', STR_PAD_LEFT));
$priority = $task['priority'] ?: 'Normal';
$backUrl  = ($_SESSION['role'] === 'admin') ? 'admin/tasks.php' : 'user/tasks.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Official Work Ticket — <?php echo htmlspecialchars($ticketId); ?></title>
<link rel="icon" type="image/svg+xml" href="assets/images/logo.svg">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
    body {
        font-family: 'Outfit', sans-serif;
        background: #f1f5f9;
        color: #0f172a;
        padding: 30px 15px;
    }
    .ticket-card {
        max-width: 720px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        border: 2px solid #e2e8f0;
        overflow: hidden;
    }
    .ticket-header {
        background: linear-gradient(135deg, #102a56 0%, #1e3c72 100%);
        color: #ffffff;
        padding: 26px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .ticket-body {
        padding: 30px;
    }
    .ticket-badge {
        font-size: 12px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 999px;
        display: inline-block;
        text-transform: uppercase;
    }
    .barcode-stub {
        font-family: 'Courier New', Courier, monospace;
        font-size: 20px;
        font-weight: bold;
        letter-spacing: 4px;
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        padding: 12px;
        text-align: center;
        border-radius: 12px;
        margin-top: 20px;
    }
    @media print {
        body { background: #fff; padding: 0; }
        .ticket-card { box-shadow: none; border: 1px solid #000; }
        .no-print { display: none !important; }
    }
</style>
</head>
<body>

<div class="no-print" style="max-width: 720px; margin: 0 auto 20px; display:flex; justify-content:space-between; align-items:center;">
    <a href="<?php echo $backUrl; ?>" style="text-decoration:none; font-weight:700; color:#3b82f6; font-size:14.5px; display:flex; align-items:center; gap:6px;">
        <i class="bi bi-arrow-left-circle-fill" style="font-size:18px;"></i> ← Back to Tasks Board
    </a>
    <button onclick="window.print()" style="padding:10px 20px; background:#10b981; color:#fff; border:none; border-radius:12px; font-weight:700; font-family:inherit; cursor:pointer; display:flex; align-items:center; gap:8px;">
        <i class="bi bi-printer-fill"></i> Print / Save PDF Ticket
    </button>
</div>

<div class="ticket-card">
    <div class="ticket-header">
        <div style="display:flex; align-items:center; gap:14px;">
            <img src="assets/images/logo.svg" style="width:48px; height:48px;" alt="Logo">
            <div>
                <div style="font-size:12px; opacity:0.8; text-transform:uppercase; letter-spacing:1px; font-weight:700;">upDt Technology Pvt. Ltd.</div>
                <div style="font-size:22px; font-weight:800;">Official Work Ticket</div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:18px; font-weight:800; color:#93c5fd;"><?php echo htmlspecialchars($ticketId); ?></div>
            <div style="font-size:12px; opacity:0.8;">Issued: <?php echo date('d M Y', strtotime($task['created_at'])); ?></div>
        </div>
    </div>

    <div class="ticket-body">
        <!-- Status & Priority -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:16px; border-bottom:1.5px solid #f1f5f9;">
            <div>
                <span style="font-size:12px; color:#64748b; font-weight:600; display:block; margin-bottom:4px;">PRIORITY</span>
                <span class="ticket-badge" style="background:<?php echo $priority==='Urgent'?'#fee2e2':($priority==='High'?'#ffedd5':'#e0e7ff'); ?>; color:<?php echo $priority==='Urgent'?'#991b1b':($priority==='High'?'#c2410c':'#3730a3'); ?>;">
                    <?php echo $priority==='Urgent'?'🔥 Urgent':($priority==='High'?'⚡ High':'🌱 Normal'); ?>
                </span>
            </div>

            <div>
                <span style="font-size:12px; color:#64748b; font-weight:600; display:block; margin-bottom:4px;">TICKET STATUS</span>
                <span class="ticket-badge" style="background:<?php echo !empty($task['submission_file'])?'#d1fae5':'#fef3c7'; ?>; color:<?php echo !empty($task['submission_file'])?'#065f46':'#92400e'; ?>;">
                    <?php echo !empty($task['submission_file']) ? '✓ Completed & Submitted' : '⏳ Action Pending'; ?>
                </span>
            </div>

            <div>
                <span style="font-size:12px; color:#64748b; font-weight:600; display:block; margin-bottom:4px;">DUE DATE</span>
                <span style="font-weight:700; font-size:14px; color:#1e293b;">
                    <?php echo $task['due_date'] ? date('d M Y', strtotime($task['due_date'])) : 'No Due Date'; ?>
                </span>
            </div>
        </div>

        <!-- Task Info -->
        <div style="margin-bottom:24px;">
            <span style="font-size:12px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Task Title & Subject</span>
            <h2 style="margin:6px 0 12px 0; font-size:22px; color:#0f172a;"><?php echo htmlspecialchars($task['title']); ?></h2>
            
            <?php if (!empty($task['description'])): ?>
                <span style="font-size:12px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Detailed Work Instructions</span>
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:16px; font-size:14.5px; color:#334155; line-height:1.6; margin-top:6px;">
                    <?php echo nl2br(htmlspecialchars($task['description'])); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Employee Info Grid -->
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; background:#f1f5f9; padding:18px; border-radius:16px; margin-bottom:20px;">
            <div>
                <span style="font-size:11.5px; color:#64748b; font-weight:700; text-transform:uppercase;">ASSIGNED EMPLOYEE</span>
                <div style="font-weight:700; font-size:15px; color:#0f172a; margin-top:2px;"><?php echo htmlspecialchars($task['user_name'] ?? 'Unassigned'); ?></div>
                <div style="font-size:12.5px; color:#475569;"><?php echo htmlspecialchars($task['user_email'] ?? ''); ?></div>
                <div style="font-size:12px; color:#64748b;"><?php echo htmlspecialchars($task['user_dept'] ?? 'General Dept'); ?></div>
            </div>

            <div>
                <span style="font-size:11.5px; color:#64748b; font-weight:700; text-transform:uppercase;">ISSUED BY ADMIN</span>
                <div style="font-weight:700; font-size:15px; color:#0f172a; margin-top:2px;"><?php echo htmlspecialchars($task['admin_name'] ?? 'System Administrator'); ?></div>
                <div style="font-size:12.5px; color:#475569;">upDt Technology Pvt. Ltd.</div>
            </div>
        </div>

        <!-- Submission Status if completed -->
        <?php if (!empty($task['submission_file'])): ?>
            <div style="background:#ecfdf5; border:1px solid #a7f3d0; padding:14px; border-radius:14px; margin-bottom:20px;">
                <div style="font-size:13px; font-weight:700; color:#065f46;">📄 Work PDF Submission Attachment</div>
                <div style="font-size:12px; color:#047857; margin-top:2px;">Submitted on: <?php echo $task['submitted_at']; ?></div>
            </div>
        <?php endif; ?>

        <!-- Barcode Stub -->
        <div class="barcode-stub">
            *<?php echo htmlspecialchars($ticketId); ?>*
        </div>
    </div>
</div>

</body>
</html>
