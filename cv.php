<?php include 'config.php'; ?>
<?php include 'header.php'; ?>

<?php

$profile = $conn->query("SELECT * FROM profile LIMIT 1")->fetch_assoc();
$exp = $conn->query("SELECT * FROM experience ORDER BY id DESC");
$edu = $conn->query("SELECT * FROM education ORDER BY id DESC");
$skills = $conn->query("SELECT * FROM skills ORDER BY id DESC");

?>

<section class="page">

<div style="max-width:900px;margin:auto;">

<!-- ================= HEADER ================= -->
<div style="text-align:center;margin-bottom:30px;">

    <h1 style="font-size:38px;">
        <?= htmlspecialchars($profile['name']) ?>
    </h1>

    <h3 style="color:var(--primary);">
        <?= htmlspecialchars($profile['title']) ?>
    </h3>

    <p style="color:var(--muted);max-width:700px;margin:10px auto;">
        <?= nl2br(htmlspecialchars($profile['about'])) ?>
    </p>

    <p style="margin-top:10px;color:var(--muted);line-height:1.8;">
        📞 <?= $profile['phone'] ?> |
        📧 <?= $profile['email'] ?> |
        🌐 <?= $profile['website'] ?> |
        💻 <?= $profile['github'] ?>
    </p>

    <a href="cv-pdf.php" class="btn">⬇ Download PDF</a>

</div>

<hr>

<!-- ================= TWO COLUMN LAYOUT ================= -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:30px;">

<!-- EXPERIENCE -->
<div>

<h2 class="page-title">Experience</h2>

<?php while($e = $exp->fetch_assoc()): ?>

    <div class="timeline-item">

        <h3><?= htmlspecialchars($e['title']) ?></h3>

        <p class="company"><?= htmlspecialchars($e['company']) ?></p>

        <p><?= htmlspecialchars($e['description']) ?></p>

    </div>

<?php endwhile; ?>

</div>

<!-- EDUCATION -->
<div>

<h2 class="page-title">Education</h2>

<?php while($ed = $edu->fetch_assoc()): ?>

    <div class="timeline-item">

        <h3><?= htmlspecialchars($ed['degree']) ?></h3>

        <p class="company"><?= htmlspecialchars($ed['institution']) ?></p>

        <p><?= htmlspecialchars($ed['description']) ?></p>

    </div>

<?php endwhile; ?>

</div>

</div>

<hr>

<!-- SKILLS -->
<h2 class="page-title">Skills</h2>

<div class="grid">

<?php while($s = $skills->fetch_assoc()): ?>

    <div class="card">

        <h3><?= htmlspecialchars($s['category']) ?></h3>
        <p><?= htmlspecialchars($s['description']) ?></p>

    </div>

<?php endwhile; ?>

</div>

</div>

</section>

<?php include 'footer.php'; ?>