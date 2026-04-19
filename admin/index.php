<?php
$conn = new mysqli("localhost","root","","portfolio");
$result = $conn->query("SELECT * FROM messages ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head><title>Admin</title></head>
<body style="background:#0f172a;color:white">
<h2>Messages</h2>
<table border="1" cellpadding="10">
<tr><th>Name</th><th>Email</th><th>Message</th></tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['email']; ?></td>
<td><?php echo $row['message']; ?></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>