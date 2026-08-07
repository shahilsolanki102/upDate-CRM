<?php
require_once __DIR__ . "/config.php";

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    } else {
        header("Location: user/dashboard.php");
        exit;
    }
}

header("Location: user_login.php");
exit;
?>
