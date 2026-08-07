<?php include __DIR__."/../includes/header.php"; ?>
<div class="card"><h3>WhatsApp</h3>
<form id="waForm"><input class="input" name="to" placeholder="+91XXXXXXXXXX"><input class="input" name="message" placeholder="Message"><button class="primary">Send</button></form>
<pre id="waOut"></pre></div>
<script>
document.getElementById('waForm').addEventListener('submit', async (e)=>{
 e.preventDefault(); const data=Object.fromEntries(new FormData(e.target).entries());
 const r=await fetch('http://127.0.0.1:5001/send_whatsapp',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
 document.getElementById('waOut').textContent=await r.text();
});
</script><?php include __DIR__."/../includes/footer.php"; ?>