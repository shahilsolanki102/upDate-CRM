<?php
require_once "../config.php";
$requireLogin = 'user';
include __DIR__ . "/../includes/header.php";

$msg_status = '';
$uid   = $_SESSION['uid'] ?? 0;
$user  = null;

// Ensure uploads/profiles/ directory exists
if (!file_exists(__DIR__ . '/../uploads/profiles/')) {
    mkdir(__DIR__ . '/../uploads/profiles/', 0777, true);
}

// Fetch logged in user details
if ($uid > 0) {
    $res = $conn->query("SELECT * FROM users WHERE id = $uid");
    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name              = trim($_POST['name'] ?? '');
    $phone             = trim($_POST['phone'] ?? '');
    $department        = trim($_POST['department'] ?? '');
    $designation       = trim($_POST['designation'] ?? '');
    $emergency_contact = trim($_POST['emergency_contact'] ?? '');
    $emergency_phone   = trim($_POST['emergency_phone'] ?? '');
    $address           = trim($_POST['address'] ?? '');
    $bio               = trim($_POST['bio'] ?? '');
    $pass              = $_POST['password'] ?? '';
    $photoPath         = $user['profile_pic'] ?? '';

    // Profile Photo Upload Handling
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $fileName = 'user_' . $uid . '_' . time() . '.' . strtolower($ext);
        $target = __DIR__ . '/../uploads/profiles/' . $fileName;
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target)) {
            $photoPath = $fileName;
        }
    }

    if ($name !== '' && $uid > 0) {
        // Ensure columns exist
        $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_pic VARCHAR(255) NULL");
        $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS emergency_contact VARCHAR(100) NULL");
        $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS emergency_phone VARCHAR(30) NULL");
        $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT NULL");
        $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS bio TEXT NULL");

        if ($pass !== '') {
            $newHash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, department=?, designation=?, emergency_contact=?, emergency_phone=?, address=?, bio=?, password=?, profile_pic=? WHERE id=?");
            $stmt->bind_param("ssssssssssi", $name, $phone, $department, $designation, $emergency_contact, $emergency_phone, $address, $bio, $newHash, $photoPath, $uid);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, department=?, designation=?, emergency_contact=?, emergency_phone=?, address=?, bio=?, profile_pic=? WHERE id=?");
            $stmt->bind_param("sssssssssi", $name, $phone, $department, $designation, $emergency_contact, $emergency_phone, $address, $bio, $photoPath, $uid);
            $stmt->execute();
            $stmt->close();
        }

        // Log activity for Admin monitoring
        $actionLog = "Updated profile details & account settings ($name).";
        $conn->query("INSERT INTO activity_log (user_id, action, created_at) VALUES ($uid, '$actionLog', NOW())");

        $_SESSION['name'] = $name;
        $msg_status = "Profile & Account Settings updated successfully!";

        // Refresh user object
        $res = $conn->query("SELECT * FROM users WHERE id = $uid");
        if ($res && $res->num_rows > 0) {
            $user = $res->fetch_assoc();
        }
    }
}

// Fetch stats for profile widgets
$taskStats = $conn->query("SELECT COUNT(*) c FROM tasks WHERE user_id=$uid OR assigned_to=$uid");
$totalTasks = $taskStats ? ($taskStats->fetch_assoc()['c'] ?? 0) : 0;

$shiftStats = $conn->query("SELECT SUM(total_minutes) m FROM attendance_logs WHERE user_id=$uid AND status IN ('completed', 'completed_auto')");
$totalMins = $shiftStats ? ($shiftStats->fetch_assoc()['m'] ?? 0) : 0;
$workHoursStr = floor($totalMins / 60) . "h " . ($totalMins % 60) . "m";

$empId = $user['emp_id'] ?? ("EMP-2026-" . str_pad($uid, 3, '0', STR_PAD_LEFT));
$joiningDate = !empty($user['joining_date']) ? date('d M Y', strtotime($user['joining_date'])) : date('d M Y');
?>

<style>
  .profile-hero {
    background: linear-gradient(135deg, #102a56 0%, #1e3c72 50%, #2a5298 100%);
    border-radius: 24px;
    padding: 34px;
    color: #ffffff;
    margin-bottom: 24px;
    box-shadow: 0 14px 30px rgba(16, 42, 86, 0.2);
    position: relative;
    overflow: hidden;
  }
  .profile-avatar-wrap {
    position: relative;
    width: 100px;
    height: 100px;
    flex-shrink: 0;
  }
  .profile-avatar-img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #ffffff;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
  }
  .profile-tab-btn {
    padding: 12px 20px;
    border-radius: 12px;
    border: 1px solid var(--border-light);
    background: #ffffff;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    color: var(--text-dark);
  }
  .profile-tab-btn.active {
    background: var(--brand-primary);
    color: #ffffff;
    border-color: var(--brand-primary);
    box-shadow: 0 4px 12px rgba(16, 42, 86, 0.2);
  }
</style>

