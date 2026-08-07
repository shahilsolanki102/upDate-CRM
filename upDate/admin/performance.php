<?php
require_once "../config.php";
include __DIR__."/../includes/header.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/dashboard.php");
    exit;
}

// User status counts
$userStats = $conn->query("SELECT status, COUNT(*) as count FROM users GROUP BY status");
$uData = ['active' => 0, 'inactive' => 0];
if ($userStats) {
    while ($row = $userStats->fetch_assoc()) {
        $st = $row['status'] ?: 'active';
        $uData[$st] = (int)$row['count'];
    }
}

// Task status breakdown
$taskStats = $conn->query("SELECT status, COUNT(*) as count FROM tasks GROUP BY status");
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
            <h3>Overall Productivity Score</h3>
            <div class="metric" style="color:#10b981;">94.8%</div>
            <div style="font-size:12px; color:#10b981; margin-top:4px;">▲ +4.2% from last month</div>
        </div>
        <div style="font-size:32px; color:#10b981;"><i class="bi bi-trophy-fill"></i></div>
    </div>

    <div class="card" style="display:flex; align-items:center; justify-content:space-between;">
        <div>
            <h3>Completed Tasks Rate</h3>
            <div class="metric" style="color:#2563eb;"><?php echo ($tData['submitted'] + $tData['complete']); ?> Tasks</div>
            <div style="font-size:12px; color:#3b82f6; margin-top:4px;">Submitted & Approved</div>
        </div>
        <div style="font-size:32px; color:#2563eb;"><i class="bi bi-check-circle-fill"></i></div>
    </div>

    <div class="card" style="display:flex; align-items:center; justify-content:space-between;">
        <div>
            <h3>Active Team Members</h3>
            <div class="metric" style="color:#8b5cf6;"><?php echo $uData['active']; ?> Active</div>
            <div style="font-size:12px; color:#8b5cf6; margin-top:4px;">Remote & On-Site</div>
        </div>
        <div style="font-size:32px; color:#8b5cf6;"><i class="bi bi-person-check-fill"></i></div>
    </div>
</div>

<div class="grid" style="grid-template-columns: 1fr 1fr;">
  <div class="card">
    <h3>📊 User Status Analytics</h3>
    <div style="max-width: 380px; margin: 10px auto;">
      <canvas id="userChart"></canvas>
    </div>
  </div>

  <div class="card">
    <h3>📈 Task Distribution Analytics</h3>
    <div style="max-width: 380px; margin: 10px auto;">
      <canvas id="taskChart"></canvas>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // User chart
    new Chart(document.getElementById('userChart').getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Active Employees', 'Inactive Employees'],
        datasets: [{
          data: [<?php echo $uData['active']; ?>, <?php echo $uData['inactive']; ?>],
          backgroundColor: ['#2563eb', '#ef4444']
        }]
      },
      options: { responsive: true }
    });

    // Task chart
    new Chart(document.getElementById('taskChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: ['Pending', 'In Progress', 'Submitted / Complete'],
        datasets: [{
          label: 'Number of Tasks',
          data: [<?php echo $tData['pending']; ?>, <?php echo $tData['in_progress']; ?>, <?php echo ($tData['submitted'] + $tData['complete']); ?>],
          backgroundColor: ['#f59e0b', '#3b82f6', '#10b981']
        }]
      },
      options: { responsive: true }
    });
  });
</script>

<?php include __DIR__."/../includes/footer.php"; ?>
