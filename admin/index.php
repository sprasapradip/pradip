<<<<<<< HEAD
<?php
define('APP_INIT', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/guard.php';

/* -------------------------
   SAFE COUNTS
--------------------------*/

$msgCount = 0;
$projCount = 0;

/* Messages Count */
$msgRes = $conn->query("SELECT COUNT(*) as total FROM messages");
if ($msgRes) {
    $msgCount = $msgRes->fetch_assoc()['total'];
}

/* Projects Count */
$projRes = $conn->query("SELECT COUNT(*) as total FROM projects");
if ($projRes) {
    $projCount = $projRes->fetch_assoc()['total'];
}

/* Recent Data */
$recentMessages = $conn->query("SELECT * FROM messages ORDER BY id DESC LIMIT 5");
$recentProjects = $conn->query("SELECT * FROM projects ORDER BY id DESC LIMIT 5");

include __DIR__ . '/layout/header.php';
?>

<section class="admin-page">

<h1>Dashboard</h1>

<!-- =========================
     STAT CARDS
========================= -->

<div class="grid">

    <div class="card">
        <h2><?= $msgCount ?></h2>
        <p>Total Messages</p>
    </div>

    <div class="card">
        <h2><?= $projCount ?></h2>
        <p>Total Projects</p>
    </div>

</div>

<br>

<!-- =========================
     QUICK ACTIONS
========================= -->

<h2>Quick Actions</h2>

<div class="grid">

    <a href="projects/add.php" class="card">
        ➕ Add Project
    </a>

    <a href="messages/index.php" class="card">
        📬 View Messages
    </a>

</div>

<br>

<!-- =========================
     RECENT PROJECTS
========================= -->

<h2>Recent Projects</h2>

<div class="grid">

<?php if($recentProjects && $recentProjects->num_rows > 0): ?>

    <?php while($row = $recentProjects->fetch_assoc()): ?>

        <div class="card">

            <h3><?= htmlspecialchars($row['title']) ?></h3>

            <p>
                <?= htmlspecialchars(substr($row['description'], 0, 100)) ?>...
            </p>

            <a href="projects/edit.php?id=<?= $row['id'] ?>" class="btn">
                Edit
            </a>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <div class="card">
        No projects found.
    </div>

<?php endif; ?>

</div>

<br>

<!-- =========================
     RECENT MESSAGES
========================= -->

<h2>Recent Messages</h2>

<div class="card">

<?php if($recentMessages && $recentMessages->num_rows > 0): ?>

    <ul>

        <?php while($msg = $recentMessages->fetch_assoc()): ?>

            <li>
                <strong><?= htmlspecialchars($msg['name']) ?>:</strong>
                <?= htmlspecialchars(substr($msg['message'], 0, 80)) ?>...
            </li>

        <?php endwhile; ?>

    </ul>

<?php else: ?>

    <p>No messages yet.</p>

<?php endif; ?>

</div>

</section>

<?php include __DIR__ . '/layout/footer.php'; ?>
=======
<?php include 'header.php'; ?>

<h2>Admin Dashboard</h2>
<div class="grid">
    <a href="projects/index.php" class="card">Manage Projects</a>
    <a href="messages/index.php" class="card">View Messages</a>
    <a href="blog/index.php" class="card">Manage Blog</a>
</div>

<?php include 'footer.php'; ?>
>>>>>>> fe19f5faa741cfcbb315602c1db3bd7e772eac19
