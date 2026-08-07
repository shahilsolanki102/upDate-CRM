<?php
require_once "../config.php";
include __DIR__."/../includes/header.php";

$eventsList = $conn->query("SELECT * FROM events ORDER BY start ASC");
?>

<div class="card" style="margin-bottom:20px; background:linear-gradient(135deg, #059669 0%, #10b981 100%); color:#fff;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div>
            <h3 style="color:#d1fae5; margin-bottom:4px;">🌴 Official Company Weekend Policy</h3>
            <div style="font-size:14px; opacity:0.95;">
                <strong>Saturday & Sunday are official company holidays</strong> (Weekly Off for all employees & remote workers).
            </div>
        </div>
        <span class="badge" style="background:#fff; color:#065f46; font-size:12px; padding:6px 14px;">🎉 Saturday & Sunday Off</span>
    </div>
</div>

<div class="card" style="margin-bottom:20px;">
    <h3>📅 Scheduled Events & Meetings</h3>
    <table>
        <thead>
            <tr><th>#</th><th>Event Title</th><th>Date & Time</th></tr>
        </thead>
        <tbody>
        <?php $i=1; if ($eventsList && $eventsList->num_rows > 0): while($ev = $eventsList->fetch_assoc()): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><strong><?php echo htmlspecialchars($ev['title']); ?></strong></td>
                <td style="font-size:13px; color:#64748b;"><?php echo date('d M Y, h:i A', strtotime($ev['start'])); ?></td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="3" style="text-align:center; color:#777; padding:16px;">No upcoming events scheduled.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <div id="calendar" style="min-height:500px;"></div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  let calendarEl = document.getElementById('calendar');
  let calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    businessHours: {
      daysOfWeek: [ 1, 2, 3, 4, 5 ],
      startTime: '09:00',
      endTime: '18:00',
    },
    events: "../admin/getEvents.php"
  });
  calendar.render();
});
</script>

<?php include __DIR__."/../includes/footer.php"; ?>
