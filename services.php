// ================= services.php =================
<?php
$services = ["Installation","Maintenance","Solar","Troubleshooting"];
?>
<!DOCTYPE html>
<html>
<body>
<h2>Services</h2>
<?php foreach($services as $s): ?>
<p><?php echo $s; ?></p>
<?php endforeach; ?>
</body>
</html>
