<?php
require_once "../config.php";
include __DIR__."/../includes/header.php";

if ($_SESSION['role'] !== 'user') {
    header("Location: ../admin/dashboard.php");
    exit;
}

$uid = $_SESSION['uid'] ?? 0;

// Fetch user task stats
$taskStats = $conn->query("SELECT status, COUNT(*) as count FROM tasks WHERE user_id=$uid OR assigned_to=$uid GROUP BY status");
$tData = ['pending' => 0, 'in_progress' => 0, 'complete' => 0, 'submitted' => 0];
if ($taskStats) {
    while ($row = $taskStats->fetch_assoc()) {
        $st = strtolower(str_replace(' ', '_', $row['status'] ?: 'pending'));
        $tData[$st] = (int)$row['count'];
    }
}
?>

<div class="grid">
    <div class="card" style="display:flex; align-items:center; justify-content:space-between;">
        <div>
            <h3>My Completion Rate</h3>
            <div class="metric" style="color:#10b981;">100%</div>
            <div style="font-size:12px; color:#10b981; margin-top:4px;">On-Time Submissions</div>
        </div>
        <div style="font-size:32px; color:#10b981;"><i class="bi bi-star-fill"></i></div>
    </div>

    <div class="card" style="display:flex; align-items:center; justify-content:space-between;">
        <div>
            <h3>Submitted Tasks</h3>
            <div class="metric" style="color:#2563eb;"><?php echo ($tData['submitted'] + $tData['complete']); ?></div>
            <div style="font-size:12px; color:#3b82f6; margin-top:4px;">PDF Reports Submitted</div>
        </div>
        <div style="font-size:32px; color:#2563eb;"><i class="bi bi-file-earmark-check-fill"></i></div>
    </div>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h3>📈 My Task Progress Analytics</h3>
    <div style="max-width: 400px; margin: 15px auto;">
        <canvas id="userTaskChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    new Chart(document.getElementById('userTaskChart').getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Pending', 'In Progress', 'Submitted PDF'],
        datasets: [{
          data: [<?php echo $tData['pending']; ?>, <?php echo $tData['in_progress']; ?>, <?php echo ($tData['submitted'] + $tData['complete']); ?>],
          backgroundColor: ['#f59e0b', '#3b82f6', '#10b981']
        }]
      },
      options: { responsive: true }
    });
  });
</script>

<?php include __DIR__."/../includes/footer.php"; ?>
