<?php include 'config.php'; ?>
<?php include 'header.php'; ?>





<style>

.hero-modern{
    padding:120px 20px;
    background:linear-gradient(135deg,#0f172a,#111827);
    color:#fff;
    text-align:center;
}

.hero-modern h1{
    font-size:64px;
    margin-bottom:20px;
}

.hero-text{
    max-width:800px;
    margin:auto;
    line-height:1.8;
    opacity:.9;
}

.hero-badge{
    display:inline-block;
    padding:10px 20px;
    background:#1e293b;
    border-radius:50px;
    margin-bottom:20px;
}

.stats-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    padding:50px 20px;
}

.stat-card{
    background:#111827;
    color:#fff;
    padding:40px;
    border-radius:20px;
    text-align:center;
}

.project-card img{
    width:100%;
    height:220px;
    object-fit:cover;
    border-radius:15px 15px 0 0;
}

.about-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:40px;
}

.about-box{
    background:#0f172a;
    color:#fff;
    padding:30px;
    border-radius:20px;
}

.cta-section{
    padding:100px 20px;
    text-align:center;
    background:#0f172a;
    color:#fff;
}


</style>

<!-- HERO SECTION -->
<section class="hero-modern">

    <div class="hero-content">

        <span class="hero-badge">
            Electrical Engineer • Ropeway Systems • Industrial Maintenance
        </span>

        <h1>
            <?php echo htmlspecialchars($profile['name']); ?>
        </h1>

        <p class="hero-text">
            Specialized in electrical engineering, cable car operations,
            industrial automation, preventive maintenance, and power systems.
            Currently working at Maulakalika Cable Car ensuring safe and reliable
            transportation systems.
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
            <h2>5+</h2>
            <p>Years Experience</p>
        </div>

        <div class="stat-card">
            <h2>100+</h2>
            <p>Electrical Repairs</p>
        </div>

        <div class="stat-card">
            <h2>50+</h2>
            <p>Maintenance Operations</p>
        </div>

        <div class="stat-card">
            <h2>99%</h2>
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
                I am an Electrical Engineer focused on power systems,
                industrial maintenance, electrical safety, ropeway systems,
                and automation technologies.
            </p>

            <p class="text-block">
                My experience includes preventive maintenance,
                troubleshooting electrical faults, maintaining cable car
                infrastructure, and managing operational safety systems.
            </p>
        </div>

        <div class="about-box">
            <h3>Core Engineering Areas</h3>

            <ul>
                <li>Power Distribution Systems</li>
                <li>Industrial Electrical Maintenance</li>
                <li>PLC & Control Systems</li>
                <li>Cable Car Electrical Systems</li>
                <li>Fault Diagnosis</li>
                <li>Safety & Inspection</li>
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
<section class="page">

    <h1 class="page-title">Professional Experience</h1>

    <div class="timeline">

        <div class="timeline-item">
            <h3>Electrical Engineer</h3>
            <span>Maulakalika Cable Car</span>

            <p>
                Managing ropeway electrical systems, preventive maintenance,
                troubleshooting, and operational safety inspections.
            </p>
        </div>

        <div class="timeline-item">
            <h3>Technical Instructor</h3>
            <span>Kathmandu Technical School</span>

            <p>
                Training students in electrical systems,
                industrial controls, and engineering principles.
            </p>
        </div>

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