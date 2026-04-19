// ================= experience.php =================
<?php
$exp = [
 ["role"=>"Cable Car Electrical Work","year"=>"2025"],
 ["role"=>"Industrial Electrical","year"=>"2024"]
];
?>
<!DOCTYPE html>
<html>
<body>
<h2>Experience</h2>
<?php foreach($exp as $e): ?>
<p><?php echo $e['role']; ?> (<?php echo $e['year']; ?>)</p>
<?php endforeach; ?>
</body>
</html>