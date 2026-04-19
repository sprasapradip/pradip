// ================= index.php =================
<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
<title>Home</title>
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

<section class="hero">
<h1><?php echo $profile['name']; ?></h1>
<p><?php echo $profile['title']; ?></p>
<p><?php echo $profile['bio']; ?></p>
<a class="btn" href="<?php echo $profile['cv']; ?>">Download CV</a>
</section>

<footer>© <?php echo date("Y"); ?></footer>
</body>
</html>