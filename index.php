<?php include 'config.php'; ?>
<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $profile['name']; ?> | Portfolio</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<!-- NAVBAR -->
<nav>
<a href="index.php">Home</a>
<a href="projects.php">Projects</a>
<a href="experience.php">Experience</a>
<a href="services.php">Services</a>
<a href="contact.php">Contact</a>
</nav>

<!-- HERO SECTION -->
<section class="hero">
    <h1><?php echo $profile['name']; ?></h1>
    <h2><?php echo $profile['title']; ?></h2>
    <p><?php echo $profile['bio']; ?></p>

    <div class="hero-buttons">
        <a class="btn" href="<?php echo $profile['cv']; ?>" download>Download CV</a>
        <a class="btn secondary" href="contact.php">Hire Me</a>
    </div>
</section>

<!-- SKILLS SECTION -->
<section class="skills">
    <h2>Skills</h2>
    <div class="skills-grid">
        <div>WordPress Development</div>
        <div>PHP & MySQL</div>
        <div>Website Design</div>
        <div>SEO Optimization</div>
        <div>Project Management</div>
        <div>Electrical Engineering</div>
    </div>
</section>

<!-- PROJECT PREVIEW -->
<section class="projects">
    <h2>Featured Projects</h2>
    <div class="project-grid">
        <?php
        $projects = $conn->query("SELECT * FROM projects LIMIT 3");
        while($row = $projects->fetch_assoc()){
            echo "
            <div class='card'>
                <h3>{$row['title']}</h3>
                <p>{$row['description']}</p>
                <a href='{$row['link']}' target='_blank'>View Project</a>
            </div>
            ";
        }
        ?>
    </div>
    <a class="btn" href="projects.php">View All Projects</a>
</section>

<!-- SERVICES -->
<section class="services">
    <h2>Services</h2>
    <div class="service-grid">
    <div class="service-card">Website Development</div>
    <div class="service-card">Hosting & Domain Setup</div>
    <div class="service-card">SEO Optimization</div>
    <div class="service-card">Technical Consulting</div>
</div>
</section>

<!-- CALL TO ACTION -->
<section class="cta">
    <h2>Have a project in mind?</h2>
    <p>Let’s build something great together.</p>
    <a class="btn" href="contact.php">Contact Me</a>
</section>

<!-- FOOTER -->
<?php include 'footer.php'; ?>
</body>
</html>