<?php
require_once "../config.php";
$requireLogin = 'admin'; 
include __DIR__."/../includes/header.php";

// Fetch all logs with user info
$logs = $conn->query("
    SELECT a.*, u.name 
    FROM activity_log a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC
");
?>
<h2>Admin Activity Log</h2>

<table border="1" width="100%">
    <tr><th>User</th><th>Action</th><th>Date</th></tr>
    <?php while($row = $logs->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['name'] ?></td>
            <td><?= $row['action'] ?></td>
            <td><?= $row['created_at'] ?></td>
        </tr>
    <?php } ?>
</table>
<?php include __DIR__."/../includes/footer.php"; ?>