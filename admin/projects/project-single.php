<?php include 'header.php'; ?>

<?php

if(empty($_GET['slug'])){
    die("Invalid project");
}

$slug = $_GET['slug'];

$stmt = $conn->prepare("
    SELECT *
    FROM projects
    WHERE slug=?
    LIMIT 1
");

$stmt->bind_param("s", $slug);
$stmt->execute();

$project = $stmt->get_result()->fetch_assoc();

if(!$project){
    die("Project not found");
}
?>

<section class="page">

<div style="max-width:900px;margin:auto;">

<h1><?= htmlspecialchars($project['title']) ?></h1>

<p style="color:gray;">
    <?= date('d M Y', strtotime($project['created_at'] ?? 'now')) ?>
</p>

<?php if($project['image']): ?>
    <img src="uploads/<?= htmlspecialchars($project['image']) ?>" style="width:100%;border-radius:12px;">
<?php endif; ?>

<div style="line-height:1.8;">

<?= html_entity_decode($project['description']) ?>

</div>

<a href="/projects.php" class="btn">← Back</a>

</div>

</section>

<?php include 'footer.php'; ?>