<?php
require_once "../config.php";
include __DIR__."/../includes/header.php";

$msg = '';
if (isset($_POST['save_event'])) {
    $title = trim($_POST['title'] ?? '');
    $start = trim($_POST['start'] ?? '');
    $end   = trim($_POST['end'] ?? $start);

    if ($title !== '' && $start !== '') {
        $stmt = $conn->prepare("INSERT INTO events (title, start, end) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $start, $end);
        $stmt->execute();
        $stmt->close();
        $msg = "Schedule Event added successfully!";
    }
}

if (isset($_GET['del'])) {
    $delId = (int)$_GET['del'];
    $conn->query("DELETE FROM events WHERE id=$delId");
    header("Location: calendar.php"); exit;
}

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
        <span class="badge" style="background:#fff; color:#065f46; font-size:12px; padding:6px 14px;">🎉 Every Saturday & Sunday Off</span>
    </div>
</div>

<div class="grid">
    <div class="card">
        <h3>➕ Add Schedule Event</h3>
        <?php if ($msg): ?>
            <div style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; padding:8px 12px; border-radius:8px; font-size:13px; margin-bottom:12px;">
                ✓ <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="calendar.php" style="display:grid; gap:10px;">
            <div>
                <label style="font-size:12px; font-weight:600; color:var(--text-muted);">Event Title</label>
                <input class="input" name="title" placeholder="e.g. Client Meeting, Team Review" required>
            </div>
            <div>
                <label style="font-size:12px; font-weight:600; color:var(--text-muted);">Start Date / Time</label>
                <input class="input" type="datetime-local" name="start" required>
            </div>
            <div>
                <label style="font-size:12px; font-weight:600; color:var(--text-muted);">End Date / Time (Optional)</label>
                <input class="input" type="datetime-local" name="end">
            </div>
            <button class="btn" name="save_event" type="submit" style="margin-top:4px;">📅 Add to Schedule</button>
        </form>
    </div>

    <div class="card" style="grid-column: span 2;">
        <h3>📅 Upcoming Schedule & Meetings</h3>
        <table>
            <thead>
                <tr><th>#</th><th>Event Title</th><th>Date & Time</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php $i=1; if ($eventsList && $eventsList->num_rows > 0): while($ev = $eventsList->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><strong><?php echo htmlspecialchars($ev['title']); ?></strong></td>
                    <td style="font-size:13px; color:#64748b;"><?php echo date('d M Y, h:i A', strtotime($ev['start'])); ?></td>
                    <td><a href="?del=<?php echo $ev['id']; ?>" class="btn danger-btn" style="padding:4px 10px; font-size:12px;" onclick="return confirm('Delete event?')">Delete</a></td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="4" style="text-align:center; color:#777; padding:16px;">No upcoming events scheduled.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
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
    selectable: true,
    businessHours: {
      daysOfWeek: [ 1, 2, 3, 4, 5 ], // Mon-Fri work days
      startTime: '09:00',
      endTime: '18:00',
    },
    events: "getEvents.php"
  });
  calendar.render();
});
</script>

<?php include __DIR__."/../includes/footer.php"; ?>
