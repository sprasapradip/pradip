<?php include 'config.php'; ?>
<?php include 'header.php'; ?>
<?php
$settingsRaw = $conn->query("SELECT setting_key, setting_value FROM site_settings");

$settings = [];

while($row = $settingsRaw->fetch_assoc()){
    $settings[$row['setting_key']] = $row['setting_value'];
}
$areas = $conn->query("SELECT * FROM core_areas ORDER BY sort_order ASC");
?>
<!-- HERO SECTION -->
<section class="hero-modern">

    <div class="hero-content">

        <span class="hero-badge">
    <?= htmlspecialchars($settings['hero_badge'] ?? '') ?>
</span>
        <h1>
    <?= htmlspecialchars($settings['hero_title'] ?? '') ?>
</h1>

<p class="hero-text">
    <?= htmlspecialchars($settings['hero_text'] ?? '') ?>
</p>

        <div class="hero-buttons">
            <a href="projects.php" class="btn">View Projects</a>
            <a href="contact.php" class="btn secondary">Hire Me</a>
            <a href="assets/cv.pdf" class="btn outline" target="_blank">
                Download CV
            </a>
        </div>

    </div>

</section>

<!-- STATS -->
<section class="stats-section">

    <div class="stats-grid">

        <div class="stat-card">
           <h2><?= $settings['experience_years'] ?? 0 ?>+</h2>
            <p>Years Experience</p>
        </div>

        <div class="stat-card">
            <h2><?= $settings['repairs'] ?? 0 ?>+</h2>
            <p>Electrical Repairs</p>
        </div>

        <div class="stat-card">
            <h2><?= $settings['operations'] ?? 0 ?>+</h2>
            <p>Maintenance Operations</p>
        </div>

        <div class="stat-card">
            <h2><?= $settings['reliability'] ?? 0 ?>%</h2>
            <p>System Reliability</p>
        </div>

    </div>

</section>

<!-- ABOUT -->
<section class="page">

    <div class="about-grid">

        <div>
            <h1 class="page-title">About Me</h1>

           <p class="text-block">
               <?= htmlspecialchars($settings['about_text_1'] ?? '') ?>
            </p>

           <p class="text-block">
                 <?= htmlspecialchars($settings['about_text_2'] ?? '') ?>
           </p>
        </div>

        <div class="about-box">
            <h3>Core Engineering Areas</h3>

            <ul>
    <?php while($a = $areas->fetch_assoc()): ?>
        <li><?= htmlspecialchars($a['title']) ?></li>
    <?php endwhile; ?>
</ul>
        </div>

    </div>

</section>

<!-- TECHNICAL SKILLS -->
<section class="page">

    <h1 class="page-title">Technical Expertise</h1>

    <div class="grid">

        <div class="card">
            <div class="card-body">
                <h3>Electrical Systems</h3>
                <p>Industrial and commercial power systems maintenance.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3>Industrial Automation</h3>
                <p>PLC controls, sensors, relays, and automation systems.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3>Cable Car Operations</h3>
                <p>Ropeway electrical system maintenance and operation.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3>Fault Analysis</h3>
                <p>Electrical troubleshooting and diagnostic procedures.</p>
            </div>
        </div>

    </div>

</section>

<!-- FEATURED PROJECTS -->
<section class="page">

    <h1 class="page-title">Featured Engineering Projects</h1>

    <div class="grid">

        <?php
        $projects = $conn->query("SELECT * FROM projects ORDER BY id DESC LIMIT 6");

        while($row = $projects->fetch_assoc()):
        ?>

        <div class="project-card">

            <div class="project-image">
                <img src="uploads/<?php echo $row['image']; ?>" alt="">
            </div>

            <div class="card-body">

                <h3>
                    <?php echo htmlspecialchars($row['title']); ?>
                </h3>

                <p>
                    <?php echo substr(strip_tags($row['description']),0,120); ?>
                </p>

                <a href="project.php?id=<?php echo $row['id']; ?>" class="btn small">
                    View Details
                </a>

            </div>

        </div>

        <?php endwhile; ?>

    </div>

</section>

<!-- EXPERIENCE -->
<!-- EXPERIENCE -->
<section class="page">

    <h1 class="page-title">Professional Experience</h1>

    <div class="timeline">

        <?php
        $exp = $conn->query("SELECT * FROM experience ORDER BY sort_order ASC, id DESC");

        while($e = $exp->fetch_assoc()):
        ?>

        <div class="timeline-item">

            <h3><?= htmlspecialchars($e['position']) ?></h3>

            <span>
                <?= htmlspecialchars($e['company']) ?>
                (<?= htmlspecialchars($e['start_year']) ?> - <?= htmlspecialchars($e['end_year']) ?>)
            </span>

            <p>
                <?= htmlspecialchars($e['description']) ?>
            </p>

        </div>

        <?php endwhile; ?>

    </div>

</section>

<!-- BLOG -->
<section class="page">

    <h1 class="page-title">Latest Engineering Articles</h1>

    <div class="grid">

        <?php
        $blogs = $conn->query("SELECT * FROM blogs ORDER BY id DESC LIMIT 3");

        while($blog = $blogs->fetch_assoc()):
        ?>

        <div class="card">

            <div class="card-body">

                <h3>
                    <?php echo htmlspecialchars($blog['title']); ?>
                </h3>

                <p>
                    <?php echo substr(strip_tags($blog['content']),0,100); ?>
                </p>

                <a href="blog.php?id=<?php echo $blog['id']; ?>" class="btn small">
                    Read More
                </a>

            </div>

        </div>

        <?php endwhile; ?>

    </div>

</section>

<!-- CONTACT CTA -->
<section class="cta-section">

    <h1>Need Electrical Engineering Solutions?</h1>

    <p>
        Available for engineering consultation,
        industrial maintenance, troubleshooting,
        and technical projects.
    </p>

    <a href="contact.php" class="btn">
        Contact Me
    </a>

</section>

<?php include 'footer.php'; ?>