<?php include __DIR__."/../includes/header.php"; ?>
<div class="container">
  <h2>📅 Calendar / Schedule</h2>
  <div id="calendar"></div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  let calendarEl = document.getElementById('calendar');
  let calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    events: "../admin/getEvents.php" // read-only data
  });
  calendar.render();
});
</script>
<?php include __DIR__."/../includes/footer.php"; ?>
