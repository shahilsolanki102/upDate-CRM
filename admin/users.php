<?php 
require_once "../config.php";
include __DIR__."/../includes/header.php";

$msg = '';

// Add / Create User
if (isset($_POST['create'])) { 
    $name   = trim($_POST['name'] ?? ''); 
    $email  = trim($_POST['email'] ?? ''); 
    $phone  = trim($_POST['phone'] ?? ''); 
    $dept   = trim($_POST['department'] ?? 'General'); 
    $desig  = trim($_POST['designation'] ?? 'Employee'); 
    $role   = $_POST['role'] ?? 'user';
    $pwd    = password_hash($_POST['password'] ?? 'user123', PASSWORD_DEFAULT);

    if ($name && $email) {
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, department, designation, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active') ON DUPLICATE KEY UPDATE name=VALUES(name)");
        $stmt->bind_param("sssssss", $name, $email, $phone, $dept, $desig, $pwd, $role);
        $stmt->execute();
        $stmt->close();
        $msg = "User/Employee account created successfully!";
    }
}

// Toggle Status / Delete User
if (isset($_GET['del'])) { 
    $id = (int)$_GET['del']; 
    $conn->query("DELETE FROM users WHERE id=$id AND role != 'admin'"); 
    header("Location: users.php"); exit;
}

if (isset($_GET['toggle'])) { 
    $id = (int)$_GET['toggle']; 
    $conn->query("UPDATE users SET status = IF(status='active','inactive','active') WHERE id=$id"); 
    header("Location: users.php"); exit;
}

$res = $conn->query("SELECT * FROM users ORDER BY id DESC"); 
?>

<div class="card" style="margin-bottom:24px;">
    <h3>👥 Add New User / Employee</h3>
    <?php if ($msg): ?>
        <div style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; padding:10px 14px; border-radius:10px; font-size:14px; margin-bottom:16px;">
            ✓ <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="users.php" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
        <div>
            <label style="font-size:12px; font-weight:600; color:var(--text-muted);">Full Name</label>
            <input class="input" name="name" placeholder="John Doe" required>
        </div>
        <div>
            <label style="font-size:12px; font-weight:600; color:var(--text-muted);">Email Address</label>
            <input class="input" type="email" name="email" placeholder="john@company.com" required>
        </div>
        <div>
            <label style="font-size:12px; font-weight:600; color:var(--text-muted);">Phone Number</label>
            <input class="input" name="phone" placeholder="+919876543210">
        </div>
        <div>
            <label style="font-size:12px; font-weight:600; color:var(--text-muted);">Department</label>
            <input class="input" name="department" placeholder="e.g. Sales, IT, HR">
        </div>
        <div>
            <label style="font-size:12px; font-weight:600; color:var(--text-muted);">Role</label>
            <select name="role" class="input">
                <option value="user">User / Employee</option>
                <option value="admin">Administrator</option>
            </select>
        </div>
        <div>
            <label style="font-size:12px; font-weight:600; color:var(--text-muted);">Password</label>
            <input class="input" type="password" name="password" placeholder="••••••••" required>
        </div>
        <div style="grid-column: 1 / -1; margin-top:8px;">
            <button class="btn" name="create" type="submit" style="padding:12px 24px;">➕ Create Employee Account</button>
        </div>
    </form>
</div>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
        <h3>👥 All Registered Users & Employees</h3>
        <span class="badge"><?php echo $res ? $res->num_rows : 0; ?> Total Users</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>User Details</th>
                <th>Role</th>
                <th>Department</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res && $res->num_rows > 0): while($r = $res->fetch_assoc()): ?>
            <tr>
                <td style="display:flex; align-items:center; gap:12px;">
                    <div class="avatar-sm" style="width:38px; height:38px; font-size:15px; background:<?php echo $r['role']==='admin'?'var(--brand-gradient)':'linear-gradient(135deg, #10b981, #059669)'; ?>;">
                        <?php echo strtoupper(substr($r['name'], 0, 1)); ?>
                    </div>
                    <div>
                        <div style="font-weight:700; color:var(--text-dark);"><?php echo htmlspecialchars($r['name']); ?></div>
                        <div style="font-size:12px; color:var(--text-muted);"><?php echo htmlspecialchars($r['email']); ?></div>
                    </div>
                </td>
                <td>
                    <span class="badge" style="background:<?php echo $r['role']==='admin'?'#e0e7ff':'#ecfdf5'; ?>; color:<?php echo $r['role']==='admin'?'#3730a3':'#065f46'; ?>; text-transform:capitalize;">
                        <?php echo $r['role']; ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($r['department'] ?? 'General'); ?></td>
                <td><?php echo htmlspecialchars($r['phone'] ?: '—'); ?></td>
                <td>
                    <a href="?toggle=<?php echo $r['id']; ?>" class="badge" style="background:<?php echo ($r['status']??'active')==='active'?'#d1fae5':'#fee2e2'; ?>; color:<?php echo ($r['status']??'active')==='active'?'#065f46':'#991b1b'; ?>; text-decoration:none;">
                        ● <?php echo ucfirst($r['status'] ?? 'active'); ?>
                    </a>
                </td>
                <td style="white-space:nowrap;">
                    <?php if ($r['role'] !== 'admin'): ?>
                        <a class="btn danger-btn" style="padding:6px 12px; font-size:12px;" href="?del=<?php echo $r['id']; ?>" onclick="return confirm('Delete this user account?')">Delete</a>
                    <?php else: ?>
                        <span style="font-size:12px; color:#aaa;">System Admin</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="6" style="text-align:center; color:#777; padding:16px;">No users registered yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__."/../includes/footer.php"; ?>