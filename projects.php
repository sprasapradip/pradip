// ================= projects.php =================
<?php include 'config.php'; ?>
<?php
$projects = [
 ["img"=>"images/linkedin1.jpg","title"=>"Cable Car Project","desc"=>"Maulakali Cable Car electrical work"],
 ["img"=>"images/linkedin2.jpg","title"=>"Site Installation","desc"=>"Electrical installation process"],
 ["img"=>"images/linkedin3.jpg","title"=>"Maintenance","desc"=>"System troubleshooting"],
 ["img"=>"images/linkedin4.jpg","title"=>"Safety Engineering","desc"=>"Ensuring safety system"]
];
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="style.css">
</head>
<body>
<nav>
<a href="index.php">Home</a>
<a href="projects.php">Projects</a>
<a href="experience.php">Experience</a>
<a href="services.php">Services</a>
<a href="contact.php">Contact</a>
</nav>

<section>
<h2>Projects</h2>
<div class="grid">
<?php foreach($projects as $p): ?>
<div class="card">
<img src="<?php echo $p['img']; ?>">
<div class="card-body">
<h3><?php echo $p['title']; ?></h3>
<p><?php echo $p['desc']; ?></p>
</div>
</div>
<?php endforeach; ?>
</div>
</section>
</body>
</html>