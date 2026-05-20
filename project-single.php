<?php include 'config.php'; ?>

<?php

$slug = $_GET['slug'] ?? '';

$stmt = $conn->prepare("
    SELECT *
    FROM projects
    WHERE slug = ?
");

$stmt->bind_param("s", $slug);
$stmt->execute();

$result = $stmt->get_result();
$project = $result->fetch_assoc();

if(!$project){
    die("<h2 style='text-align:center;padding:50px;'>Project not found</h2>");
}

?>

<?php include 'header.php'; ?>

<!-- SEO -->
<title><?= htmlspecialchars($project['title']) ?></title>

<meta name="description" content="<?= htmlspecialchars(substr(strip_tags($project['description']),0,160)) ?>">

<section class="page">

<div style="max-width:850px;margin:auto;">

    <!-- TITLE -->
    <h1 class="page-title">
        <?= htmlspecialchars($project['title']) ?>
    </h1>

    <!-- META -->
    <div style="
        display:flex;
        gap:15px;
        flex-wrap:wrap;
        color:gray;
        margin-bottom:20px;
        font-size:14px;
    ">

        <span>
            📅 <?= date('d M Y', strtotime($project['created_at'] ?? 'now')) ?>
        </span>

        <span>
            🆔 Project #<?= $project['id'] ?>
        </span>

    </div>

    <!-- IMAGE -->
    <?php if(!empty($project['image'])): ?>
        <img
            src="/pradip/uploads/<?= htmlspecialchars($project['image']) ?>"
            alt="<?= htmlspecialchars($project['title']) ?>"
            style="
                width:100%;
                max-height:450px;
                object-fit:cover;
                border-radius:14px;
                margin-bottom:25px;
            "
        >
    <?php endif; ?>

    <!-- CONTENT -->
    <div class="blog-content" style="
        line-height:1.9;
        text-align:left;
        font-size:17px;
        color:var(--text);
    ">
        <?= html_entity_decode($project['description']) ?>
    </div>

    <!-- SHARE -->
    <div style="margin-top:50px;">

        <h3>Share Project</h3>

        <?php
            $site = "http://localhost/pradip";
            $url = urlencode($site . "/project/" . $project['slug']);
            $title = urlencode($project['title']);
        ?>

        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:15px;">

            <a class="btn" target="_blank"
               href="https://www.facebook.com/sharer/sharer.php?u=<?= $url ?>">
                Facebook
            </a>

            <a class="btn" target="_blank"
               href="https://twitter.com/intent/tweet?url=<?= $url ?>&text=<?= $title ?>">
                Twitter
            </a>

            <a class="btn" target="_blank"
               href="https://www.linkedin.com/shareArticle?mini=true&url=<?= $url ?>">
                LinkedIn
            </a>

        </div>
    </div>

    <!-- RELATED PROJECTS -->
    <div style="margin-top:70px;">

        <h2>Related Projects</h2>

        <div class="project-grid">

        <?php
        $related = $conn->prepare("
            SELECT *
            FROM projects
            WHERE id != ?
            ORDER BY RAND()
            LIMIT 3
        ");

        $related->bind_param("i", $project['id']);
        $related->execute();

        $related_result = $related->get_result();

        while($r = $related_result->fetch_assoc()):
        ?>

            <div class="project-card">

                <?php if(!empty($r['image'])): ?>
                    <div class="project-image">
                        <img src="/pradip/uploads/<?= htmlspecialchars($r['image']) ?>">
                    </div>
                <?php endif; ?>

                <div class="project-content">

                    <h3><?= htmlspecialchars($r['title']) ?></h3>

                    <p>
                        <?= mb_substr(strip_tags($r['description']),0,90) ?>...
                    </p>

                    <a href="/pradip/project/<?= urlencode($r['slug']) ?>"
                       class="btn">
                        Read More
                    </a>

                </div>

            </div>

        <?php endwhile; ?>

        </div>

    </div>

    <!-- BACK -->
    <div style="margin-top:60px;">
        <a href="/pradip/projects.php" class="btn">
            ← Back to Projects
        </a>
    </div>

</div>

</section>

<?php include 'footer.php'; ?>