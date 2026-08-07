<?php
date_default_timezone_set('Asia/Kolkata');

$host = "localhost"; 
$user = "root"; 
$pass = ""; 
$db   = "updt_crm";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { 
    // Fallback if updt_crm fails
    $conn = new mysqli($host, $user, $pass, "updt_shahil");
    if ($conn->connect_error) {
        die("DB Connection failed: " . $conn->connect_error); 
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
