<?php
date_default_timezone_set('Asia/Kolkata');

$host = "localhost"; 
$user = "root"; 
$pass = ""; 
$db   = "updt_crm";

// Connect to MySQL with safe error suppression and clear guidance
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_errno) { 
    // Fallback if updt_crm fails
    $conn = @new mysqli($host, $user, $pass, "updt_shahil");
    if ($conn->connect_errno) {
        die("
            <div style='font-family:sans-serif; max-width:600px; margin:60px auto; padding:24px; border:2px solid #ef4444; border-radius:16px; background:#fef2f2; color:#991b1b;'>
                <h2 style='margin:0 0 10px 0;'>⚠️ MySQL Server is NOT running in XAMPP</h2>
                <p style='font-size:15px; line-height:1.5; color:#7f1d1d;'>
                    Please open <strong>XAMPP Control Panel</strong> and click <strong>Start</strong> button next to <strong>MySQL</strong>.
                </p>
                <div style='font-size:13px; color:#6b7280; margin-top:10px;'>
                    Error Details: " . htmlspecialchars($conn->connect_error) . " (Code: " . $conn->connect_errno . ")
                </div>
            </div>
        "); 
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
