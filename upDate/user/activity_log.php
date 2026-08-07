<?php
require_once "../config.php";
$requireLogin = 'user'; 
include __DIR__."/../includes/header.php";


$user_id = $_SESSION['user_id']; // login time set thayelu hovu joiye

$sql = "SELECT a.*, u.name AS username
        FROM activity_log a
        LEFT JOIN users u ON a.user_id = u.id
        WHERE a.user_id = '$user_id'
        ORDER BY a.created_at DESC";

$result = $conn->query($sql);
?>
<h2>My Activity Log</h2>
<table border="1" width="100%">
    <tr>
        <th>User</th>
        <th>Action</th>
        <th>Date</th>
    </tr>
    <?php 
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['action']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
            </tr>
    <?php } 
    } else { ?>
        <tr><td colspan="3">No activity found</td></tr>
    <?php } ?>
</table>

<?php include __DIR__."/../includes/footer.php"; ?>