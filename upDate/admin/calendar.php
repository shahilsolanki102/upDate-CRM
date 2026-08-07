<?php include __DIR__."/../includes/header.php"; ?>
<div class="container">
  <h2>📅 Calendar / Schedule (Admin)</h2>
  <div id="calendar"></div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  let calendarEl = document.getElementById('calendar');
  let calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    selectable: true,
    events: "getEvents.php",

    // Add event
    select: function(info) {
      let title = prompt("Enter Event Title:");
      if (title) {
        $.post("saveEvent.php", {
          title: title,
          start: info.startStr,
          end: info.endStr
        }, function() {
          calendar.refetchEvents();
        });
      }
    },

    // Delete event
    eventClick: function(info) {
      if (confirm("Delete this event?")) {
        $.post("deleteEvent.php", {id: info.event.id}, function() {
          info.event.remove();
        });
      }
    }
  });

  calendar.render();
});
</script>
<?php include __DIR__."/../includes/footer.php"; ?>
