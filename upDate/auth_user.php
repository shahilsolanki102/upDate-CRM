<?php
require_once "config.php";

$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

if ($email === '' || $pass === '') {
    echo "<script>alert('Please enter email and password');location.href='user_login.php';</script>";
    exit;
}

$stmt = $conn->prepare("SELECT id, name, phone, password FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows === 1) {
    $r = $res->fetch_assoc();

    $valid = false;
    if (password_verify($pass, $r['password'])) {
        $valid = true;
    } elseif ($r['password'] === md5($pass) || $r['password'] === $pass) {
        $valid = true;
    }

    if ($valid) {
        // Generate OTP
        $otp = rand(1000, 9999);

        // Save in session
        $_SESSION['otp']        = $otp;
        $_SESSION['temp_name']  = $r['name'];
        $_SESSION['temp_uid']   = $r['id'];
        $_SESSION['temp_phone'] = $r['phone'] ?? '';

        // Attempt sending OTP via WhatsApp API (with timeout & silent fallback)
        if (!empty($r['phone'])) {
            $url = "https://e4daa5d05574.ngrok-free.app/send_otp"; 
            $payload = json_encode([
                "to" => "whatsapp:+91". $r['phone'], 
                "message" => "Your OTP is: ".$otp
            ]);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);
            @curl_exec($ch);
            @curl_close($ch);
        }

        header("Location: verify_otp.php");
        exit;
    } else {
        echo "<script>alert('Invalid password');location.href='user_login.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('Invalid user credentials');location.href='user_login.php';</script>";
    exit;
}
?>