<!-- Profile Hero Banner -->
<div class="profile-hero">
    <div style="display:flex; align-items:center; gap:24px; flex-wrap:wrap;">
        <div class="profile-avatar-wrap">
            <?php if (!empty($user['profile_pic']) && file_exists(__DIR__ . '/../uploads/profiles/' . $user['profile_pic'])): ?>
                <img src="../uploads/profiles/<?php echo urlencode($user['profile_pic']); ?>" class="profile-avatar-img" alt="Avatar">
            <?php else: ?>
                <div class="avatar-sm" style="width:100px; height:100px; font-size:38px; border:4px solid #ffffff; box-shadow:0 8px 20px rgba(0,0,0,0.3);">
                    <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="flex:1;">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:4px;">
                <h2 style="font-size:26px; font-weight:800; margin:0;"><?php echo htmlspecialchars($user['name'] ?? ''); ?></h2>
                <span class="badge" style="background:#e0e7ff; color:#3730a3; font-weight:800; font-size:12px; padding:4px 12px;">
                    🎟️ <?php echo htmlspecialchars($empId); ?>
                </span>
                <span class="badge" style="background:#d1fae5; color:#065f46; font-weight:700; font-size:12px; padding:4px 12px;">
                    🟢 Active Employee
                </span>
            </div>

            <div style="font-size:14px; opacity:0.9; margin-bottom:12px;">
                <?php echo htmlspecialchars($user['designation'] ?? 'Software Specialist'); ?> | 
                <strong><?php echo htmlspecialchars($user['department'] ?? 'Technology Dept'); ?></strong>
            </div>

            <div style="display:flex; gap:20px; font-size:13px; opacity:0.85;">
                <span><i class="bi bi-envelope-fill"></i> <?php echo htmlspecialchars($user['email'] ?? ''); ?></span>
                <span><i class="bi bi-telephone-fill"></i> <?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></span>
                <span><i class="bi bi-calendar-check-fill"></i> Joined: <?php echo $joiningDate; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Success Message -->
<?php if ($msg_status): ?>
    <div style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; padding:14px 18px; border-radius:14px; font-size:14.5px; margin-bottom:24px; font-weight:600; display:flex; align-items:center; gap:10px;">
        <i class="bi bi-check-circle-fill" style="font-size:20px;"></i>
        <span><?php echo htmlspecialchars($msg_status); ?></span>
    </div>
<?php endif; ?>

<!-- Quick Stat Cards -->
<div class="grid">
    <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h3>Total Work Hours</h3>
            <div class="metric" style="color:#10b981;"><?php echo $workHoursStr; ?></div>
            <div style="font-size:12px; color:#10b981; margin-top:4px; font-weight:600;">⏱️ Recorded Work Shifts</div>
        </div>
        <div style="width:50px; height:50px; border-radius:14px; background:#ecfdf5; border:1px solid #a7f3d0; color:#10b981; display:flex; align-items:center; justify-content:center; font-size:24px;">
            <i class="bi bi-clock-history"></i>
        </div>
    </div>

    <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h3>Assigned Work Tickets</h3>
            <div class="metric" style="color:#2563eb;"><?php echo $totalTasks; ?></div>
            <div style="font-size:12px; color:#2563eb; margin-top:4px; font-weight:600;">🎫 Tasks & Assignments</div>
        </div>
        <div style="width:50px; height:50px; border-radius:14px; background:#eff6ff; border:1px solid #bfdbfe; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:24px;">
            <i class="bi bi-ticket-perforated-fill"></i>
        </div>
    </div>
</div>

