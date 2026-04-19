// ================= projects.php =================
<?php include 'config.php'; ?>
<?php
$projects = [
 ["img"=>"images/linkedin1.jpg","title"=>"Cable Car Project","desc"=>"Maulakali Cable Car electrical work"],
 ["img"=>"images/linkedin2.jpg","title"=>"Site Work","desc"=>"Electrical installation"],
 ["img"=>"images/linkedin3.jpg","title"=>"Maintenance","desc"=>"System troubleshooting"],
 ["img"=>"images/linkedin4.jpg","title"=>"Safety Work","desc"=>"Engineering safety system"]
];
?>
<!DOCTYPE html>
<html>
<head><title>Projects</title></head>
<body>
<h2>Projects</h2>
<?php foreach($projects as $p): ?>
<div>
<img src="<?php echo $p['img']; ?>" width="300">
<h3><?php echo $p['title']; ?></h3>
<p><?php echo $p['desc']; ?></p>
</div>
<?php endforeach; ?>
</body>
</html>


