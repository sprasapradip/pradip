<?php include 'config.php'; ?>
<?php include 'header.php'; ?>

<!-- HERO -->
<section class="hero">

    <h1><?php echo $profile['name']; ?></h1>

    <p class="text-block">
        Electrical Engineer at Maulakalika Cable Car specializing in
        power systems, industrial maintenance, and cable car operations.
    </p>

    <div>
        <a class="btn" href="projects.php">View Projects</a>
        <a class="btn secondary" href="https://linkedin.com" target="_blank">LinkedIn</a>
    </div>

</section>

<!-- SKILLS -->
<section class="page">

    <h1 class="page-title">Skills</h1>

    <p class="text-block">
        Technical and professional expertise across electrical engineering and digital systems.
    </p>

    <div class="grid">
        <div class="card"><div class="card-body">Electrical Engineering</div></div>
        <div class="card"><div class="card-body">Power Systems</div></div>
        <div class="card"><div class="card-body">Industrial Maintenance</div></div>
        <div class="card"><div class="card-body">Fault Diagnosis</div></div>
        <div class="card"><div class="card-body">PLC / Control Systems</div></div>
        <div class="card"><div class="card-body">Project Management</div></div>
    </div>

</section>

<!-- FEATURED PROJECTS -->
<section class="page">

    <h1 class="page-title">Featured Projects</h1>

    <div class="grid">

        <?php
        $projects = $conn->query("SELECT * FROM projects LIMIT 3");
        while($row = $projects->fetch_assoc()):
        ?>

        <div class="card">
            <div class="card-body">
                <h3 class="section-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                <p class="text-block"><?php echo htmlspecialchars($row['description']); ?></p>
                <a class="btn" href="<?php echo $row['link']; ?>" target="_blank">View</a>
            </div>
        </div>

        <?php endwhile; ?>

    </div>

    <br>
    <a class="btn" href="projects.php">View All Projects</a>

</section>

<!-- SERVICES -->
<section class="page">

    <h1 class="page-title">Core Expertise</h1>

    <div class="service-grid">

        <div class="service-card">
            <h3 class="section-title">Cable Car Systems</h3>
            <p>Operation and maintenance of ropeway electrical systems.</p>
        </div>

        <div class="service-card">
            <h3 class="section-title">Power Systems</h3>
            <p>Distribution, protection, and control system design.</p>
        </div>

        <div class="service-card">
            <h3 class="section-title">Industrial Maintenance</h3>
            <p>Preventive and corrective maintenance strategies.</p>
        </div>

        <div class="service-card">
            <h3 class="section-title">Fault Analysis</h3>
            <p>Diagnosis and troubleshooting of electrical systems.</p>
        </div>

    </div>

</section>

<!-- EXPERIENCE PREVIEW -->
<section class="page">

    <h1 class="page-title">Experience</h1>

    <div class="timeline">

        <div class="timeline-item">
            <h3>Electrical Engineer</h3>
            <p class="company">Maulakalika Cable Car</p>
        </div>

        <div class="timeline-item">
            <h3>Technical Instructor</h3>
            <p class="company">Kathmandu Technical School</p>
        </div>

    </div>

</section>

<!-- CTA -->
<section class="page">

    <h1 class="page-title">Have a Project?</h1>

    <p class="text-block">
        Let’s work together on electrical systems, automation, or engineering solutions.
    </p>

    <a class="btn" href="contact.php">Contact Me</a>

</section>

<?php include 'footer.php'; ?>