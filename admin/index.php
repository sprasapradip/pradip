<?php
define('APP_INIT', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/guard.php';

/* =========================
   SAFE COUNT FUNCTION
========================= */
function getCount($conn, $table){
    $result = $conn->query("SELECT COUNT(*) as total FROM $table");

    if($result){
        return $result->fetch_assoc()['total'];
    }

    return 0;
}

/* =========================
   COUNTS
========================= */
$msgCount      = getCount($conn, 'messages');
$projCount     = getCount($conn, 'projects');
$blogCount     = getCount($conn, 'blogs');
$serviceCount  = getCount($conn, 'services');
$expCount      = getCount($conn, 'experience');
$skillCount    = getCount($conn, 'skills');

/* =========================
   RECENT DATA
========================= */
$recentMessages = $conn->query("
    SELECT *
    FROM messages
    ORDER BY id DESC
    LIMIT 5
");

$recentProjects = $conn->query("
    SELECT *
    FROM projects
    ORDER BY id DESC
    LIMIT 5
");

$recentBlogs = $conn->query("
    SELECT *
    FROM blogs
    ORDER BY id DESC
    LIMIT 5
");

include __DIR__ . '/layout/header.php';
?>

<section class="admin-page">

<!-- =========================
     PAGE HEADER
========================= -->

<div class="dashboard-header">

    <div>
        <h1>Admin Dashboard</h1>

        <p class="dashboard-subtitle">
            Welcome back, manage your portfolio, blogs, CV and website content.
        </p>
    </div>

    <div>
        <a href="../cv.php" target="_blank" class="btn">
            View CV
        </a>
    </div>

</div>

<!-- =========================
     STATS
========================= -->

<div class="stats-grid">

    <div class="stat-card">
        <h2><?= $msgCount ?></h2>
        <p>Total Messages</p>
    </div>

    <div class="stat-card">
        <h2><?= $projCount ?></h2>
        <p>Total Projects</p>
    </div>

    <div class="stat-card">
        <h2><?= $blogCount ?></h2>
        <p>Total Blogs</p>
    </div>

    <div class="stat-card">
        <h2><?= $serviceCount ?></h2>
        <p>Total Services</p>
    </div>

    <div class="stat-card">
        <h2><?= $expCount ?></h2>
        <p>Experience Entries</p>
    </div>

    <div class="stat-card">
        <h2><?= $skillCount ?></h2>
        <p>Skills</p>
    </div>

</div>

<!-- =========================
     QUICK ACTIONS
========================= -->

<h2 class="section-heading">Quick Actions</h2>

<div class="action-grid">

    <a href="projects/create.php" class="action-card">
        ➕ Add Project
    </a>

    <a href="blogs/create.php" class="action-card">
        ✍️ Write Blog
    </a>

    <a href="services/create.php" class="action-card">
        ⚡ Add Service
    </a>

    <a href="experience/create.php" class="action-card">
        💼 Add Experience
    </a>

    <a href="messages/index.php" class="action-card">
        📬 Messages
    </a>

    <a href="profile/edit.php" class="action-card">
        👤 Edit CV
    </a>

</div>

<!-- =========================
     RECENT PROJECTS
========================= -->

<h2 class="section-heading">Recent Projects</h2>

<div class="dashboard-grid">

<?php if($recentProjects && $recentProjects->num_rows > 0): ?>

    <?php while($row = $recentProjects->fetch_assoc()): ?>

        <div class="dashboard-card">

            <?php if(!empty($row['image'])): ?>

                <img src="../uploads/<?= htmlspecialchars($row['image']) ?>"
                     class="dashboard-image"
                     alt="project">

            <?php endif; ?>

            <div class="dashboard-content">

                <h3>
                    <?= htmlspecialchars($row['title']) ?>
                </h3>

                <p>
                     <?= substr($row['description'],0,120) ?>...
                </p>

                <a href="projects/index.php?edit=<?= $row['id'] ?>" class="btn">
                    Edit Project
                </a>
            </div>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <div class="dashboard-card">
        No projects found.
    </div>

<?php endif; ?>

</div>

<!-- =========================
     RECENT BLOGS
========================= -->

<h2 class="section-heading">Recent Blogs</h2>

<div class="dashboard-grid">

<?php if($recentBlogs && $recentBlogs->num_rows > 0): ?>

    <?php while($blog = $recentBlogs->fetch_assoc()): ?>

        <div class="dashboard-card">

            <?php if(!empty($blog['image'])): ?>

                <img src="../uploads/<?= htmlspecialchars($blog['image']) ?>"
                     class="dashboard-image"
                     alt="blog">

            <?php endif; ?>

            <div class="dashboard-content">

                <h3>
                    <?= htmlspecialchars($blog['title']) ?>
                </h3>

                <p>
                    <?= htmlspecialchars(substr(strip_tags($blog['content']), 0, 100)) ?>...
                </p>

                <a href="blogs/edit.php?id=<?= $blog['id'] ?>" class="btn">
                    Edit Blog
                </a>

            </div>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <div class="dashboard-card">
        No blogs found.
    </div>

<?php endif; ?>

</div>

<!-- =========================
     RECENT MESSAGES
========================= -->

<h2 class="section-heading">Recent Messages</h2>

<div class="message-list">

<?php if($recentMessages && $recentMessages->num_rows > 0): ?>

    <?php while($msg = $recentMessages->fetch_assoc()): ?>

        <div class="message-item">

            <div>

                <h4>
                    <?= htmlspecialchars($msg['name']) ?>
                </h4>

                <small>
                    <?= htmlspecialchars($msg['email']) ?>
                </small>

                <p>
                    <?= htmlspecialchars(substr($msg['message'], 0, 120)) ?>...
                </p>

            </div>

            <a href="messages/index.php" class="btn">
                Open
            </a>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <div class="dashboard-card">
        No messages yet.
    </div>

<?php endif; ?>

</div>

</section>

<?php include __DIR__ . '/layout/footer.php'; ?>
<style>

/* =========================
   DASHBOARD
========================= */

.dashboard-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:30px;
    flex-wrap:wrap;
}

.dashboard-subtitle{
    color:var(--muted);
    margin-top:8px;
}

/* =========================
   STATS
========================= */

.stats-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:40px;
}

.stat-card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:18px;
    padding:25px;
    text-align:center;
    transition:0.3s;
}

.stat-card:hover{
    transform:translateY(-6px);
    border-color:var(--primary);
}

.stat-card h2{
    font-size:36px;
    color:var(--primary);
    margin-bottom:10px;
}

/* =========================
   ACTIONS
========================= */

.section-heading{
    margin:30px 0 20px;
    font-size:24px;
}

.action-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
}

