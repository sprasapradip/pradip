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

<section class="page">

<h1>Projects</h1>

<div class="project-grid">

<?php while($row = $result->fetch_assoc()): ?>

<?php
    // SAFE SLUG (IMPORTANT FIX)
    $slug = !empty($row['slug'])
        ? $row['slug']
        : strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $row['title'])));
?>

<div class="project-card">

    <?php if(!empty($row['image'])): ?>
        <div class="project-image">
            <img src="/pradip/uploads/<?= htmlspecialchars($row['image']) ?>" alt="">
        </div>
    <?php endif; ?>

    <div class="project-content">

        <div class="project-title">
            <?= htmlspecialchars($row['title']) ?>
        </div>

        <div class="project-desc">
            <?= mb_substr(strip_tags($row['description']), 0, 120) ?>...
        </div>

        <!-- FIXED READ MORE LINK -->
        <a class="btn"
           href="/pradip/project/<?= urlencode($slug) ?>">
            Read More
        </a>

    </div>

</div>

<?php endwhile; ?>

</div>

<!-- PAGINATION -->
<?php if($totalPages > 1): ?>

<div class="pagination">

    <!-- PREV -->
    <?php if($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>">← Prev</a>
    <?php endif; ?>

    <!-- PAGE NUMBERS -->
    <?php for($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>"
           class="<?= ($i == $page) ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <!-- NEXT -->
    <?php if($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>">Next →</a>
    <?php endif; ?>

</div>

<?php endif; ?>

</section>

<?php include 'footer.php'; ?>