<?php include 'config.php'; ?>
<?php include 'header.php'; ?>

<?php

$stmt = $conn->prepare("
    SELECT *
    FROM blogs
    WHERE status='published'
    ORDER BY id DESC
");

$stmt->execute();
$result = $stmt->get_result();

?>

<section class="page">

    <h1 class="page-title">Blogs</h1>

    <p class="text-block">
        Latest articles and engineering updates from field experience.
    </p>

    <div class="project-grid">

        <?php while($row = $result->fetch_assoc()): ?>

            <div class="project-card">

                <?php if(!empty($row['image'])): ?>

                    <div class="project-image">

                        <img src="/pradip/uploads/<?= htmlspecialchars($row['image']) ?>"
                             alt="<?= htmlspecialchars($row['title']) ?>">

                    </div>

                <?php endif; ?>

                <div class="project-content">

                    <h3 class="project-title">
                        <?= htmlspecialchars($row['title']) ?>
                    </h3>

                    <p class="project-description">

                        <?= mb_substr(
                            trim(strip_tags(html_entity_decode($row['content']))),
                            0,
                            150
                        ) ?>...

                    </p>

                    <div style="
                        margin-bottom:15px;
                        color:gray;
                        font-size:14px;
                    ">

                        <?= date('d M Y', strtotime($row['created_at'])) ?>

                        •

                        👁 <?= (int)$row['views'] ?> views

                    </div>

                    <a href="blog/<?= urlencode($row['slug']) ?>"
                       class="btn">

                        Read More

                    </a>

                </div>

            </div>

        <?php endwhile; ?>

    </div>

</section>

<?php include 'footer.php'; ?>