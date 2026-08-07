<?php include __DIR__."/../includes/header.php"; ?>
<div class="card"><h3>Gmail</h3>
<form id="gmForm"><input class="input" name="to" placeholder="recipient@example.com">
<input class="input" name="subject" placeholder="Subject"><input class="input" name="message" placeholder="Message"><button class="primary">Send</button></form>
<pre id="gmOut"></pre></div>
<script>
document.getElementById('gmForm').addEventListener('submit', async (e)=>{
 e.preventDefault(); const data=Object.fromEntries(new FormData(e.target).entries());
 const r=await fetch('http://127.0.0.1:5001/send_gmail',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
 document.getElementById('gmOut').textContent=await r.text();
});
</script><?php include __DIR__."/../includes/footer.php"; ?>