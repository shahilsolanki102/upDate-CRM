<?php
require_once "../config.php";
include __DIR__."/../includes/header.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/dashboard.php");
    exit;
}

$result = $conn->query("SELECT status, COUNT(*) as count FROM users GROUP BY status");
$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $st = $row['status'] ?: 'active';
        $data[$st] = (int)$row['count'];
    }
}

$active   = $data['active'] ?? 1;
$inactive = $data['inactive'] ?? 0;
?>

<div class="card">
  <h2>Performance / Analytics (Admin)</h2>
  <div style="max-width: 500px; margin: 20px auto;">
    <canvas id="userChart"></canvas>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('userChart').getContext('2d');
    new Chart(ctx, {
      type: 'pie',
      data: {
        labels: ['Active Users', 'Inactive Users'],
        datasets: [{
          label: 'User Status',
          data: [<?php echo $active; ?>, <?php echo $inactive; ?>],
          backgroundColor: ['#36a2eb', '#ff6384']
        }]
      },
      options: { responsive: true }
    });
  });
</script>

<?php include __DIR__."/../includes/footer.php"; ?>