<!-- Multi-Tab Profile Management Form -->
<form method="post" action="profile.php" enctype="multipart/form-data">
    <!-- Tab Controls -->
    <div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
        <button type="button" class="profile-tab-btn active" onclick="switchTab('personal', this)">
            <i class="bi bi-person-badge-fill"></i> Personal & Work Info
        </button>
        <button type="button" class="profile-tab-btn" onclick="switchTab('emergency', this)">
            <i class="bi bi-shield-heart-fill"></i> Emergency Contacts
        </button>
        <button type="button" class="profile-tab-btn" onclick="switchTab('security', this)">
            <i class="bi bi-key-fill"></i> Account Security & Password
        </button>
    </div>

    <!-- Tab 1: Personal & Work Info -->
    <div class="card profile-tab-content" id="tab-personal">
        <h3 style="font-size:18px; margin-bottom:18px; color:var(--text-dark);">👤 Personal Details & Work Information</h3>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:18px;">
            <div style="grid-column: 1 / -1; display:flex; align-items:center; gap:20px; padding:16px; background:#f8fafc; border-radius:16px; border:1px solid #e2e8f0;">
                <i class="bi bi-camera-fill" style="font-size:32px; color:var(--brand-primary);"></i>
                <div>
                    <label style="font-size:13px; font-weight:700; color:var(--text-dark); display:block; margin-bottom:4px;">Update Profile Photo / Avatar</label>
                    <input type="file" name="profile_photo" accept="image/*" class="input" style="padding:8px 12px; font-size:13px;">
                </div>
            </div>

            <div>
                <label style="font-size:13px; font-weight:700; display:block; margin-bottom:6px;">Full Name</label>
                <input class="input" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
            </div>

            <div>
                <label style="font-size:13px; font-weight:700; display:block; margin-bottom:6px;">Email Address (Official Read-Only)</label>
                <input class="input" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled style="background:#f1f5f9; cursor:not-allowed;">
            </div>

            <div>
                <label style="font-size:13px; font-weight:700; display:block; margin-bottom:6px;">Phone Number</label>
                <input class="input" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+91 9876543210">
            </div>

            <div>
                <label style="font-size:13px; font-weight:700; display:block; margin-bottom:6px;">Department</label>
                <input class="input" name="department" value="<?php echo htmlspecialchars($user['department'] ?? 'Technology & Development'); ?>">
            </div>

            <div>
                <label style="font-size:13px; font-weight:700; display:block; margin-bottom:6px;">Designation / Role Title</label>
                <input class="input" name="designation" value="<?php echo htmlspecialchars($user['designation'] ?? 'Software Specialist'); ?>">
            </div>

            <div>
                <label style="font-size:13px; font-weight:700; display:block; margin-bottom:6px;">Staff Employee ID</label>
                <input class="input" value="<?php echo htmlspecialchars($empId); ?>" disabled style="background:#f1f5f9; cursor:not-allowed;">
            </div>

            <div style="grid-column: 1 / -1;">
                <label style="font-size:13px; font-weight:700; display:block; margin-bottom:6px;">Residential Address / Work Location</label>
                <textarea class="input" name="address" rows="2" placeholder="Enter your full home or office address..."><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>

            <div style="grid-column: 1 / -1;">
                <label style="font-size:13px; font-weight:700; display:block; margin-bottom:6px;">Personal Bio & Work Summary</label>
                <textarea class="input" name="bio" rows="3" placeholder="Tell us about your technical expertise, skills, or role..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>

    <!-- Tab 2: Emergency Contacts -->
    <div class="card profile-tab-content" id="tab-emergency" style="display:none;">
        <h3 style="font-size:18px; margin-bottom:18px; color:var(--text-dark);">💼 Emergency Contact & Next of Kin</h3>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:18px;">
            <div>
                <label style="font-size:13px; font-weight:700; display:block; margin-bottom:6px;">Emergency Contact Person</label>
                <input class="input" name="emergency_contact" value="<?php echo htmlspecialchars($user['emergency_contact'] ?? ''); ?>" placeholder="e.g. Parent / Spouse / Guardian Name">
            </div>

            <div>
                <label style="font-size:13px; font-weight:700; display:block; margin-bottom:6px;">Emergency Phone Number</label>
                <input class="input" name="emergency_phone" value="<?php echo htmlspecialchars($user['emergency_phone'] ?? ''); ?>" placeholder="+91 9876543210">
            </div>
        </div>
    </div>

    <!-- Tab 3: Security & Password -->
    <div class="card profile-tab-content" id="tab-security" style="display:none;">
        <h3 style="font-size:18px; margin-bottom:18px; color:var(--text-dark);">🔒 Account Security & Password Manager</h3>

        <div style="display:grid; gap:18px; max-width:500px;">
            <div>
                <label style="font-size:13px; font-weight:700; display:block; margin-bottom:6px;">New Password (Leave blank to keep unchanged)</label>
                <div style="position:relative;">
                    <input class="input" type="password" id="profilePw" name="password" placeholder="••••••••">
                    <button type="button" onclick="toggleProfilePw()" style="position:absolute; right:12px; top:12px; background:none; border:none; color:var(--text-muted); cursor:pointer;">
                        <i class="bi bi-eye" id="pwIcon"></i>
                    </button>
                </div>
            </div>

            <div style="background:#f8fafc; padding:16px; border-radius:14px; border:1px solid #e2e8f0;">
                <div style="font-size:13px; font-weight:700; color:var(--text-dark); margin-bottom:4px;">🛡️ Two-Factor Authentication (2FA)</div>
                <div style="font-size:12px; color:var(--text-muted);">Account login security protected with encryption & session management.</div>
            </div>
        </div>
    </div>

    <!-- Submit Button -->
    <div style="margin-top:20px;">
        <button type="submit" class="btn" style="padding:14px 28px; font-size:15px;">
            <i class="bi bi-floppy-fill"></i> Save All Profile Changes
        </button>
    </div>
</form>

<script>
function switchTab(tabName, btn) {
    document.querySelectorAll('.profile-tab-content').forEach(c => c.style.display = 'none');
    document.querySelectorAll('.profile-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tabName).style.display = 'block';
    btn.classList.add('active');
}

function toggleProfilePw() {
    const pw = document.getElementById('profilePw');
    const icon = document.getElementById('pwIcon');
    pw.type = pw.type === 'password' ? 'text' : 'password';
    icon.className = pw.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>

<?php include __DIR__ . "/../includes/footer.php"; ?>