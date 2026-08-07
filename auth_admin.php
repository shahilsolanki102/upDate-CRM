<?php
require_once "config.php";

$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

if ($email === '' || $pass === '') {
    echo "<script>alert('Please enter email and password');location.href='admin_login.php';</script>";
    exit;
}

// 1. Check in admins table
$stmt = $conn->prepare("SELECT id, name, password, role FROM admins WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

$userFound = false;
$r = null;

if ($res && $res->num_rows === 1) {
    $r = $res->fetch_assoc();
    $userFound = true;
} else {
    $stmt->close();
    // 2. Check in users table for role='admin'
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        $r = $res->fetch_assoc();
        $userFound = true;
    }
}

if ($userFound && $r) {
    $valid = false;
    if (password_verify($pass, $r['password'])) {
        $valid = true;
    } elseif ($r['password'] === md5($pass) || $r['password'] === $pass) {
        $valid = true;
    }

    if ($valid) {
        $_SESSION['role'] = 'admin';
        $_SESSION['name'] = $r['name'];
        $_SESSION['uid']  = $r['id'];
        $_SESSION['aid']  = $r['id']; // Set aid for notes.php compatibility!

        header("Location: admin/dashboard.php");
        exit;
    } else {
        echo "<script>alert('Invalid admin password');location.href='admin_login.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('Invalid admin credentials');location.href='admin_login.php';</script>";
    exit;
}
?>
