<?php require_once "config.php"; if(isset($_SESSION['role'])){ header("Location: user/dashboard.php"); exit; } ?>

<!doctype html>
<html>
  <head>
    <meta charset="utf-8"><title>Employee Login — upDate</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="loginWrap">
<form class="loginBox" method="post" action="auth_user.php">
  <img src="assets/images/logo.png" class="centerLogo" alt="logo">
  <div class="smallCenter">Employee Login</div>
  <div class="subtitle">upDt Education Technology Pvt. Ltd.</div>
  <input class="input" name="email" type="email" placeholder="Email" required>
  <input class="input" name="password" type="password" placeholder="Password" required>
  <button class="primary">Login</button>
  <div class="row"><a class="link" href="admin_login.php">Admin Login</a><a class="link" href="forgot.php">Forgot Password?</a></div>
</form></body></html>