.action-card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:16px;
    padding:22px;
    text-decoration:none;
    color:var(--text);
    font-weight:600;
    transition:0.3s;
}

.action-card:hover{
    transform:translateY(-5px);
    border-color:var(--primary);
}

/* =========================
   CONTENT GRID
========================= */

.dashboard-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
    gap:20px;
}

.dashboard-card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:18px;
    overflow:hidden;
    transition:0.3s;
}

.dashboard-card:hover{
    transform:translateY(-6px);
}

.dashboard-image{
    width:100%;
    height:220px;
    object-fit:cover;
}

.dashboard-content{
    padding:18px;
}

.dashboard-content h3{
    margin-bottom:10px;
}

.dashboard-content p{
    color:var(--muted);
    line-height:1.7;
    margin-bottom:15px;
}

/* =========================
   MESSAGES
========================= */

.message-list{
    display:flex;
    flex-direction:column;
    gap:15px;
}

.message-item{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:16px;
    padding:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
}

.message-item p{
    margin-top:8px;
    color:var(--muted);
}

/* =========================
   MOBILE
========================= */

@media(max-width:768px){

    .message-item{
        flex-direction:column;
        align-items:flex-start;
    }

    .dashboard-header{
        align-items:flex-start;
    }

}

/* =========================
   STICKY FOOTER FIX
========================= */

html,
body{
    height:100%;
}

body{
    min-height:100vh;
    display:flex;
    flex-direction:column;
}

/* MAIN CONTENT */
.main-content{
    flex:1;
}

/* FOOTER */
footer{
    margin-top:auto;
}
</style>
