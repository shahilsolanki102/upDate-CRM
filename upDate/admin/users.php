<?php include __DIR__."/../includes/header.php"; ?>
<div class="card"><h3>Users / Employees</h3>
<?php if(isset($_POST['create'])){ $name=$_POST['name']; $email=$_POST['email']; $phone=$_POST['phone']; $pwd=md5($_POST['password']); $conn->query("INSERT INTO users(name,email,phone,password) VALUES('{$conn->real_escape_string($name)}','{$conn->real_escape_string($email)}','{$conn->real_escape_string($phone)}','$pwd')"); } ?>
<?php if(isset($_GET['del'])){ $id=(int)$_GET['del']; $conn->query("DELETE FROM users WHERE id=$id"); } ?>
<?php $res=$conn->query("SELECT * FROM users ORDER BY id DESC"); ?>
<form method="post" style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:12px">
  <input class="input" name="name" placeholder="Name" required>
  <input class="input" name="email" placeholder="Email" required>
  <input class="input" name="phone" placeholder="Phone">
  <input class="input" name="password" placeholder="Password" required>
  <button class="primary" name="create">Add User</button>
</form>
<table class="card" style="width:100%;border-collapse:collapse">
<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Action</th></tr>
<?php while($r=$res->fetch_assoc()): ?><tr>
<td><?php echo $r['id']; ?></td><td><?php echo htmlspecialchars($r['name']); ?></td>
<td><?php echo htmlspecialchars($r['email']); ?></td><td><?php echo htmlspecialchars($r['phone']); ?></td>
<td><a class="link" href="?del=<?php echo $r['id']; ?>">Delete</a></td></tr><?php endwhile; ?>
</table></div><?php include __DIR__."/../includes/footer.php"; ?>