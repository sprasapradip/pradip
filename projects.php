<?php include 'header.php'; ?>

<?php

$limit = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

/* TOTAL */
$total = $conn->query("SELECT COUNT(*) as t FROM projects")
              ->fetch_assoc()['t'];

$totalPages = ceil($total / $limit);

/* FETCH */
$stmt = $conn->prepare("
    SELECT id, title, description, image, slug, created_at
    FROM projects
    ORDER BY id DESC
    LIMIT ? OFFSET ?
");

if(!$stmt){
    die("SQL ERROR: " . $conn->error);
}

$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
.project-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:25px;
}
.project-card{
    background:#fff;
    border-radius:14px;
    overflow:hidden;
    box-shadow:0 5px 20px rgba(0,0,0,0.06);
}
.project-image img{
    width:100%;
    height:200px;
    object-fit:cover;
}
.project-content{padding:16px}
.project-title{font-size:18px;font-weight:600}
.project-desc{font-size:14px;color:#6b7280;margin:10px 0}
.btn{display:inline-block;padding:10px 14px;background:#2563eb;color:#fff;border-radius:8px;text-decoration:none}
</style>

<section class="page">

<h1>Projects</h1>

<div class="project-grid">

<?php while($row = $result->fetch_assoc()): ?>

<?php
    // IMPORTANT FIX: fallback slug
    $slug = !empty($row['slug'])
        ? $row['slug']
        : strtolower(trim(preg_replace('/[^a-z0-9]+/','-',$row['title'])));
?>

<div class="project-card">

    <?php if($row['image']): ?>
        <div class="project-image">
            <img src="uploads/<?= htmlspecialchars($row['image']) ?>">
        </div>
    <?php endif; ?>

    <div class="project-content">

        <div class="project-title">
            <?= htmlspecialchars($row['title']) ?>
        </div>

        <div class="project-desc">
            <?= mb_substr(strip_tags($row['description']),0,120) ?>...
        </div>

        <a class="btn"
   href="/pradip/project/<?= urlencode($row['slug']) ?>">
    Read More
</a>




    </div>

</div>

<?php endwhile; ?>

</div>

</section>

<?php include 'footer.php'; ?>