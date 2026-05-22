<?php include 'header.php'; ?>

<?php

if(empty($_GET['slug'])){
    die("Invalid project");
}

$slug = $_GET['slug'];

/* =========================
   GET POST
========================= */
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

/* =========================
   UPDATE VIEWS
========================= */
$view = $conn->prepare("
    UPDATE projects
    SET views = views + 1
    WHERE id=?
");
$view->bind_param("i", $project['id']);
$view->execute();

/* =========================
   TAGS
========================= */
$tags = [];
if(!empty($project['tags'])){
    $tags = array_map('trim', explode(',', $project['tags']));
}

/* =========================
   RELATED POSTS (TAG BASED)
========================= */
$related = null;

if(count($tags) > 0){

    $tagLike = "%" . $tags[0] . "%";

    $rel = $conn->prepare("
        SELECT id, title, slug, image, created_at
        FROM projects
        WHERE tags LIKE ?
        AND slug != ?
        ORDER BY id DESC
        LIMIT 4
    ");

    $rel->bind_param("ss", $tagLike, $slug);
    $rel->execute();
    $related = $rel->get_result();
}

/* =========================
   TRENDING POSTS
========================= */
$trending = $conn->query("
    SELECT id, title, slug, image, views
    FROM projects
    ORDER BY views DESC
    LIMIT 5
");

?>

<style>

/* =========================
   GLOBAL
========================= */
body{
    margin:0;
    background:#0f172a;
    font-family: Arial;
}

/* =========================
   LAYOUT
========================= */
.wrapper{
    max-width:1200px;
    margin:40px auto;
    padding:0 20px;
    display:grid;
    grid-template-columns:2.5fr 1fr;
    gap:25px;
}

/* =========================
   MAIN ARTICLE
========================= */
.article{
    background:#fff;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 20px 50px rgba(0,0,0,0.3);
}

.hero{
    position:relative;
    height:400px;
}

.hero img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.hero::after{
    content:"";
    position:absolute;
    inset:0;
    background:linear-gradient(to top, rgba(0,0,0,0.7), transparent);
}

.hero-text{
    position:absolute;
    bottom:20px;
    left:20px;
    color:#fff;
}

.hero-text h1{
    margin:0;
    font-size:28px;
}

/* =========================
   CONTENT
========================= */
.content{
    padding:30px;
    line-height:1.8;
}

/* META */
.meta{
    font-size:13px;
    color:gray;
    margin-bottom:10px;
}

/* TAGS */
.tags span{
    display:inline-block;
    background:#e2e8f0;
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    margin-right:5px;
}

/* =========================
   SIDEBAR
========================= */
.sidebar{
    display:flex;
    flex-direction:column;
    gap:15px;
}

.box{
    background:#fff;
    padding:15px;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
}

.trend{
    display:flex;
    gap:10px;
    text-decoration:none;
    color:#000;
    margin-bottom:10px;
}

.trend img{
    width:60px;
    height:60px;
    object-fit:cover;
    border-radius:8px;
}

/* RELATED */
.related-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:10px;
}

.related-card{
    text-decoration:none;
    color:#000;
}

.related-card img{
    width:100%;
    height:90px;
    object-fit:cover;
    border-radius:8px;
}

/* MOBILE */
@media(max-width:900px){
    .wrapper{
        grid-template-columns:1fr;
    }
}

</style>

<div class="wrapper">

<!-- =========================
     ARTICLE
========================= -->
<div class="article">

    <div class="hero">

        <?php if($project['image']): ?>
            <img src="uploads/<?= htmlspecialchars($project['image']) ?>">
        <?php endif; ?>

        <div class="hero-text">
            <h1><?= htmlspecialchars($project['title']) ?></h1>
        </div>

    </div>

    <div class="content">

        <div class="meta">
            👤 <?= htmlspecialchars($project['author']) ?> |
            📂 <?= htmlspecialchars($project['category']) ?> |
            👁 <?= $project['views'] ?> views |
            📅 <?= date('d M Y', strtotime($project['created_at'] ?? 'now')) ?>
        </div>

        <div class="tags">
            <?php foreach($tags as $t): ?>
                <span><?= htmlspecialchars($t) ?></span>
            <?php endforeach; ?>
        </div>

        <br>

        <div>
            <?= html_entity_decode($project['description']) ?>
        </div>

    </div>

</div>

<!-- =========================
     SIDEBAR
========================= -->
<div class="sidebar">

    <!-- TRENDING -->
    <div class="box">
        <h3>🔥 Trending</h3>

        <?php while($t = $trending->fetch_assoc()): ?>

            <a class="trend"
               href="project.php?slug=<?= $t['slug'] ?>">

                <?php if($t['image']): ?>
                    <img src="uploads/<?= $t['image'] ?>">
                <?php endif; ?>

                <div>
                    <b><?= htmlspecialchars($t['title']) ?></b><br>
                    <small><?= $t['views'] ?> views</small>
                </div>

            </a>

        <?php endwhile; ?>

    </div>

    <!-- RELATED -->
    <div class="box">
        <h3>🔗 Related Posts</h3>

        <div class="related-grid">

            <?php if($related): ?>
                <?php while($r = $related->fetch_assoc()): ?>

                    <a class="related-card"
                       href="project.php?slug=<?= $r['slug'] ?>">

                        <?php if($r['image']): ?>
                            <img src="uploads/<?= $r['image'] ?>">
                        <?php endif; ?>

                        <small><?= htmlspecialchars($r['title']) ?></small>

                    </a>

                <?php endwhile; ?>
            <?php else: ?>
                <p>No related posts</p>
            <?php endif; ?>

        </div>

    </div>

</div>

</div>

<?php include 'footer.php'; ?>