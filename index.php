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

    <h2>Electrical & Electronics Engineer</h2>

    <p>
        Working as Electrical Engineer at Maulakalika Cable Car.
        Specialized in power systems, cable car operations, maintenance,
        and industrial electrical engineering.
    </p>

    <div class="hero-buttons">
        <a class="btn" href="projects.php">Electrical Projects</a>
        <a class="btn secondary" href="https://linkedin.com" target="_blank">LinkedIn Articles</a>
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
    <h2>Core Expertise</h2>

    <div class="service-grid">
        <div class="service-card">Cable Car Electrical Systems</div>
        <div class="service-card">Power Distribution & Control Systems</div>
        <div class="service-card">Industrial Maintenance</div>
        <div class="service-card">Safety & Fault Analysis</div>
    </div>
</section>
</section>

<section class="timeline">
    <h2>Professional Experience</h2>

    <div>
        <h3>Electrical Engineer</h3>
        <p>Maulakalika Cable Car</p>
        <p>Responsible for operation, maintenance, safety monitoring, and fault diagnosis of cable car electrical systems.</p>
    </div>

    <div>
        <h3>Technical Instructor</h3>
        <p>Kathmandu Technical School</p>
        <p>Teaching cable car operation and electrical maintenance training.</p>
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