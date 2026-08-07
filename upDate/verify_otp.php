<?php
require_once "config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userOtp = trim($_POST['otp'] ?? '');

    // Allow session OTP OR '1234' as universal dev OTP
    $validOtp = false;
    if (isset($_SESSION['otp']) && $userOtp !== '' && ($userOtp == $_SESSION['otp'] || $userOtp === '1234')) {
        $validOtp = true;
    } elseif ($userOtp === '1234') {
        $validOtp = true;
    }

    if ($validOtp) {
        if (!empty($_SESSION['temp_uid']))   $_SESSION['uid']   = $_SESSION['temp_uid'];
        if (!empty($_SESSION['temp_name']))  $_SESSION['name']  = $_SESSION['temp_name'];
        if (!empty($_SESSION['temp_phone'])) $_SESSION['phone'] = $_SESSION['temp_phone'];
        $_SESSION['role'] = 'user';

        unset($_SESSION['otp'], $_SESSION['temp_uid'], $_SESSION['temp_name'], $_SESSION['temp_phone']);

        header("Location: user/dashboard.php");
        exit;
    } else {
        $error = "Invalid OTP. Please try again! (Demo OTP: 1234 or " . ($_SESSION['otp'] ?? '1234') . ")";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Verify OTP — upDate</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="loginWrap">
<form class="loginBox" method="post" action="verify_otp.php">
  <img src="assets/images/logo.png" class="centerLogo" alt="logo" />
  <div class="smallCenter">OTP Verification</div>
  <div class="subtitle">Enter the OTP sent to your WhatsApp</div>

  <?php if (isset($_SESSION['otp'])): ?>
    <div style="background:rgba(255,255,255,0.1); padding:8px; border-radius:6px; margin:8px 0; font-size:12px; text-align:center;">
        Your OTP is: <strong><?php echo $_SESSION['otp']; ?></strong>
    </div>
  <?php endif; ?>

  <input class="input" name="otp" type="text" placeholder="Enter OTP" required autofocus>

  <?php if (!empty($error)) { ?>
    <div style="color:#ff6b6b; margin:8px 0; font-size:13px; text-align:center;"><?php echo htmlspecialchars($error); ?></div>
  <?php } ?>

  <button class="primary" type="submit">Verify OTP</button>

  <div class="row">
    <a class="link" href="user_login.php">Back to Login</a>
  </div>
</form>
</body>
</html>
